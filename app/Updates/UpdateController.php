<?php

declare(strict_types=1);

namespace BT\App\Updates;

use BT\Core\Database\Database;
use Exception;
use ZipArchive;

final class UpdateController
{
    private const MAX_PACKAGE_SIZE = 209715200;

    public function listar(): array
    {
        return Database::fetchAll("SELECT * FROM updates ORDER BY created_at DESC");
    }

    public function publicar(array $dados, array $arquivo): array
    {
        try {
            $versao = $this->normalizeVersion((string) ($dados['versao'] ?? ''));
            $changelog = trim((string) ($dados['changelog'] ?? ''));
            $isMandatory = isset($dados['is_mandatory']) ? 1 : 0;

            if ($versao === null) {
                throw new Exception('Use uma versao no formato X.Y.Z.');
            }
            if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $phpErrors = [
                    1 => "O arquivo excede o limite do PHP (upload_max_filesize).",
                    2 => "O arquivo excede o limite do formulário.",
                    3 => "O upload foi parcial.",
                    4 => "Nenhum arquivo foi selecionado.",
                    6 => "Pasta temporária ausente.",
                    7 => "Erro ao gravar no disco.",
                    8 => "Extensão do PHP barrou o upload."
                ];
                $msg = $phpErrors[$arquivo['error']] ?? "Erro desconhecido no upload.";
                throw new Exception($msg);
            }
            if (strtolower((string) pathinfo((string) $arquivo['name'], PATHINFO_EXTENSION)) !== 'zip') {
                throw new Exception('Apenas arquivos ZIP sao permitidos.');
            }
            if ((int) ($arquivo['size'] ?? 0) < 1 || (int) $arquivo['size'] > self::MAX_PACKAGE_SIZE) {
                throw new Exception('O pacote OTA possui tamanho invalido.');
            }

            $this->validateArchive((string) $arquivo['tmp_name']);

            $nomeFinal = 'bt_update_' . str_replace('.', '_', $versao) . '_' . time() . '.zip';
            $destinoRaiz = dirname(__DIR__, 2) . '/storage/updates/';
            if (!is_dir($destinoRaiz) && !mkdir($destinoRaiz, 0775, true) && !is_dir($destinoRaiz)) {
                throw new Exception('Nao foi possivel preparar o armazenamento OTA.');
            }

            $caminhoCompleto = $destinoRaiz . $nomeFinal;
            if (!move_uploaded_file((string) $arquivo['tmp_name'], $caminhoCompleto)) {
                throw new Exception('Falha ao armazenar o pacote OTA.');
            }

            $hash = hash_file('sha256', $caminhoCompleto);
            Database::execute(
                'INSERT INTO updates (versao, arquivo_path, checksum_sha256, changelog, is_mandatory, canal) VALUES (?, ?, ?, ?, ?, ?)',
                [$versao, $nomeFinal, $hash, $changelog, $isMandatory, 'stable']
            );

            return ['success' => true, 'message' => 'Versao publicada com sucesso.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getLatest(): ?array
    {
        $valid = [];
        foreach (Database::fetchAll('SELECT * FROM updates') as $release) {
            $version = $this->normalizeVersion((string) ($release['versao'] ?? ''));
            $file = basename((string) ($release['arquivo_path'] ?? ''));
            $path = dirname(__DIR__, 2) . '/storage/updates/' . $file;

            if ($version === null || $file === '' || !is_file($path)) {
                continue;
            }
            if (!hash_equals((string) $release['checksum_sha256'], (string) hash_file('sha256', $path))) {
                continue;
            }

            $release['versao'] = $version;
            $release['arquivo_path'] = $file;
            $valid[] = $release;
        }

        usort($valid, static fn(array $a, array $b): int => version_compare($b['versao'], $a['versao']));
        return $valid[0] ?? null;
    }

    public function getById(int $id): ?array
    {
        $release = Database::fetch('SELECT * FROM updates WHERE id = ? LIMIT 1', [$id]);
        if (!$release) {
            return null;
        }

        $file = basename((string) $release['arquivo_path']);
        $path = dirname(__DIR__, 2) . '/storage/updates/' . $file;
        if (!is_file($path) || !hash_equals((string) $release['checksum_sha256'], (string) hash_file('sha256', $path))) {
            return null;
        }

        $release['versao'] = $this->normalizeVersion((string) $release['versao']);
        return $release['versao'] === null ? null : $release;
    }

    private function normalizeVersion(string $version): ?string
    {
        $version = trim($version);
        // Regex mais flexível: remove qualquer 'v' extra e aceita X.Y.Z ou X.Y
        if (!preg_match('/^v*(\d+)(?:\.(\d+))?(?:\.(\d+))?$/i', $version, $matches)) {
            return null;
        }

        $major = $matches[1];
        $minor = $matches[2] ?? '0';
        $patch = $matches[3] ?? '0';

        return "$major.$minor.$patch";
    }

    private function validateArchive(string $archive): void
    {
        $zip = new ZipArchive();
        if ($zip->open($archive) !== true) {
            throw new Exception('O pacote ZIP nao pode ser aberto.');
        }

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = (string) $zip->getNameIndex($index);
            if (!$this->isSafePackagePath($name)) {
                $zip->close();
                throw new Exception('O pacote contem um caminho nao permitido: ' . $name);
            }
        }
        $zip->close();
    }

    private function isSafePackagePath(string $path): bool
    {
        $normalized = str_replace('\\', '/', $path);
        if ($normalized === '' || str_starts_with($normalized, '/') || str_contains($normalized, "\0")) {
            return false;
        }
        foreach (explode('/', $normalized) as $segment) {
            if ($segment === '..') {
                return false;
            }
        }
        return !preg_match('#^(database|public/uploads|cache|logs)/#i', $normalized);
    }
}

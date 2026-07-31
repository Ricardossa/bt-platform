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
            $produto = trim((string) ($dados['produto'] ?? 'BT_QUEUE_ENTERPRISE'));
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

            $ext = strtolower((string) pathinfo((string) $arquivo['name'], PATHINFO_EXTENSION));

            // Validação por tipo de produto
            if ($produto === 'BT1_COLETOR_PRO' && $ext !== 'apk') {
                throw new Exception('Para o Coletor Pro, apenas arquivos .apk sao permitidos.');
            }
            if ($produto === 'BT_QUEUE_ENTERPRISE' && $ext !== 'zip') {
                throw new Exception('Para a Enterprise, apenas arquivos .zip sao permitidos.');
            }

            if ((int) ($arquivo['size'] ?? 0) < 1 || (int) $arquivo['size'] > self::MAX_PACKAGE_SIZE) {
                throw new Exception('O pacote OTA possui tamanho invalido.');
            }

            // Validação interna apenas para ZIPs
            if ($ext === 'zip') {
                $this->validateArchive((string) $arquivo['tmp_name']);
            }

            $prefixo = ($ext === 'zip') ? 'bt_update_' : 'bt_app_';
            $nomeFinal = $prefixo . str_replace('.', '_', $versao) . '_' . time() . '.' . $ext;

            $subPasta = ($ext === 'zip') ? 'updates/' : 'apks/';
            $destinoRaiz = dirname(__DIR__, 2) . '/storage/' . $subPasta;

            if (!is_dir($destinoRaiz) && !mkdir($destinoRaiz, 0775, true) && !is_dir($destinoRaiz)) {
                throw new Exception('Nao foi possivel preparar o armazenamento OTA.');
            }

            $caminhoCompleto = $destinoRaiz . $nomeFinal;
            if (!move_uploaded_file((string) $arquivo['tmp_name'], $caminhoCompleto)) {
                throw new Exception('Falha ao armazenar o arquivo no servidor.');
            }

            $hash = hash_file('sha256', $caminhoCompleto);
            Database::execute(
                'INSERT INTO updates (produto, versao, arquivo_path, checksum_sha256, changelog, is_mandatory, canal) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$produto, $versao, $nomeFinal, $hash, $changelog, $isMandatory, 'stable']
            );

            return ['success' => true, 'message' => 'Lançamento publicado com sucesso.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function excluir(int $id): array
    {
        try {
            $release = Database::fetch('SELECT * FROM updates WHERE id = ?', [$id]);
            if (!$release) {
                throw new Exception('Lançamento não encontrado.');
            }

            $file = basename((string) $release['arquivo_path']);
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $subPasta = ($ext === 'zip') ? 'updates/' : 'apks/';
            $path = dirname(__DIR__, 2) . '/storage/' . $subPasta . $file;

            // Remove o arquivo físico se existir
            if (is_file($path)) {
                @unlink($path);
            }

            // Remove do banco de dados
            Database::execute('DELETE FROM updates WHERE id = ?', [$id]);

            return ['success' => true, 'message' => 'Lançamento removido com sucesso.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getLatest(string $produto = 'BT_QUEUE_ENTERPRISE'): ?array
    {
        $valid = [];
        $releases = Database::fetchAll('SELECT * FROM updates WHERE produto = ?', [$produto]);

        foreach ($releases as $release) {
            $version = $this->normalizeVersion((string) ($release['versao'] ?? ''));
            $file = basename((string) ($release['arquivo_path'] ?? ''));

            $subPasta = (str_ends_with($file, '.zip')) ? 'updates/' : 'apks/';
            $path = dirname(__DIR__, 2) . '/storage/' . $subPasta . $file;

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

        if (empty($valid)) return null;

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
        $subPasta = (str_ends_with($file, '.zip')) ? 'updates/' : 'apks/';
        $path = dirname(__DIR__, 2) . '/storage/' . $subPasta . $file;

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

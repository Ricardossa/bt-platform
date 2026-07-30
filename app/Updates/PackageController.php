<?php

declare(strict_types=1);

namespace BT\App\Updates;

use Exception;
use ZipArchive;

final class PackageController
{
    private string $enterpriseDir;
    private string $versionPath;
    private string $storageDir;

    public function __construct()
    {
        // Agora a Enterprise é um projeto vizinho, fora da pasta public
        $this->enterpriseDir = dirname(BT_ROOT) . '/bt-enterprise/';
        $this->versionPath = $this->enterpriseDir . 'public/version.json';
        $this->storageDir = BT_ROOT . '/public/uploads/temp_packages/';
    }

    /**
     * Retorna a versão atual lida do arquivo version.json da Enterprise
     */
    public function getVersionInfo(): array
    {
        if (!is_file($this->versionPath)) {
            return ['version' => '0.0.0', 'build' => 'N/A'];
        }

        $json = json_decode((string) file_get_contents($this->versionPath), true);
        return [
            'version' => $json['version'] ?? '0.0.0',
            'build' => $json['build'] ?? 'N/A'
        ];
    }

    /**
     * Gera o pacote ZIP e retorna o caminho para download
     */
    public function generate(string $newVersion): array
    {
        try {
            // 1. Atualizar version.json
            $this->updateVersionFile($newVersion);

            // 2. Preparar diretório de saída
            if (!is_dir($this->storageDir)) {
                mkdir($this->storageDir, 0775, true);
            }

            $zipName = "BT_Update_v{$newVersion}_" . date('Ymd_His') . ".zip";
            $zipPath = $this->storageDir . $zipName;

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception("Falha ao criar arquivo ZIP em $zipPath");
            }

            // 3. Adicionar TODO o conteúdo da pasta Enterprise (Respeitando filtros)
            $this->addFolderToZip($this->enterpriseDir, $zip, $this->enterpriseDir);

            $zip->close();

            return [
                'success' => true,
                'file_name' => $zipName,
                'download_url' => 'uploads/temp_packages/' . $zipName,
                'version' => $newVersion
            ];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function updateVersionFile(string $version): void
    {
        $info = $this->getVersionInfo();
        $data = [
            'version' => $version,
            'build' => date('Ymd') . '_AUTO_BUILD',
            'channel' => 'stable'
        ];

        if (file_put_contents($this->versionPath, json_encode($data, JSON_PRETTY_PRINT)) === false) {
            throw new Exception("Falha ao atualizar o arquivo version.json");
        }
    }

    private function addFolderToZip(string $dir, ZipArchive $zip, string $baseDir): void
    {
        if (!is_dir($dir)) return;

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($baseDir));

                // --- FILTRO DE SEGURANÇA (DEC-051) ---
                // Ignorar arquivos indesejados e diretórios protegidos
                if (preg_match('#^(database|public/uploads|cache|logs)/#i', $relativePath)) {
                    continue;
                }

                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * Limpa pacotes antigos (mais de 24h)
     */
    public function cleanOldPackages(): void
    {
        if (!is_dir($this->storageDir)) return;

        foreach (scandir($this->storageDir) as $file) {
            $path = $this->storageDir . $file;
            if (is_file($path) && (time() - filemtime($path) > 86400)) {
                @unlink($path);
            }
        }
    }
}

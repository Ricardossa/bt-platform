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
            $this->addWhitelistedFilesToZip($zip);

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

    /**
     * Build the OTA from an explicit allowlist. Local unit state is never
     * traversed, so it cannot accidentally become part of a release.
     */
    private function addWhitelistedFilesToZip(ZipArchive $zip): void
    {
        $this->addAllowedFile($zip, 'bootstrap.php');
        $this->addAllowedPhpTree($zip, 'core');
        $this->addAllowedPhpTree($zip, 'public');
        $this->addAllowedAssetTree($zip, 'public/assets');

        // Versioned installation sources only. config.json is unit-specific.
        $this->addAllowedFile($zip, 'public/version.json');
        $this->addAllowedFile($zip, 'config/config.php');

        // Required by the active local installation and OTA post-install step.
        foreach (['BT_Kernel.vbs', 'BT_Sync_Service.vbs', 'BT_Watchdog.vbs', 'Ligar_Impressora_Local.bat', 'print_bridge.php'] as $file) {
            $this->addAllowedFile($zip, $file);
        }
    }

    private function addAllowedPhpTree(ZipArchive $zip, string $relativeDir): void
    {
        $this->addFilesFromTree($zip, $relativeDir, static function (string $relativePath): bool {
            return str_ends_with(strtolower($relativePath), '.php')
                && !self::isDevelopmentOrBackupFile($relativePath);
        });
    }

    private function addAllowedAssetTree(ZipArchive $zip, string $relativeDir): void
    {
        $this->addFilesFromTree($zip, $relativeDir, static function (string $relativePath): bool {
            return !self::isDevelopmentOrBackupFile($relativePath);
        });
    }

    private function addAllowedFile(ZipArchive $zip, string $relativePath): void
    {
        $relativePath = str_replace('\\', '/', $relativePath);
        $path = $this->enterpriseDir . $relativePath;

        if (is_file($path) && !self::isDevelopmentOrBackupFile($relativePath)) {
            $zip->addFile($path, $relativePath);
        }
    }

    /** @param callable(string): bool $isAllowed */
    private function addFilesFromTree(ZipArchive $zip, string $relativeDir, callable $isAllowed): void
    {
        $directory = $this->enterpriseDir . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir);
        if (!is_dir($directory)) return;

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if ($file->isDir()) continue;

            $path = $file->getPathname();
            $relativePath = str_replace('\\', '/', substr($path, strlen($this->enterpriseDir)));

            if ($isAllowed($relativePath)) {
                $zip->addFile($path, $relativePath);
            }
        }
    }

    private static function isDevelopmentOrBackupFile(string $relativePath): bool
    {
        $normalized = strtolower(str_replace('\\', '/', $relativePath));
        $name = basename($normalized);

        return preg_match('#(^|/)(\.git|99_quarentena|backups?|cache|logs|uploads?)(/|$)#', $normalized) === 1
            || $normalized === 'config/config.json'
            || preg_match('/(^|[._-])(bak|old|tmp|temp|swp|swo|orig|rej|testbak)([._-]|$)/', $name) === 1
            || preg_match('/(^|[_-])(debug|diag|teste?|audit|fix)([_-]|\.|$)/', $name) === 1
            || preg_match('#(^|/)\.(idea|vscode)(/|$)#', $normalized) === 1;
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

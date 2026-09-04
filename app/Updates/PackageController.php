<?php

declare(strict_types=1);

namespace BT\App\Updates;

use Exception;
use ZipArchive;

/**
 * Motor de Empacotamento Multi-Plataforma (v2.0)
 * Suporta Enterprise (Farmácia) e Enterprise Lite (SaaS/Barbearia).
 */
final class PackageController
{
    private string $currentProjectDir;
    private string $versionPath;
    private string $storageDir;
    private string $publicDir;
    private string $product;

    public function __construct(string $product = 'BT_QUEUE_ENTERPRISE')
    {
        $this->product = $product;

        // Define a pasta de origem baseada no produto
        $folder = ($product === 'BT_QUEUE_ENTERPRISE_LITE') ? 'bt-enterprise-lite' : 'bt-enterprise';

        $this->currentProjectDir = (string) realpath(dirname(BT_ROOT) . '/' . $folder) . DIRECTORY_SEPARATOR;
        $this->versionPath = $this->currentProjectDir . 'public' . DIRECTORY_SEPARATOR . 'version.json';
        $this->storageDir = (string) realpath(BT_ROOT . '/storage/updates') . DIRECTORY_SEPARATOR;
        $this->publicDir = (string) realpath(BT_ROOT . '/public') . DIRECTORY_SEPARATOR;
    }

    public function getVersionInfo(): array
    {
        if (!is_file($this->versionPath)) return ['version' => '0.0.0', 'build' => 'N/A'];
        $json = json_decode((string) file_get_contents($this->versionPath), true);
        return ['version' => $json['version'] ?? '0.0.0', 'build' => $json['build'] ?? 'N/A'];
    }

    public function generate(string $newVersion): array
    {
        // [v2.0.1] Prevenção de Erro 500: Aumenta limites para processamento de arquivos
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        try {
            $this->updateVersionFile($newVersion);
            if (!is_dir($this->storageDir)) mkdir($this->storageDir, 0775, true);

            $prefix = ($this->product === 'BT_QUEUE_ENTERPRISE_LITE') ? 'LITE' : 'Update';
            $zipName = "BT_{$prefix}_v{$newVersion}_" . date('Ymd_His') . ".zip";
            $zipPath = $this->storageDir . $zipName;

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception("Falha ao criar arquivo ZIP OTA para {$this->product}");
            }

            // --- CONTEÚDO OTA ---
            $this->addAllowedFile($zip, 'bootstrap.php');
            $this->addTree($zip, 'core', ['php']);
            $this->addTree($zip, 'public', ['php', 'css', 'js', 'png', 'jpg', 'jpeg', 'svg', 'gif', 'ico', 'mp3', 'wav', 'ttf', 'woff', 'woff2']);
            $this->addAllowedFile($zip, 'public/version.json');

            // Lançadores e Utilitários
            $files = ['BT_Kernel.vbs', 'BT_Sync_Service.vbs', 'BT_Watchdog.vbs', 'Ligar_Impressora_Local.bat', 'print_bridge.php', 'Ligar_Sistema.bat'];
            foreach ($files as $file) {
                if (!$this->addAllowedFile($zip, $file)) {
                    // Log silencioso ou ignorar se arquivo nÃ£o existir
                }
            }

            if (!$zip->close()) {
                throw new Exception("Falha ao finalizar o arquivo ZIP (PermissÃ£o ou EspaÃ§o)");
            }

            return [
                'success' => true,
                'file_name' => $zipName,
                'download_url' => 'api/v1/updates_download.php?id=' . $zipName,
                'version' => $newVersion,
                'product' => $this->product
            ];
        } catch (Exception $e) {
            \BT\Core\Logger::error("Gerador Erro: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function generateFull(): array
    {
        // [v2.0.1] Prevenção de Erro 500
        set_time_limit(600);
        ini_set('memory_limit', '1G');

        try {
            $prefix = ($this->product === 'BT_QUEUE_ENTERPRISE_LITE') ? 'bt-enterprise-lite' : 'bt-enterprise';
            $zipPath = $this->publicDir . "{$prefix}.zip";

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception("Falha ao criar pacote FULL para {$this->product}");
            }

            // 1. Código e Assets
            $this->addAllowedFile($zip, 'bootstrap.php');
            $this->addTree($zip, 'core', ['php']);
            $this->addTree($zip, 'public', ['php', 'css', 'js', 'png', 'jpg', 'jpeg', 'svg', 'gif', 'ico', 'mp3', 'wav', 'ttf', 'woff', 'woff2']);
            $this->addAllowedFile($zip, 'public/version.json');

            // 2. Configuração e Banco
            $this->addAllowedFile($zip, 'config/config.php');
            $this->addTree($zip, 'database/migrations', ['sql']);
            $this->addAllowedFile($zip, 'database/banco_template.db');
            $this->addAllowedFile($zip, 'database/schema.sql');
            $this->addAllowedFile($zip, 'database/seeds.sql');

            // 3. Suporte
            $this->addTree($zip, 'scripts', ['php', 'sh']);
            $this->addAllowedFile($zip, 'install/apache.conf');

            // 4. Lançadores
            $files = ['BT_Kernel.vbs', 'BT_Sync_Service.vbs', 'BT_Watchdog.vbs', 'Ligar_Impressora_Local.bat', 'print_bridge.php', 'Ligar_Sistema.bat'];
            foreach ($files as $file) {
                $this->addAllowedFile($zip, $file);
            }

            $zip->close();

            return [
                'success' => true,
                'file_name' => "{$prefix}.zip",
                'size' => round(filesize($zipPath) / 1024 / 1024, 2) . ' MB'
            ];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    private function addTree(ZipArchive $zip, string $relativeDir, array $allowedExts): void
    {
        $directory = $this->currentProjectDir . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir);
        if (!is_dir($directory)) return;

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if ($file->isDir()) continue;
            $path = $file->getRealPath();
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen($this->currentProjectDir)));
            $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

            if (in_array($ext, $allowedExts) && !self::isProhibited($relativePath)) {
                $zip->addFile($path, $relativePath);
            }
        }
    }

    private function addAllowedFile(ZipArchive $zip, string $relativePath): void
    {
        $path = $this->currentProjectDir . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if (is_file($path)) {
            $zip->addFile($path, $relativePath);
        }
    }

    private static function isProhibited(string $relativePath): bool
    {
        $normalized = strtolower(str_replace('\\', '/', $relativePath));
        $name = basename($normalized);

        return preg_match('#(^|/)(\.git|99_quarentena|99_backups_antigos|backups?|cache|logs|uploads?|temp_prod_backups|output|runtime|service)(/|$)#', $normalized) === 1
            || $normalized === 'database/banco.db'
            || $normalized === 'database/temp_client.db'
            || str_ends_with($normalized, '.zip')
            || str_contains($name, '.pre_')
            || str_contains($name, '.test')
            || preg_match('/(^|[._-])(bak|old|tmp|temp|swp|swo|orig|rej|testbak)([._-]|$)/', $name) === 1;
    }

    private function updateVersionFile(string $version): void
    {
        $data = ['version' => $version, 'build' => date('Ymd') . '_AUTO_BUILD', 'channel' => 'stable'];
        file_put_contents($this->versionPath, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Limpa pacotes antigos para economizar espaÃ§o (MantÃ©m os 15 mais recentes)
     */
    public function cleanOldPackages(): void
    {
        $files = glob($this->storageDir . 'BT_*.zip');
        if (count($files) > 15) {
            array_multisort(array_map('filemtime', $files), SORT_ASC, $files);
            while (count($files) > 15) {
                $oldFile = array_shift($files);
                @unlink($oldFile);
            }
        }
    }
}

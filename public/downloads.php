<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\App\Auth\Auth;

if (!Auth::check()) {
    header('Location: /login.php');
    exit;
}

$uploadDir = __DIR__ . '/uploads/apks/';
$message = '';
$messageType = '';

// --- AÇÃO: UPLOAD DE APK ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['apk_file'])) {
    $file = $_FILES['apk_file'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($extension !== 'apk') {
        $message = "Erro: Apenas arquivos .apk são permitidos.";
        $messageType = "danger";
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $message = "Erro no upload: " . $file['error'];
        $messageType = "danger";
    } else {
        $safeName = preg_replace("/[^a-zA-Z0-0\._-]/", "_", $file['name']);
        $dest = $uploadDir . $safeName;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $message = "APK enviado com sucesso!";
            $messageType = "success";
        } else {
            $message = "Erro ao mover o arquivo para a pasta de destino.";
            $messageType = "danger";
        }
    }
}

// --- AÇÃO: EXCLUIR APK ---
if (isset($_GET['delete'])) {
    $fileToDelete = basename($_GET['delete']);
    $path = $uploadDir . $fileToDelete;
    if (file_exists($path)) {
        unlink($path);
        header('Location: downloads.php?deleted=1');
        exit;
    }
}

if (isset($_GET['deleted'])) {
    $message = "Arquivo excluído com sucesso.";
    $messageType = "success";
}

// --- LISTAR ARQUIVOS ---
$apks = [];
if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..' || !str_ends_with($f, '.apk')) continue;
        $full = $uploadDir . $f;
        $apks[] = [
            'name' => $f,
            'size' => round(filesize($full) / 1024 / 1024, 2) . ' MB',
            'date' => date('d/m/Y H:i', filemtime($full))
        ];
    }
}

// --- DETECTAR URL BASE ---
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] == 443);
$protocol = $isHttps ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$baseUrl = "$protocol://$host/uploads/apks/";

$pageTitle = 'Central de Downloads APK';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Biblioteca QR Code carregada antes do loop -->
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>

<style>
    .apk-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; margin-top: 30px; }
    .apk-card { background: var(--card); border-radius: 15px; padding: 25px; border: 1px solid var(--border); text-align: center; position: relative; }
    .qr-box { background: #fff; padding: 15px; border-radius: 10px; display: inline-block; margin: 15px 0; }
    .qr-box img { width: 180px; height: 180px; }
    .apk-icon { font-size: 40px; color: var(--primary); margin-bottom: 10px; }
</style>

<main class="bt-main">

    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 style="margin:0;">📲 Central de Downloads APK</h2>
            <p style="color:var(--text2); font-size:14px; margin-top:5px;">Hospede seus APKs e gere QR Codes para instalação rápida.</p>
        </div>
        <button class="bt-button bt-primary" onclick="document.getElementById('uploadBox').style.display='block'">
            <i class="fa-solid fa-cloud-arrow-up"></i> Subir Novo APK
        </button>
    </div>

    <?php if($message): ?>
        <div class="bt-alert bt-<?= $messageType ?>" style="margin-top:20px;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <!-- BOX DE UPLOAD (OCULTO POR PADRÃO) -->
    <section id="uploadBox" class="bt-card" style="margin-top:25px; display:none; border: 2px dashed var(--primary);">
        <h3>Subir Novo Arquivo</h3>
        <form method="POST" enctype="multipart/form-data" style="margin-top:15px;">
            <div class="form-group">
                <input type="file" name="apk_file" class="form-control" accept=".apk" required>
            </div>
            <div style="margin-top:15px; display:flex; gap:10px;">
                <button type="submit" class="bt-button bt-primary">Iniciar Upload</button>
                <button type="button" class="bt-button" onclick="document.getElementById('uploadBox').style.display='none'">Cancelar</button>
            </div>
        </form>
    </section>

    <div class="apk-grid">
        <?php foreach ($apks as $apk):
            $downloadUrl = $baseUrl . $apk['name'];
        ?>
            <div class="apk-card">
                <div class="apk-icon"><i class="fa-brands fa-android"></i></div>
                <h4 style="margin:0; word-break: break-all;"><?= htmlspecialchars($apk['name']) ?></h4>
                <div style="font-size:12px; color:var(--text2); margin-top:5px;">
                    <span><?= $apk['size'] ?></span> • <span><?= $apk['date'] ?></span>
                </div>

                <div class="qr-box" id="qr-<?= md5($apk['name']) ?>">
                    <!-- QR Code será gerado aqui -->
                </div>

                <div style="font-size:10px; color:var(--text2); margin-bottom:15px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    <?= $downloadUrl ?>
                </div>

                <div style="display:flex; gap:10px; justify-content:center;">
                    <a href="<?= $downloadUrl ?>" class="bt-button bt-primary" style="font-size:12px;" download>
                        <i class="fa-solid fa-download"></i> Baixar
                    </a>
                    <a href="?delete=<?= urlencode($apk['name']) ?>" class="bt-button" style="color:var(--danger); font-size:12px;" onclick="return confirm('Excluir este APK permanentemente?')">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </div>

                <script>
                    window.addEventListener('load', function() {
                        try {
                            var qr = qrcode(0, 'M');
                            qr.addData('<?= $downloadUrl ?>');
                            qr.make();
                            document.getElementById('qr-<?= md5($apk['name']) ?>').innerHTML = qr.createImgTag(5);
                        } catch (e) {
                            console.error("Erro ao gerar QR Code:", e);
                            document.getElementById('qr-<?= md5($apk['name']) ?>').innerHTML = "<small style='color:red'>Erro ao gerar QR</small>";
                        }
                    });
                </script>
            </div>
        <?php endforeach; ?>

        <?php if(empty($apks)): ?>
            <div class="bt-card" style="grid-column: 1 / -1; text-align:center; padding:50px;">
                <p style="color:var(--text2);">Nenhum APK disponível. Clique em "Subir Novo APK" para começar.</p>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

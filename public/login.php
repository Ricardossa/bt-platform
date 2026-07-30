<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/autoload.php';

use BT\App\Auth\LoginController;

$pageTitle = 'Login Master';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $controller = new LoginController();

    $resultado = $controller->login(
        trim($_POST['login'] ?? ''),
        $_POST['senha'] ?? ''
    );

    if ($resultado['success']) {
        header('Location: /');
        exit;
    }

    $erro = $resultado['message'];
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="bt-main">

    <section class="bt-card" style="max-width:420px;margin:40px auto;">

        <h2 style="text-align:center;margin-bottom:25px;">
            🔐 Login Master
        </h2>

        <form method="POST">

            <div class="form-group">

                <label>Usuário</label>

                <input
                    type="text"
                    name="login"
                    class="form-control"
                    autocomplete="username"
                    required>

            </div>

            <div class="form-group" style="margin-top:15px;">

                <label>Senha</label>

                <input
                    type="password"
                    name="senha"
                    class="form-control"
                    autocomplete="current-password"
                    required>

            </div>

            <button
                type="submit"
                class="bt-button bt-primary"
                style="width:100%;margin-top:25px;">

                Entrar

            </button>

        </form>

        <?php if ($erro !== ''): ?>

            <div
                style="
                    margin-top:20px;
                    text-align:center;
                    color:#ff6666;
                ">

                <?= htmlspecialchars($erro) ?>

            </div>

        <?php endif; ?>

    </section>

</main>

<?php

require_once __DIR__ . '/includes/footer.php';

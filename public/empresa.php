<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\App\Empresa\EmpresaController;

$controller = new EmpresaController();

$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

$empresaDados = $id > 0
    ? $controller->buscarPorId($id)
    : null;



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $dados = [
        'nome_fantasia' => $_POST['nome_fantasia'] ?? '',
        'razao_social'  => $_POST['razao_social'] ?? '',
        'cnpj'          => $_POST['cnpj'] ?? '',
        'telefone'      => $_POST['telefone'] ?? '',
        'whatsapp'      => $_POST['whatsapp'] ?? '',
        'email'         => $_POST['email'] ?? '',
        'site'          => $_POST['site'] ?? '',
        'endereco'      => $_POST['endereco'] ?? '',
        'cidade'        => $_POST['cidade'] ?? '',
        'estado'        => $_POST['estado'] ?? '',
        'cep'           => $_POST['cep'] ?? '',
        'tema'          => $_POST['tema'] ?? 'blue'
    ];

    if ($id > 0) {
        $controller->atualizar($id, $dados);
    } else {
        $controller->salvar($dados);
    }

    header('Location: empresas.php?salvo=1');
    exit;
}

$pageTitle = 'Empresas';

require_once __DIR__ . '/includes/header.php';
?>

<main class="bt-main">

    <section class="bt-card">

        <h2>🏢 Empresas</h2>

        <?php if (isset($_GET['salvo'])): ?>

        <div class="bt-alert bt-success">
            ✅ Empresa salva com sucesso.
        </div>

        <?php endif; ?>

        <p>
            Cadastro de empresas da BT Queue Platform.
        </p>

        <hr style="margin:20px 0;">

        <form method="POST">
            <?php if ($id > 0): ?>
                <input type="hidden" name="id" value="<?= $id ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Nome Fantasia</label>
                <input
                    type="text"
                    name="nome_fantasia"
                    class="form-control"
                    value="<?= htmlspecialchars($empresaDados['nome_fantasia'] ?? '') ?>">
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label>Razão Social</label>
                <input
                    type="text"
                    name="razao_social"
                    class="form-control"
                    value="<?= htmlspecialchars($empresaDados['razao_social'] ?? '') ?>">
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label>CNPJ</label>
                <input
                    type="text"
                    name="cnpj"
                    class="form-control"
                    value="<?= htmlspecialchars($empresaDados['cnpj'] ?? '') ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top:15px;">
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($empresaDados['telefone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($empresaDados['whatsapp'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label>E-mail</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($empresaDados['email'] ?? '') ?>">
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label>Site</label>
                <input type="text" name="site" class="form-control" value="<?= htmlspecialchars($empresaDados['site'] ?? '') ?>">
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label>Endereço Completo</label>
                <input type="text" name="endereco" class="form-control" value="<?= htmlspecialchars($empresaDados['endereco'] ?? '') ?>">
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 15px; margin-top:15px;">
                <div class="form-group">
                    <label>Cidade</label>
                    <input type="text" name="cidade" class="form-control" value="<?= htmlspecialchars($empresaDados['cidade'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Estado (UF)</label>
                    <input type="text" name="estado" class="form-control" maxlength="2" value="<?= htmlspecialchars($empresaDados['estado'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>CEP</label>
                    <input type="text" name="cep" class="form-control" value="<?= htmlspecialchars($empresaDados['cep'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label>Tema Visual</label>
                <select name="tema" class="form-control">
                    <option value="blue" <?= ($empresaDados['tema'] ?? '') === 'blue' ? 'selected' : '' ?>>Azul</option>
                    <option value="dark" <?= ($empresaDados['tema'] ?? '') === 'dark' ? 'selected' : '' ?>>Escuro</option>
                    <option value="green" <?= ($empresaDados['tema'] ?? '') === 'green' ? 'selected' : '' ?>>Verde</option>
                </select>
            </div>

            <div style="margin-top:25px; display: flex; gap: 10px;">
                <button
                    type="submit"
                    class="bt-button bt-primary">
                    Salvar Empresa
                </button>

                <a href="empresas.php" class="bt-button" style="background: #ccc; text-decoration: none; color: #333; line-height: 1.5; padding: 10px 20px; border-radius: 4px;">
                    Voltar
                </a>
            </div>

        </form>

    </section>

</main>

<?php
require_once __DIR__ . '/includes/footer.php';

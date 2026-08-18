<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';
require_once __DIR__ . '/../bootstrap/autoload.php';

use BT\App\Auth\Auth;
use BT\App\Dashboard\DashboardController;
use BT\App\Services\ActivityService;

if (!Auth::check()) {
    header('Location: /login.php');
    exit;
}

$usuario = Auth::user();
$dashboard = new DashboardController();

// Garante que a timeline nunca durma
$dashboard->ensureActivityIsAlive();

$metrics = $dashboard->getMetrics();
$alerts = $dashboard->getAlerts();
$health = $dashboard->getHealth();
$activities = ActivityService::getRecent(10);
$companies = $dashboard->getQuickCompanies();

function formatTimeAgo($timestamp) {
    if (!$timestamp) return '--:--';
    $diff = time() - strtotime($timestamp);
    if ($diff < 60) return "há $diff s";
    if ($diff < 3600) return "há " . round($diff/60) . " min";
    return date('H:i', strtotime($timestamp));
}

$pageTitle = 'Centro de Operações Master';

require_once __DIR__ . '/includes/header.php';
?>

<style>
    .noc-card { background: var(--card); border-radius: 12px; padding: 20px; border: 1px solid var(--border); box-shadow: var(--shadow); }
    .noc-metric-card { text-align: left; padding: 20px; position: relative; overflow: hidden; }
    .noc-metric-card .stat-number { font-size: 36px; line-height: 1; margin: 10px 0; }
    .noc-metric-card .trend { font-size: 12px; font-weight: bold; opacity: 0.8; }
    .noc-alert-item { padding: 12px 15px; border-radius: 8px; margin-bottom: 8px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
    .noc-alert-critical { background: rgba(255, 77, 77, 0.1); border: 1px solid rgba(255, 77, 77, 0.2); color: #ff4d4d; }
    .noc-alert-warning { background: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.2); color: #ffc107; }
    .noc-health-bar { display: flex; gap: 20px; background: var(--sidebar); padding: 10px 25px; border-radius: 50px; border: 1px solid var(--border); }
    .noc-health-item { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
    .noc-health-dot { width: 8px; height: 8px; border-radius: 50%; }
    .noc-company-card { background: var(--sidebar); border: 1px solid var(--border); border-radius: 10px; padding: 15px; transition: .2s; cursor: pointer; }
    .noc-company-card:hover { border-color: var(--primary); transform: translateY(-2px); }
    .noc-timeline-item { display: flex; gap: 15px; margin-bottom: 15px; position: relative; }
    .noc-timeline-item:not(:last-child):after { content: ''; position: absolute; left: 45px; top: 25px; bottom: -15px; width: 1px; background: var(--border); }
    .noc-user-badge { font-size: 10px; background: var(--primary); color: #fff; padding: 2px 6px; border-radius: 4px; margin-left: 5px; }
</style>

<main class="bt-main">

    <!-- HEADER NOC -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <div>
            <h1 style="font-size:24px; color:var(--text); margin:0;">🚀 BT Platform Enterprise</h1>
            <div style="color:var(--text2); font-size:14px; margin-top:4px;">
                Dashboard Master • <?= date('d/m/Y') ?> • <span id="clock"><?= date('H:i') ?></span>
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:20px;">
            <div style="text-align:right;">
                <div style="color:var(--success); font-weight:bold; font-size:14px; display:flex; align-items:center; gap:8px; justify-content:flex-end;">
                    <div class="noc-health-dot" style="background:var(--success); box-shadow: 0 0 10px var(--success);"></div>
                    STATUS GERAL: OPERACIONAL
                </div>
                <div style="color:var(--text2); font-size:11px; margin-top:2px;">Última sincronização: há 18 seg</div>
            </div>
            <a href="#" class="bt-button bt-primary" style="padding: 10px 20px; font-size: 13px; text-decoration:none;">
                🖥️ Centro de Operações
            </a>
        </div>
    </div>

    <!-- CARDS DE MÉTRICAS -->
    <div class="grid-dashboard" style="margin-bottom: 30px; grid-template-columns: repeat(5, 1fr);">

        <div class="noc-card noc-metric-card">
            <h3 style="font-size:13px; color:var(--text2); text-transform:uppercase;">🏢 Empresas</h3>
            <div class="stat-number"><?= $metrics['empresas']['total'] ?></div>
            <div class="trend" style="color:var(--success);"><?= $metrics['empresas']['trend'] ?></div>
        </div>

        <div class="noc-card noc-metric-card">
            <h3 style="font-size:13px; color:var(--text2); text-transform:uppercase;">💻 Instalações</h3>
            <div class="stat-number"><?= $metrics['instalacoes']['total'] ?></div>
            <div class="trend" style="color:var(--secondary);"><?= $metrics['instalacoes']['trend'] ?></div>
        </div>

        <div class="noc-card noc-metric-card">
            <h3 style="font-size:13px; color:var(--text2); text-transform:uppercase;">🔑 Licenças Ativas</h3>
            <div class="stat-number" style="color:var(--secondary);"><?= $metrics['licencas']['total'] ?></div>
            <div class="trend" style="color:var(--success);"><?= $metrics['licencas']['trend'] ?></div>
        </div>

        <div class="noc-card noc-metric-card">
            <h3 style="font-size:13px; color:var(--text2); text-transform:uppercase;">📱 Online</h3>
            <div class="stat-number" style="color:var(--success);"><?= $metrics['online']['total'] ?></div>
            <div class="trend" style="color:var(--danger);"><?= $metrics['online']['trend'] ?></div>
        </div>

        <div class="noc-card noc-metric-card">
            <h3 style="font-size:13px; color:var(--text2); text-transform:uppercase;">🔄 Sinc. Hoje</h3>
            <div class="stat-number" style="color:#f5a524;"><?= $metrics['syncs_hoje']['total'] ?></div>
            <div class="trend" style="color:var(--success);"><?= $metrics['syncs_hoje']['trend'] ?></div>
        </div>

    </div>

    <!-- ALERTAS CRÍTICOS -->
    <?php if (!empty($alerts)): ?>
    <section class="noc-card" style="border-top: 4px solid var(--danger); margin-bottom: 30px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h2 style="margin:0; font-size:16px; color:var(--danger); font-weight:bold;">🔴 ALERTAS CRÍTICOS (<?= count($alerts) ?>)</h2>
            <a href="#" style="color:var(--text2); font-size:12px; text-decoration:none;">Ver todos os alertas →</a>
        </div>
        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:15px;">
            <?php foreach ($alerts as $a): ?>
                <div class="noc-alert-item <?= $a['tipo'] === 'CRITICAL' ? 'noc-alert-critical' : 'noc-alert-warning' ?>">
                    <span style="font-size:18px;"><?= $a['icon'] ?></span>
                    <span><?= $a['msg'] ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- 🧠 ASSISTENTE NOC DIAMOND (v6.8 AI) -->
    <section class="noc-card" style="margin-bottom: 30px; border-left: 6px solid var(--secondary); background: rgba(29, 180, 255, 0.05);">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2 style="margin:0; font-size:16px; color:var(--secondary); font-weight:bold;"><i class="fa-solid fa-user-shield"></i> BRIEFING DO COMANDANTE (IA)</h2>
            <button id="btnAiBriefing" class="bt-button bt-primary" style="font-size:11px; padding:6px 15px;">
                <i class="fa-solid fa-bolt"></i> GERAR RELATÓRIO ESTRATÉGICO
            </button>
        </div>

        <div id="ai-loading" class="hidden" style="padding: 20px; text-align: center;">
            <i class="fa-solid fa-circle-notch fa-spin" style="color:var(--secondary);"></i>
            <span style="margin-left:10px; color:var(--text2); font-size:13px;">Consultando sinais vitais do império no Xeon...</span>
        </div>

        <div id="ai-briefing-content" class="hidden animate__animated animate__fadeIn" style="margin-top:20px; padding:20px; background:var(--sidebar); border-radius:10px; border:1px solid var(--border); color:#fff; line-height:1.6; font-size:14px; white-space:pre-wrap;">
        </div>
    </section>

    <div class="bt-grid">

        <div style="display:flex; flex-direction:column; gap:25px;">
            <!-- SAÚDE DA PLATAFORMA -->
            <section class="noc-card">
                <h2 style="font-size:16px; margin-bottom:20px;">❤️ Saúde da Plataforma</h2>
                <div class="noc-health-bar">
                    <?php foreach ($health as $h): ?>
                    <div class="noc-health-item">
                        <div class="noc-health-dot" style="background:<?= $h['color'] ?>;"></div>
                        <?= $h['nome'] ?>: <span style="color:<?= $h['color'] ?>;"><?= $h['status'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- EMPRESAS - VISÃO RÁPIDA -->
            <section class="noc-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h2 style="margin:0; font-size:16px;">🏢 Empresas</h2>
                    <a href="empresas.php" style="color:var(--text2); font-size:12px; text-decoration:none;">Ver todas as empresas →</a>
                </div>
                <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:15px;">
                    <?php foreach ($companies as $c): ?>
                    <div class="noc-company-card" onclick="location.href='empresa.php?id=<?= $c['id'] ?>'">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                            <span style="font-weight:bold; color:var(--text);"><?= htmlspecialchars($c['nome_fantasia']) ?></span>
                            <?php
                                $isOnline = (time() - strtotime($c['last_sync'] ?? '')) < 1800;
                            ?>
                            <span style="font-size:10px; background:<?= $isOnline ? 'var(--success)' : '#666' ?>; color:#fff; padding:2px 8px; border-radius:10px;">
                                <?= $isOnline ? 'ONLINE' : 'OFFLINE' ?>
                            </span>
                        </div>
                        <div style="font-size:12px; color:var(--text2); display:flex; flex-direction:column; gap:6px;">
                            <div style="display:flex; gap:10px;">
                                <?php
                                    $prods = array_unique(explode(',', $c['produtos'] ?? ''));
                                    foreach($prods as $p) {
                                        if(!$p) continue;
                                        $icon = str_contains($p, 'BT1') ? '🟢 BT1' : '🟢 BTQ';
                                        echo "<span style='font-weight:bold; color:var(--secondary);'>$icon</span>";
                                    }
                                ?>
                            </div>
                            <div style="display:flex; justify-content:space-between; border-top:1px solid rgba(255,255,255,0.05); padding-top:6px;">
                                <span>📱 <?= $c['total_dispositivos'] ?> Disp.</span>
                                <span>⏱️ <?= formatTimeAgo($c['last_sync']) ?></span>
                            </div>
                            <div style="font-size:10px; color:var(--text2);">
                                <i class="fa-solid fa-key" style="margin-right:5px;"></i> Licença: <?= $c['license_expiry'] ? date('d/m/Y', strtotime($c['license_expiry'])) : 'Pendente' ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <!-- ATIVIDADES EM TEMPO REAL -->
        <section class="noc-card">
            <h2 style="font-size:16px; margin-bottom:25px;">📈 Atividade em Tempo Real</h2>
            <div style="display:flex; flex-direction:column;">
                <?php if (empty($activities)): ?>
                    <p style="color:var(--text2); text-align:center; padding:20px;">Aguardando eventos operacionais...</p>
                <?php else: ?>
                    <?php foreach ($activities as $act): ?>
                    <div class="noc-timeline-item">
                        <div style="font-size:12px; color:var(--text2); min-width:40px; text-align:right;"><?= date('H:i', strtotime($act['data_criacao'])) ?></div>
                        <div style="flex:1;">
                            <div style="font-size:13px; color:var(--text);">
                                <?php
                                    $icon = match($act['tipo']) { 'SUCCESS' => '✅', 'WARNING' => '🟡', 'CRITICAL' => '🔴', default => '🔵' };
                                    echo "<span style='margin-right:8px;'>$icon</span>" . htmlspecialchars($act['mensagem']);
                                ?>
                                <?php if ($act['usuario']): ?>
                                    <span class="noc-user-badge"><?= htmlspecialchars($act['usuario']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </div>

    <!-- RODAPÉ NOC -->
    <div style="margin-top:40px; border-top:1px solid var(--border); padding-top:20px; display:flex; justify-content:space-between; align-items:center; color:var(--text2); font-size:12px;">
        <div style="display:flex; gap:20px;">
            <span>📱 Dispositivos Online: <strong><?= $metrics['online']['total'] ?></strong></span>
            <span style="color:var(--danger);">🔴 Offline: <strong><?= $metrics['online']['offline'] ?></strong></span>
            <span>⏳ Pendentes: <strong>0</strong></span>
        </div>
        <div>
            Última sincronização global há <strong>18 segundos</strong>
        </div>
    </div>

</main>

<script>
    // Relógio em tempo real para o Header NOC
    setInterval(() => {
        const now = new Date();
        document.getElementById('clock').innerText = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
    }, 1000);

    // --- LÓGICA DO ASSISTENTE AI NOC ---
    const btnAi = document.getElementById('btnAiBriefing');
    const aiLoading = document.getElementById('ai-loading');
    const aiContent = document.getElementById('ai-briefing-content');

    if (btnAi) {
        btnAi.addEventListener('click', async () => {
            btnAi.disabled = true;
            aiLoading.classList.remove('hidden');
            aiContent.classList.add('hidden');

            try {
                const res = await fetch('api/v1/master_ai_briefing.php');
                const json = await res.json();

                if (json.success) {
                    aiContent.innerText = json.briefing;
                    aiContent.classList.remove('hidden');
                } else {
                    alert(json.message || "O Comandante está ocupado.");
                }
            } catch (e) {
                alert("Erro ao conectar com a Central de Inteligência.");
            } finally {
                aiLoading.classList.add('hidden');
                btnAi.disabled = false;
            }
        });
    }
</script>

<?php
require_once __DIR__ . '/includes/footer.php';

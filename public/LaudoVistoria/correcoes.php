<?php

require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../g_ver_login.php';
/**
 * relatorio_vistoria.php
 * Gera um relatório HTML (Bootstrap) a partir de um JSON com chaves: erros, avisos, revisaoIA.
 * - Aceita JSON como string ($jsonString), arquivo (?file=path.json) ou POST/GET raw.
 * - Seguro contra XSS via htmlspecialchars.
 */

// ---------------------- ENTRADA DO JSON ---------------------- //
// 1) Opção A: colar o JSON aqui (exemplo):
if(!isset($_GET['id'])){die('id não definido');}

$id=$_GET['id'];
$sql = "SELECT vistoria_correcoes_json FROM agendamentos WHERE id = $id";
$result = $conn->query($sql);
while ($vistoriador_at = $result->fetch_assoc()) {
    $jsonString = $vistoriador_at['vistoria_correcoes_json'];
}

// ---------------------- PARSE & HELPERS ---------------------- //
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$data = json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
$erros     = isset($data['erros']) && is_array($data['erros']) ? $data['erros'] : [];
$avisos    = isset($data['avisos']) && is_array($data['avisos']) ? $data['avisos'] : [];
$revisaoIA = isset($data['revisaoIA']) && is_array($data['revisaoIA']) ? $data['revisaoIA'] : [];

function renderTable(array $items, string $tipo): string {
    if (empty($items)) {
        return '<div class="alert alert-secondary my-3">Nenhum item em '.$tipo.'.</div>';
    }

    // Colunas padrão (presentes no JSON)
    $cols = ['onde' => 'Ambiente', 'regra' => 'Regra', 'trecho' => 'Trecho', 'mensagem' => 'Mensagem'];
    $hasSugestao = false;
    foreach ($items as $it) {
        if (isset($it['sugestao'])) { $hasSugestao = true; break; }
    }
    if ($hasSugestao) $cols['sugestao'] = 'Sugestão';

    ob_start(); ?>
    <div class="table-responsive">
      <table class="table table-sm table-hover align-middle">
        <thead class="table-light">
          <tr>
            <?php foreach ($cols as $k => $label): ?>
              <th scope="col"><?= e($label) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $row): ?>
            <tr>
              <?php foreach ($cols as $k => $label): ?>
                <td><?= isset($row[$k]) ? nl2br(e((string)$row[$k])) : '<span class="text-muted">—</span>' ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php
    return ob_get_clean();
}

$now = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('d/m/Y H:i');
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Relatório de Correções de Vistoria</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #fafafa; }
    .badge-soft { background: rgba(0,0,0,.06); }
    .section-title { display:flex; align-items:center; gap:.5rem; }
    .section-title .count { font-weight:600; }
    .card-shadow { box-shadow: 0 8px 20px rgba(0,0,0,.06); }
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
  </style>
</head>
<body>
<div class="container my-4">
  <header class="mb-4">
    <h1 class="h3">Relatório de Correções de Vistoria</h1>
    <p class="text-muted mb-0">Gerado em <?= e($now) ?> • <span class="mono">America/Sao_Paulo</span></p>
  </header>

  <div class="row g-4">
    <div class="col-12 col-lg-4">
      <div class="card card-shadow">
        <div class="card-body">
          <div class="section-title mb-1"><span class="badge bg-danger">Erros</span><span class="count"><?= count($erros) ?></span></div>
          <p class="text-muted small mb-0">Itens que exigem correção imediata.</p>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-4">
      <div class="card card-shadow">
        <div class="card-body">
          <div class="section-title mb-1"><span class="badge bg-warning text-dark">Avisos</span><span class="count"><?= count($avisos) ?></span></div>
          <p class="text-muted small mb-0">Observações e duplicidades a avaliar.</p>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-4">
      <div class="card card-shadow">
        <div class="card-body">
          <div class="section-title mb-1"><span class="badge bg-info text-dark">Revisão IA</span><span class="count"><?= count($revisaoIA) ?></span></div>
          <p class="text-muted small mb-0">Sugestões de padronização e estilo.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Seção Erros -->
  <section class="mt-4">
    <h2 class="h5 mb-3">
      <span class="badge bg-danger me-2">Erros</span>
      <small class="text-muted">(<?= count($erros) ?>)</small>
    </h2>
    <?= renderTable($erros, 'Erros'); ?>
  </section>

  <!-- Seção Avisos -->
  <section class="mt-4">
    <h2 class="h5 mb-3">
      <span class="badge bg-warning text-dark me-2">Avisos</span>
      <small class="text-muted">(<?= count($avisos) ?>)</small>
    </h2>
    <?= renderTable($avisos, 'Avisos'); ?>
  </section>

  <!-- Seção Revisão IA -->
  <section class="mt-4 mb-5">
    <h2 class="h5 mb-3">
      <span class="badge bg-info text-dark me-2">Revisão IA</span>
      <small class="text-muted">(<?= count($revisaoIA) ?>)</small>
    </h2>
    <?= renderTable($revisaoIA, 'Revisão IA'); ?>
  </section>

  <footer class="py-4 text-center text-muted small">
    <div>Relatório gerado automaticamente a partir de JSON</div>
  </footer>
</div>
</body>
</html>

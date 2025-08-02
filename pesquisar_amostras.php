<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

$pagina_ativa = 'pesquisar_amostras'; 
require_once 'header.php';
require_once 'conexao.php';

if (!isset($_SESSION['representante_email'])) {
  header('Location: index.html');
  exit();
}

$isAdmin = (isset($_SESSION['admin']) && $_SESSION['admin'] == 1);
$limite = 25;
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina - 1) * $limite;

// --- NOVIDADE: Captura a query string atual para usar nos links de ação ---
$queryString = http_build_query($_GET);

$campos_filtros = [
  'produto' => ['ce.produto', 'ce.codigo'], 'cliente_razao_social' => 'cc.razao_social',
  'responsavel_pedido' => 'pa.responsavel_pedido', 'status' => 'pa.status',
  'data_inicial' => 'pa.data_pedido', 'data_final' => 'pa.data_pedido'
];

$filtros = [];
$parametros = [];

foreach ($campos_filtros as $campo_get => $colunas_db) {
  if (!empty($_GET[$campo_get])) {
    if ($campo_get === 'data_inicial') {
      $filtros[] = "$colunas_db >= :data_inicial";
      $parametros[':data_inicial'] = $_GET[$campo_get] . ' 00:00:00';
    } elseif ($campo_get === 'data_final') {
      $filtros[] = "$colunas_db <= :data_final";
      $parametros[':data_final'] = $_GET[$campo_get] . ' 23:59:59';
    } elseif ($campo_get === 'produto') {
      $filtros[] = "($colunas_db[0] LIKE :produto OR $colunas_db[1] LIKE :produto)";
      $parametros[":produto"] = "%" . $_GET[$campo_get] . "%";
    } else {
      $filtros[] = "$colunas_db LIKE :$campo_get";
      $parametros[":$campo_get"] = "%" . $_GET[$campo_get] . "%";
    }
  }
}

$sql_base = "FROM itens_pedido_amostra AS ipa JOIN pedidos_amostra AS pa ON ipa.id_pedido_amostra = pa.id JOIN cot_clientes AS cc ON pa.id_cliente = cc.id JOIN cot_estoque AS ce ON ipa.id_produto = ce.id";
$where = $filtros ? "WHERE " . implode(" AND ", $filtros) : "";
$orderBy = " ORDER BY pa.data_pedido DESC, ce.produto ASC ";

$totalStmt = $pdo->prepare("SELECT COUNT(ipa.id) $sql_base $where");
$totalStmt->execute($parametros);
$totalResultados = $totalStmt->fetchColumn();

$sql = "SELECT pa.id as pedido_id, pa.numero_referencia, pa.responsavel_pedido, pa.data_pedido, pa.status, cc.razao_social, ce.produto AS nome_produto, ce.codigo AS codigo_produto, ipa.quantidade, ce.unidade, ipa.fabricante, ipa.necessita_fracionamento, ipa.disponivel_estoque $sql_base $where $orderBy LIMIT :limite OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($parametros as $key => &$value) { $stmt->bindParam($key, $value); }
$stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalPaginas = $totalResultados > 0 ? ceil($totalResultados / $limite) : 0;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Resultados da Pesquisa de Itens de Amostras</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    .action-buttons a, .action-buttons button { margin-right: 5px; }
    .badge { font-size: 0.8em; min-width: 80px; }
    .approval-buttons button { min-width: 90px; margin: 2px; }
    td { vertical-align: middle; }
    th { text-align: center; }
  </style>
</head>

<body>
  <div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="mb-0">Resultados da Pesquisa de Itens</h2>
      <div><a href="filtrar_amostras.php" class="btn btn-secondary">Nova Pesquisa</a></div>
    </div>
    <p>Total de <strong>itens</strong> encontrados: <strong><?= $totalResultados ?></strong></p>

    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th>Nº Pedido</th> <th>Produto Solicitado</th> <th>Fabricante</th> <th>Quantidade</th>
            <th>Estoque?</th> <th>Fracionar?</th> <th>Cliente</th> <th>Representante</th>
            <th>Data</th> <th>Status Pedido</th> <th>Ações do Pedido</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($resultados): ?>
            <?php foreach ($resultados as $item): ?>
              <tr>
                <td class="text-center"><?= htmlspecialchars($item['numero_referencia']) ?></td>
                <td><strong><?= htmlspecialchars($item['nome_produto']) ?></strong><br><small class="text-muted">Cód: <?= htmlspecialchars($item['codigo_produto']) ?></small></td>
                <td><?= htmlspecialchars($item['fabricante']) ?></td>
                <td class="text-center"><?= htmlspecialchars(number_format($item['quantidade'], 3, ',', '.')) . ' ' . htmlspecialchars($item['unidade']) ?></td>
                <td class="text-center">
                    <?php $estoque_txt = $item['disponivel_estoque'] ?? 'NÃO'; $estoque_badge = ($estoque_txt == 'SIM') ? 'bg-success' : 'bg-danger'; ?>
                    <span class="badge <?= $estoque_badge ?>"><?= htmlspecialchars($estoque_txt) ?></span>
                </td>
                <td class="text-center">
                    <?php $fracionar_txt = $item['necessita_fracionamento'] ?? 'NÃO'; $fracionar_badge = ($fracionar_txt == 'SIM') ? 'bg-warning text-dark' : 'bg-secondary'; ?>
                    <span class="badge <?= $fracionar_badge ?>"><?= htmlspecialchars($fracionar_txt) ?></span>
                </td>
                <td><?= htmlspecialchars($item['razao_social']) ?></td>
                <td><?= htmlspecialchars($item['responsavel_pedido']) ?></td>
                <td class="text-center"><?= date('d/m/Y H:i', strtotime($item['data_pedido'])) ?></td>
                <td class="text-center">
                    <?php
                    $status = $item['status'] ?? 'Pendente';
                    if ($isAdmin && strtolower($status) === 'pendente') {
                        // --- NOVIDADE: Botões que abrem o modal com data-attributes ---
                        $return_url = urlencode("pesquisar_amostras.php?" . $queryString);
                    ?>
                        <div class="approval-buttons">
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#confirmationModal" 
                                data-action-url="atualizar_status_amostra.php?id=<?= $item['pedido_id'] ?>&status=Enviado&return_url=<?= $return_url ?>"
                                data-action-type="aprovar"
                                data-pedido-num="<?= htmlspecialchars($item['numero_referencia']) ?>">
                                Aprovar
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmationModal" 
                                data-action-url="atualizar_status_amostra.php?id=<?= $item['pedido_id'] ?>&status=Cancelado&return_url=<?= $return_url ?>"
                                data-action-type="reprovar"
                                data-pedido-num="<?= htmlspecialchars($item['numero_referencia']) ?>">
                                Reprovar
                            </button>
                        </div>
                    <?php
                    } else {
                        $badge_class = 'bg-secondary';
                        switch (strtolower($status)) {
                            case 'pendente': $badge_class = 'bg-warning text-dark'; break;
                            case 'enviado': $badge_class = 'bg-success'; break;
                            case 'cancelado': $badge_class = 'bg-danger'; break;
                        }
                        echo '<span class="badge ' . $badge_class . '">' . htmlspecialchars(ucfirst($status)) . '</span>';
                    }
                    ?>
                </td>
                <td class="text-center action-buttons">
                    <a href="alterar_amostra.php?id=<?= $item['pedido_id']; ?>" class="btn btn-sm btn-warning" title="Alterar Pedido Completo">Alterar</a>
                    <a href="gerar_pdf_amostra.php?id=<?= $item['pedido_id']; ?>" class="btn btn-sm btn-info" title="Gerar PDF do Pedido" target="_blank">PDF</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="11" class="text-center">Nenhum item encontrado para os filtros aplicados.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalPaginas > 1): ?>
    <nav class="mt-4"><ul class="pagination justify-content-center">
        <?php if ($pagina > 1): ?><li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>">Anterior</a></li><?php endif; ?>
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?><li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"><?= $i ?></a></li><?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?><li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>">Próxima</a></li><?php endif; ?>
    </ul></nav>
    <?php endif; ?>
  </div>

<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmationModalLabel">Confirmar Ação</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="confirmationModalBody">
        Você tem certeza que deseja executar esta ação?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <a href="#" id="confirmButton" class="btn">Confirmar</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var confirmationModal = document.getElementById('confirmationModal');
    confirmationModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Botão que acionou o modal
        
        // Extrai as informações dos data-* attributes
        var actionUrl = button.getAttribute('data-action-url');
        var actionType = button.getAttribute('data-action-type');
        var pedidoNum = button.getAttribute('data-pedido-num');
        
        var modalTitle = confirmationModal.querySelector('.modal-title');
        var modalBody = confirmationModal.querySelector('.modal-body');
        var confirmButton = confirmationModal.querySelector('#confirmButton');
        
        // Personaliza o modal com base na ação
        if (actionType === 'aprovar') {
            modalTitle.textContent = 'Confirmar Aprovação';
            modalBody.innerHTML = `Tem certeza que deseja <strong>APROVAR</strong> o pedido de amostra Nº <strong>${pedidoNum}</strong>?<br>O status será alterado para "Enviado".`;
            confirmButton.className = 'btn btn-success';
        } else if (actionType === 'reprovar') {
            modalTitle.textContent = 'Confirmar Reprovação';
            modalBody.innerHTML = `Tem certeza que deseja <strong>REPROVAR</strong> o pedido de amostra Nº <strong>${pedidoNum}</strong>?<br>O status será alterado para "Cancelado".`;
            confirmButton.className = 'btn btn-danger';
        }
        
        // Define o link de confirmação
        confirmButton.setAttribute('href', actionUrl);
    });
});
</script>

</body>
</html>
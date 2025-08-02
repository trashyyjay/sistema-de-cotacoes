<?php
session_start();
date_default_timezone_set('America/Sao_Paulo'); 

// Define a página ativa para o menu
$pagina_ativa = 'consultar_orcamentos'; 

require_once 'header.php'; 
require_once 'conexao.php';

if (!isset($_SESSION['representante_email'])) {
  header('Location: index.html');
  exit();
}

// Configurações de Paginação
$por_pagina = 20;
$pagina_atual = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT, [
    'options' => ['default' => 1, 'min_range' => 1]
]);
$offset = ($pagina_atual - 1) * $por_pagina;

// Total de orçamentos
$total = $pdo->query("SELECT COUNT(DISTINCT `NUM_ORCAMENTO`) FROM cot_cotacoes_importadas WHERE `NUM_ORCAMENTO` IS NOT NULL AND `NUM_ORCAMENTO` != ''")->fetchColumn();
$total_paginas = ceil($total / $por_pagina);

// Busca os orçamentos da página atual
try {
  $stmt = $pdo->prepare("
  SELECT 
    c1.`NUM_ORCAMENTO`, 
    c1.`RAZÃO SOCIAL`, 
    c1.`UF`, 
    c1.`DATA`,
    (SELECT c2.`COTADO_POR` FROM cot_cotacoes_importadas c2 
     WHERE c2.`NUM_ORCAMENTO` = c1.`NUM_ORCAMENTO` 
     LIMIT 1) AS `COTADO_POR`
  FROM cot_cotacoes_importadas c1
  WHERE c1.`NUM_ORCAMENTO` IS NOT NULL AND c1.`NUM_ORCAMENTO` != ''
  GROUP BY c1.`NUM_ORCAMENTO`, c1.`RAZÃO SOCIAL`, c1.`UF`, c1.`DATA`
  ORDER BY c1.`NUM_ORCAMENTO` DESC
  LIMIT :offset, :por_pagina
  ");
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->bindValue(':por_pagina', $por_pagina, PDO::PARAM_INT);
  $stmt->execute();
  $orcamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "Erro na consulta: " . $e->getMessage();
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Consultar Orçamentos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Consultar Orçamentos</h2>
    </div>
    
    <div id="loadingSpinner" class="text-center my-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2">Buscando orçamentos, por favor aguarde...</p>
    </div>

    <div id="contentContainer" style="display: none;">
        <?php if (isset($_GET['atualizado'])): ?>
          <div class="alert alert-success">✅ Orçamento atualizado com sucesso.</div>
        <?php endif; ?>

        <table class="table table-bordered table-hover">
          <thead class="table-light">
            <tr>
              <th>Nº Orçamento</th> <th>Razão Social</th> <th>UF</th>
              <th>Data</th> <th>Cotado por</th> <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orcamentos as $orc): ?>
              <?php if (!empty($orc['NUM_ORCAMENTO'])): ?>
                <tr>
                  <td><?= htmlspecialchars($orc['NUM_ORCAMENTO']) ?></td>
                  <td><?= htmlspecialchars($orc['RAZÃO SOCIAL']) ?></td>
                  <td><?= htmlspecialchars($orc['UF']) ?></td>
                  <td><?= date('d/m/Y', strtotime($orc['DATA'])) ?></td>
                  <td><?= isset($orc['COTADO_POR']) ? htmlspecialchars($orc['COTADO_POR']) : '-' ?></td>
                  
                  <td class="text-center">
                    <a href="atualizar_orcamento.php?num=<?= urlencode($orc['NUM_ORCAMENTO']) ?>" class="btn btn-sm btn-primary">Abrir</a>
                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalExcluir<?= $orc['NUM_ORCAMENTO'] ?>">Excluir</button>
                  </td>

                </tr>
                <div class="modal fade" id="modalExcluir<?= $orc['NUM_ORCAMENTO'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $orc['NUM_ORCAMENTO'] ?>" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="modalLabel<?= $orc['NUM_ORCAMENTO'] ?>">Confirmar Exclusão</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                      </div>
                      <div class="modal-body">Tem certeza que deseja excluir o orçamento <strong><?= $orc['NUM_ORCAMENTO'] ?></strong>?</div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <a href="excluir_orcamento.php?num_orcamento=<?= urlencode($orc['NUM_ORCAMENTO']) ?>" class="btn btn-danger">Excluir</a>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if ($total_paginas > 1): ?>
        <nav>
          <ul class="pagination justify-content-center mt-4">
            <?php if ($pagina_atual > 1): ?>
              <li class="page-item"><a class="page-link" href="?pagina=<?= $pagina_atual - 1 ?>">Anterior</a></li>
            <?php else: ?>
              <li class="page-item disabled"><span class="page-link">Anterior</span></li>
            <?php endif; ?>
            <?php
            $maxLinks = 2;
            $start = max(1, $pagina_atual - $maxLinks);
            $end = min($total_paginas, $pagina_atual + $maxLinks);
            if ($start > 1) {
              echo '<li class="page-item"><a class="page-link" href="?pagina=1">1</a></li>';
              if ($start > 2) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; }
            }
            for ($i = $start; $i <= $end; $i++) {
              $active = ($i == $pagina_atual) ? 'active' : '';
              echo '<li class="page-item ' . $active . '"><a class="page-link" href="?pagina=' . $i . '">' . $i . '</a></li>';
            }
            if ($end < $total_paginas) {
              if ($end < $total_paginas - 1) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; }
              echo '<li class="page-item"><a class="page-link" href="?pagina=' . $total_paginas . '">' . $total_paginas . '</a></li>';
            }
            ?>
            <?php if ($pagina_atual < $total_paginas): ?>
              <li class="page-item"><a class="page-link" href="?pagina=<?= $pagina_atual + 1 ?>">Próxima</a></li>
            <?php else: ?>
              <li class="page-item disabled"><span class="page-link">Próxima</span></li>
            <?php endif; ?>
          </ul>
        </nav>
        <?php endif; ?>
    </div>

    <script>
        window.onload = function() {
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('contentContainer').style.display = 'block';
        };
    </script>
</body>
</html>
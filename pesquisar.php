<?php
require_once 'conexao.php';
session_start();

function formatarOrigem($codigoOrigem) {
    $rotulos = [
        '0' => 'NACIONAL',
        '1' => 'IMPORTADO',
        '6' => 'LISTA CAMEX'
    ];
    return $rotulos[trim($codigoOrigem)] ?? htmlspecialchars($codigoOrigem);
}

require_once 'header.php'; 

if (!isset($_SESSION['representante_email'])) {
  header('Location: index.html');
  exit();
}

$limite = 25;
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT, [
    'options' => ['default' => 1, 'min_range' => 1]
]);
$offset = ($pagina - 1) * $limite;

$campos = [
  'suframa' => 'SUFRAMA', 'razao_social' => 'RAZÃO SOCIAL', 'uf' => 'UF',
  'data_inicial' => 'DATA', 'data_final' => 'DATA', 'codigo' => 'COD DO PRODUTO',
  'produto' => 'PRODUTO', 'origem' => 'ORIGEM', 'embalagem' => 'EMBALAGEM_KG',
  'ncm' => 'NCM', 'volume' => 'VOLUME', 'ipi' => 'IPI %',
  'preco_net' => 'PREÇO NET USD/KG', 'icms' => 'ICMS', 'preco_full' => 'PREÇO FULL USD/KG',
  'disponibilidade' => 'DISPONIBilidade', 'cotado_por' => 'COTADO_POR', 'dolar' => 'DOLAR COTADO',
  'suspensao_ipi' => 'SUSPENCAO_IPI', 'observacoes' => 'OBSERVAÇÕES'
];

$filtros = [];
$parametros = [];

foreach ($campos as $campo => $coluna) {
  if (!empty($_GET[$campo])) {
    if ($campo === 'data_inicial') {
      $filtros[] = "`$coluna` >= :data_inicial";
      $parametros[':data_inicial'] = $_GET[$campo];
    } elseif ($campo === 'data_final') {
      $filtros[] = "`$coluna` <= :data_final";
      $parametros[':data_final'] = $_GET[$campo];
    } else {
      $filtros[] = "`$coluna` LIKE :$campo";
      $parametros[":{$campo}"] = "%" . $_GET[$campo] . "%";
    }
  }
}

$where = $filtros ? "WHERE " . implode(" AND ", $filtros) : "";
$orderBy = " ORDER BY `NUM_ORCAMENTO` DESC ";

$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM cot_cotacoes_importadas $where $orderBy LIMIT :limite OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($parametros as $key => $value) {
  $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalResultados = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
$totalPaginas = ceil($totalResultados / $limite);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Resultados da Pesquisa</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <style>
    .pagination .page-item.disabled .page-link { color: #6c757d; pointer-events: none; }
    .pagination .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; color: white; }
    table { width: 100% !important; }
    td, tr {
      font-family: "Montserrat", sans-serif; font-optical-sizing: auto; font-size: 10px;
      text-align: center; vertical-align: middle;
    }
    .tabela-orcamento th { font-weight: bold; font-size: 15px; }
    .tabela-orcamento td { font-size: 14px; }
    .action-buttons a { margin: 0 2px; }
  </style>
</head>
<body>
  <div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="mb-0">Resultados da Pesquisa</h2>
    </div>

    <div class="alert alert-danger text-center fw-bold">
      PROIBIDO O COMPARTILHAMENTO DESTE DOCUMENTO COM PESSOAS EXTERNAS
    </div>

    <p>Total de resultados: <strong><?= $totalResultados ?></strong></p>

    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>PESQUISA MAINÔ</th> <th>CLIENTE</th> <th>UF</th> <th>DATA</th>
            <th>CÓDIGO</th> <th>PRODUTO</th> <th>ORIGEM</th> <th>EMBALAGEM</th>
            <th>NCM</th> <th>VOLUME</th> <th>IPI %</th> <th>PREÇO NET USD/KG</th>
            <th>ICMS</th> <th>PREÇO FULL USD/KG</th> <th>DISPONIBILIDADE</th>
            <th>COTADO POR</th> <th>DOLAR COTADO</th> <th>SUSPENSÃO DE IPI</th>
            <th>SUFRAMA</th>
            <th>AÇÕES</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resultados as $linha): ?>
            <tr>
              <td>
                <a href="https://app.maino.com.br/produto_estoques?utf8=✓&filtro=true&codigo=<?= urlencode($linha['COD DO PRODUTO']) ?>&descricao=<?= urlencode($linha['PRODUTO']) ?>&commit=Filtrar"
                  target="_blank" class="btn btn-sm btn-outline-primary">
                  Visualizar movimentações
                </a>
              </td>
              <td><?= htmlspecialchars($linha['RAZÃO SOCIAL']) ?></td>
              <td><?= htmlspecialchars($linha['UF']) ?></td>
              <td><?= date('d/m/Y', strtotime($linha['DATA'])) ?></td>
              <td><?= htmlspecialchars($linha['COD DO PRODUTO']) ?></td>
              <td><?= htmlspecialchars($linha['PRODUTO']) ?></td>
              <td><?= formatarOrigem($linha['origem'] ?? $linha['ORIGEM'] ?? '') ?></td>
              <td><?= htmlspecialchars($linha['EMBALAGEM_KG']) ?></td>
              <td><?= htmlspecialchars($linha['NCM']) ?></td>
              <td><?= htmlspecialchars($linha['VOLUME']) ?></td>
              <td><?= htmlspecialchars($linha['IPI %']) ?></td>
              <td><?= 'USD$ ' . number_format((float) $linha['PREÇO NET USD/KG'], 2, '.', ',') ?></td>
              <td><?= htmlspecialchars($linha['ICMS']) ?></td>
              <td><?= 'USD$ ' . number_format((float) $linha['PREÇO FULL USD/KG'], 2, '.', ',') ?></td>
              <td><?= htmlspecialchars($linha['DISPONIBILIDADE']) ?></td>
              <td><?= htmlspecialchars($linha['COTADO_POR']) ?></td>
              <td><?= htmlspecialchars($linha['DOLAR COTADO']) ?></td>
              <td><?= htmlspecialchars($linha['SUSPENCAO_IPI']) ?></td>
              <td><?= htmlspecialchars($linha['SUFRAMA']) ?></td>
              
              <td class="action-buttons">
                <a href="gerar_pdf_orcamento.php?num=<?= urlencode($linha['NUM_ORCAMENTO']) ?>" class="btn btn-sm btn-info" target="_blank" title="Ver PDF">Ver</a>
                <a href="atualizar_orcamento.php?num=<?= urlencode($linha['NUM_ORCAMENTO']) ?>" class="btn btn-sm btn-warning" title="Alterar Itens">Alterar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <nav>
      <ul class="pagination justify-content-center">
        <?php if ($pagina > 1): ?>
          <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>">Anterior</a></li>
        <?php endif; ?>
        <?php
        $maxLinks = 2;
        $start = max(1, $pagina - $maxLinks);
        $end = min($totalPaginas, $pagina + $maxLinks);
        if ($start > 1) {
          echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['pagina' => 1])) . '">1</a></li>';
          if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        for ($i = $start; $i <= $end; $i++) {
          $active = $pagina === $i ? 'active' : '';
          echo '<li class="page-item ' . $active . '"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['pagina' => $i])) . '">' . $i . '</a></li>';
        }
        if ($end < $totalPaginas) {
          if ($end < $totalPaginas - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
          echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['pagina' => $totalPaginas])) . '">' . $totalPaginas . '</a></li>';
        }
        if ($pagina < $totalPaginas): ?>
          <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>">Próxima</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
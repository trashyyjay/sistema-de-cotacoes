<?php
require_once 'conexao.php';
// Carrega todos os produtos para o modal de busca
$produtos = $pdo->query("SELECT codigo, produto, origem, ncm, ipi FROM cot_estoque ORDER BY produto ASC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);





$num_orcamento = $_GET['num'] ?? null;
if (!$num_orcamento) {
  die("Número do orçamento não informado.");
}

$stmt = $pdo->prepare("SELECT * FROM cot_cotacoes_importadas WHERE NUM_ORCAMENTO = ?");
$stmt->execute([$num_orcamento]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Atualizar Orçamento</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

  <h3>Editar Itens do Orçamento: <?= htmlspecialchars($num_orcamento) ?></h3>
  <table class="table table-bordered table-hover mt-4">
    <thead class="table-light">
      <tr>
        <th>Produto</th>
        <th>Origem</th>
        <th>Volume</th>
        <th>Preço NET</th>
        <th>ICMS</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($itens as $i => $item): ?>
        <tr>
          <td><?= htmlspecialchars($item['PRODUTO']) ?></td>
          <td><?= htmlspecialchars($item['ORIGEM']) ?></td>
          <td><?= htmlspecialchars($item['VOLUME']) ?></td>
          <td><?= htmlspecialchars($item['PREÇO NET USD/KG']) ?></td>
          <td><?= htmlspecialchars($item['ICMS']) ?></td>
          <td>
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editarItem<?= $i ?>">Alterar</button>
            <button class="btn btn-sm btn-danger">Excluir</button>
          </td>
        </tr>

        <!-- Modal de edição -->
<div class="modal fade" id="editarItem<?= $i ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <form method="post" action="salvar_item.php">
      <input type="hidden" name="id_linha" value="<?= $item['id'] ?>">
      <input type="hidden" name="num_orcamento" value="<?= $num_orcamento ?>">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar Item do Orçamento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-2 align-items-end">
            <div class="col-md-2">
              <label>Código</label>
              <div class="input-group">
                <input type="text" name="codigo" class="form-control" value="<?= htmlspecialchars($item['COD DO PRODUTO']) ?>" required readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="abrirModalProdutoModalEditar(this)">🔍</button>
              </div>
            </div>
            <div class="col-md-3">
              <label>Produto</label>
              <input type="text" name="produto" class="form-control" value="<?= htmlspecialchars($item['PRODUTO']) ?>" required readonly>
            </div>
            <div class="col-md-2">
              <label>Unidade</label>
              <input type="text" name="unidade" class="form-control" value="<?= htmlspecialchars($item['UNIDADE']) ?>" readonly>
            </div>
            <div class="col-md-2">
              <label>Origem</label>
              <input type="text" name="origem" class="form-control" value="<?= htmlspecialchars($item['ORIGEM']) ?>" readonly>
            </div>
            <div class="col-md-2">
              <label>NCM</label>
              <input type="text" name="ncm" class="form-control" value="<?= htmlspecialchars($item['NCM']) ?>" readonly>
            </div>
            <div class="col-md-2">
              <label>Volume</label>
              <input type="text" name="volume" class="form-control" value="<?= htmlspecialchars($item['VOLUME']) ?>">
            </div>
            <div class="col-md-2">
              <label>Embalagem</label>
              <input type="text" name="embalagem" class="form-control" value="<?= htmlspecialchars($item['EMBALAGEM_KG']) ?>">
            </div>
            <div class="col-md-2">
              <label>IPI</label>
              <input type="text" name="ipi" class="form-control" value="<?= htmlspecialchars($item['IPI %']) ?>">
            </div>
            <div class="col-md-2">
              <label>ICMS</label>
              <input type="text" name="icms" class="form-control" value="<?= htmlspecialchars($item['ICMS']) ?>">
            </div>
            <div class="col-md-3">
              <label>Disponibilidade</label>
              <input type="text" name="disponibilidade" class="form-control" value="<?= htmlspecialchars($item['DISPONIBILIDADE']) ?>">
            </div>
            <div class="col-md-2">
              <label>Preço Net</label>
              <input type="text" name="preco_net" class="form-control" value="<?= htmlspecialchars($item['PREÇO NET USD/KG']) ?>">
            </div>
            <div class="col-md-2">
              <label>Preço Full</label>
              <input type="text" name="preco_full" class="form-control" value="<?= htmlspecialchars($item['PREÇO FULL USD/KG']) ?>">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Salvar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

      <?php endforeach; ?>
    </tbody>
  </table>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    let produtoBtnReferencia = null;
function abrirModalProduto(button) {
  produtoBtnReferencia = button.closest('.item-row');
  const modal = new bootstrap.Modal(document.getElementById('modalProdutos'));
  modal.show();
}
</script>
<div class="modal fade" id="modalProdutos" tabindex="-1" aria-labelledby="modalProdutosLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalProdutosLabel">Selecionar Produto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
      <div class="input-group mb-3">
  <input type="text" id="filtro-produto" class="form-control" placeholder="Filtrar produto...">
  <button class="btn btn-primary" onclick="filtrarProdutos()">Filtrar</button>
</div>

<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
  <table class="table table-bordered table-sm" id="tabela-produtos">
    <thead>
      <tr>
        <th>Código</th>
        <th>Produto</th>
        <th>Origem</th>
        <th>NCM</th>
        <th>IPI</th>
        <th>Ação</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($produtos as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['codigo']) ?></td>
          <td><?= htmlspecialchars($p['produto']) ?></td>
          <td><?= $p['origem'] == 0 ? 'NACIONAL' : ($p['origem'] == 1 ? 'IMPORTADO' : ($p['origem'] == 6 ? 'LISTA CAMEX' : $p['origem'])) ?></td>
          <td><?= htmlspecialchars($p['ncm']) ?></td>
          <td><?= htmlspecialchars($p['ipi']) ?>%</td>
          <td>
            <button class="btn btn-success btn-sm" onclick="selecionarProduto(this)">Selecionar</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

      </div>
    </div>
  </div>
</div>
<script>
function filtrarProdutos() {
  const filtro = document.getElementById('filtro-produto').value.toLowerCase();
  const linhas = document.querySelectorAll('#tabela-produtos tbody tr');
  linhas.forEach(tr => {
    const texto = tr.innerText.toLowerCase();
    tr.style.display = texto.includes(filtro) ? '' : 'none';
  });
}

function selecionarProduto(botao) {
  const linha = botao.closest('tr');
  const colunas = linha.querySelectorAll('td');

  const codigo = colunas[0].innerText.trim();
  const produto = colunas[1].innerText.trim();
  const origemTexto = colunas[2].innerText.trim();
  const origem = origemTexto === 'NACIONAL' ? 0 : origemTexto === 'IMPORTADO' ? 1 : 6;
  const ncm = colunas[3].innerText.trim();
  const ipi = colunas[4].innerText.trim().replace('%', '');

  console.log("⛏️ DEBUG:", {
    codigo, produto, origem, ncm, ipi,
    ref: produtoBtnReferencia,
    campoProduto: produtoBtnReferencia?.querySelector('[name=\"produto\"]')
  });

  if (produtoBtnReferencia) {
    produtoBtnReferencia.querySelector('[name="codigo"]').value = codigo;
    produtoBtnReferencia.querySelector('[name="produto"]').value = produto;
    produtoBtnReferencia.querySelector('[name="origem"]').value = origem;
    produtoBtnReferencia.querySelector('[name="ncm"]').value = ncm;
    produtoBtnReferencia.querySelector('[name="ipi"]').value = ipi;
  }

  const modal = bootstrap.Modal.getInstance(document.getElementById('modalProdutos'));
  modal.hide();
}


function abrirModalProdutoModalEditar() {
  produtoBtnReferencia = document.querySelector('#modalEditarItem .item-row');
  const modal = new bootstrap.Modal(document.getElementById('modalProdutos'));
  modal.show();
}

</script>

</body>
</html>

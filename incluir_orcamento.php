<?php
session_start();
date_default_timezone_set('America/Sao_Paulo'); // Define fuso horário
// !!! DEFINA A PÁGINA ATIVA AQUI !!!
$pagina_ativa = 'incluir_orcamento'; // Exemplo para consultar_amostras.php
// Use: 'incluir_amostra' para incluir_ped_amostras.php
// Use: 'gerenciar_cliente' para gerenciar_cliente.php
// Use: 'incluir_orcamento' para incluir_orcamento.php
// Use: 'filtrar' para filtrar.php
// Use: 'consultar_orcamentos' para consultar_orcamentos.php
// Use: 'previsao' para previsao.php

require_once 'header.php'; // Inclui o header
require_once 'conexao.php'; // Conexão PDO

if (!isset($_SESSION['representante_email'])) {
  header('Location: index.html');
  exit();
}

$select_disponibilidade = '<select name="itens[${index}][disponibilidade]" class="form-select" required>';
$select_disponibilidade .= '<option value="">Selecione</option>';
$disp = $pdo->query("SELECT prazo FROM cot_disponibilidade")->fetchAll(PDO::FETCH_ASSOC);
foreach ($disp as $d) {
  $prazo = htmlspecialchars($d['prazo']);
  $select_disponibilidade .= "<option value=\"$prazo\">$prazo</option>";
}
$select_disponibilidade .= '</select>';

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Incluir Orçamento</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .form-section {
      background-color: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .remove-item {
      cursor: pointer;
      color: red;
      font-weight: bold;
    }

    .custom-remove-btn {
      background-color: #dc3545;
      /* vermelho Bootstrap */
      color: white;
      border: 1px solid #dc3545;
      transition: all 0.2s ease-in-out;
    }

    .custom-remove-btn:hover {
      background-color: white;
      color: #dc3545;
    }
  </style>
</head>

<body>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Novo Orçamento</h2>


    </div>

    <form method="POST" action="salvar_orcamento.php">
      <div class="form-section">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Cliente</label>
            <div class="input-group">
              <input type="text" name="cliente" id="cliente" class="form-control" readonly required>
              <button class="btn btn-outline-secondary" type="button"
                onclick="abrirModalCliente()">🔍</button>
            </div>
          </div>

          <div class="col-md-1">
            <label class="form-label">UF</label>
            <input type="text" name="uf" id="uf" class="form-control" readonly required>
          </div>

          <div class="col-md-3">
            <label for="data" class="form-label">Data</label>
            <input type="date" id="data" name="data" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Cotado por</label>
            <input type="text" name="cotado_por" class="form-control"
              value="<?= strtoupper($_SESSION['representante_nome'] ?? '') ?>" readonly required>

          </div>

          <div class="col-md-2">
            <label class="form-label">SUFRAMA</label>
            <select name="suframa" class="form-select" required>
              <option value="">Selecione</option>
              <option value="Sim">Sim</option>
              <option value="Não" selected>Não</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Suspensão de IPI</label>
            <select name="suspensao_ipi" class="form-select" required>
              <option value="">Selecione</option>
              <option value="Sim">Sim</option>
              <option value="Não" selected>Não</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Dólar PTAX BDB</label>
            <input type="text" name="dolar" class="form-control" inputmode="decimal"
              pattern="[0-9]+([,\.][0-9]+)?" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Incluir preço NET?</label>
            <select name="incluir_net" id="incluir_net" class="form-control" required title="Ao selecionar SIM, a coluna PREÇO NET será adicionada ao seu orçamento.">
              <option value="false">Não</option>
              <option value="true">Sim</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Observações</label>
            <textarea name="observacoes" class="form-control"></textarea>
          </div>
        </div>
      </div>

      <div class="form-section">
        <h5>Itens do Orçamento</h5>
        <div id="itens-container"></div>
        <button type="button" class="btn btn-outline-primary" onclick="adicionarItem()">+ Adicionar
          Item</button>

      </div>

      <div class="text-center mt-3">
        <button type="submit" class="btn btn-success" id="btnSalvar">
          Salvar Orçamento
        </button>
        <div id="loading" class="spinner-border text-primary" role="status" style="display:none;"></div>
        <div> </div>
      </div>
    </form>
  </div>

  <div class="modal fade" id="modalClientes" tabindex="-1" aria-labelledby="modalClientesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Selecionar Cliente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <input type="text" id="buscaCliente" class="form-control mb-3"
            placeholder="Buscar cliente ou UF...">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Cliente</th>
                <th>UF</th>
                <th>Ação</th>
              </tr>
            </thead>
            <tbody id="listaClientes">
              <tr>
                <td colspan="3">Digite para buscar...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>


  <!-- Modal de Seleção de Produto -->
  <div class="modal fade" id="modalProdutos" tabindex="-1" aria-labelledby="modalProdutosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalProdutosLabel">Selecionar Produto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <input type="text" id="buscaProduto" class="form-control"
            placeholder="Buscar por nome, código ou NCM...">
          <button class="btn btn-outline-primary" type="button" onclick="buscarProdutos()">Pesquisar</button>
          <table class="table table-bordered" id="tabelaProdutos">
            <thead>
              <tr>
                <th>Código</th>
                <th>Produto</th>
                <th>Unidade</th>
                <th>Origem</th>
                <th>NCM</th>
                <th>IPI %</th>
                <th>Ação</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $produtos = $pdo->query("SELECT * FROM cot_estoque ORDER BY produto ASC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
              foreach ($produtos as $p): ?>
                <tr>
                  <td><?= htmlspecialchars($p['codigo']) ?></td>
                  <td><?= htmlspecialchars($p['produto']) ?></td>
                  <td><?= htmlspecialchars($p['unidade']) ?></td>
                  <td><?= htmlspecialchars($p['origem']) ?></td>
                  <td><?= htmlspecialchars($p['ncm']) ?></td>
                  <td><?= htmlspecialchars($p['ipi']) ?></td>
                  <td>
                    <button type="button" class="btn btn-sm btn-primary"
                      onclick='selecionarProduto(<?= json_encode($p) ?>)'>Selecionar</button>
                  </td>
                </tr>
              <?php endforeach; ?>

            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function adicionarItem() {
      const container = document.getElementById('itens-container');
      const index = container.children.length;

      const html = `
    <div class="row g-2 align-items-end mb-3 border-bottom pb-2 item-row">
      <div class="col-md-2">
        <label>Código</label>
        <div class="input-group">
          <input type="text" name="itens[${index}][codigo]" class="form-control codigo-input" readonly required>
          <button class="btn btn-outline-secondary" type="button" onclick="abrirModalProduto(this)">🔍</button>
        </div>
      </div>
      <div class="col-md-3">
        <label>Produto</label>
        <input type="text" name="itens[${index}][produto]" class="form-control" readonly required>
      </div>
      <div class="col-md-2">
        <label>Unidade</label>
        <input type="text" name="itens[${index}][unidade]" class="form-control" readonly required>
      </div>
      <div class="col-md-2">
        <label>Origem</label>
        <input id="origem" type="text" name="itens[${index}][origem]" class="form-control" readonly required>
      </div>
      <div class="col-md-2">
        <label>NCM</label>
        <input type="text" name="itens[${index}][ncm]" class="form-control" readonly required>
      </div>

      <div class="col-md-2">
        <label>Volume</label>
        <input type="text" name="itens[${index}][volume]" class="form-control only-numbers" required>
      </div>
      <div class="col-md-2">
        <label>Embalagem</label>
        <input type="text" name="itens[${index}][embalagem]" class="form-control only-numbers" required>
      </div>
      <div class="col-md-2">
        <label>IPI</label>
        <input type="text" name="itens[${index}][ipi]" class="form-control" required>
      </div>
      <div class="col-md-2">
        <label>ICMS</label>
        <input type="text" name="itens[${index}][icms]" class="form-control" required>
      </div>
      <div class="col-md-3">
  <label for="disponibilidade">Disponibilidade</label>
  <select name="itens[${index}][disponibilidade]" class="form-select" required>
    <option value="">Selecione</option>
    <option value="IMEDIATA">IMEDIATA</option>
    <option value="LEAD-TIME - 7 DIAS">LEAD-TIME - 7 DIAS</option>
    <option value="LEAD-TIME - 15 DIAS">LEAD-TIME - 15 DIAS</option>
    <option value="LEAD-TIME - 20 DIAS">LEAD-TIME - 20 DIAS</option>
    <option value="LEAD-TIME - 25 DIAS">LEAD-TIME - 25 DIAS</option>
    <option value="LEAD-TIME - 30 DIAS">LEAD-TIME - 30 DIAS</option>
    <option value="LEAD-TIME - 35 DIAS">LEAD-TIME - 35 DIAS</option>
    <option value="LEAD-TIME - 40 DIAS">LEAD-TIME - 40 DIAS</option>
    <option value="LEAD-TIME - 45 DIAS">LEAD-TIME - 45 DIAS</option>
    <option value="LEAD-TIME - 50 DIAS">LEAD-TIME - 50 DIAS</option>
    <option value="LEAD-TIME - 55 DIAS">LEAD-TIME - 55 DIAS</option>
    <option value="LEAD-TIME - 60 DIAS">LEAD-TIME - 60 DIAS</option>
    <option value="LEAD-TIME - 65 DIAS">LEAD-TIME - 65 DIAS</option>
    <option value="LEAD-TIME - 70 DIAS">LEAD-TIME - 70 DIAS</option>
    <option value="LEAD-TIME - 75 DIAS">LEAD-TIME - 75 DIAS</option>
    <option value="LEAD-TIME - 80 DIAS">LEAD-TIME - 80 DIAS</option>
    <option value="LEAD-TIME - 85 DIAS">LEAD-TIME - 85 DIAS</option>
    <option value="LEAD-TIME - 90 DIAS">LEAD-TIME - 90 DIAS</option>
    <option value="LEAD-TIME - 120 DIAS">LEAD-TIME - 120 DIAS</option>
    <option value="PROC IMPORTAÇÃO">PROC IMPORTAÇÃO</option>
  </select>
</div>

      <div class="col-md-2">
        <label>Preço Net</label>
        <input type="text" name="itens[${index}][preco_net]" class="form-control" oninput="calcularPrecoFull(this.closest('.item-row'))" required>
      </div>
      <div class="col-md-2">
        <label>Preço Full</label>
        <input type="text" name="itens[${index}][preco_full]" class="form-control preco_full" required>
      </div>
<small class="aviso_preco_full" style="display: none; color: red; font-weight: bold;">
  ⚠️ Cuidado ao editar o preço sem autorização
</small>

      <div class="col-md3 text-end align-self-start">
        <span class="remove-item btn btn btn-outline-danger mt-4" onclick="this.closest('.item-row').remove()">Remover Item</span>
      </div>
    </div>
  `;


      // Adiciona o evento de cálculo automático
      container.insertAdjacentHTML('beforeend', html);

      const novaLinha = container.lastElementChild;

      // Adiciona o aviso ao focar no campo "preço full"
      const precoInput = novaLinha.querySelector('.preco_full');
      const aviso = novaLinha.querySelector('.aviso_preco_full');

      if (precoInput && aviso) {
        precoInput.addEventListener('focus', () => {
          aviso.style.display = 'inline';
        });

        precoInput.addEventListener('blur', () => {
          aviso.style.display = 'none';
        });
      }

      // Seus outros eventos continuam abaixo
      const netInput = novaLinha.querySelector('[name*="[preco_net]"]');
      const icmsInput = novaLinha.querySelector('[name*="[icms]"]');

      netInput.addEventListener('input', () => calcularPrecoFull(novaLinha));
      icmsInput.addEventListener('input', () => calcularPrecoFull(novaLinha));


    }

    let produtoBtnReferencia = null;

    function abrirModalProduto(button) {
      produtoBtnReferencia = button.closest('.item-row');
      const modal = new bootstrap.Modal(document.getElementById('modalProdutos'));
      modal.show();
    }





    function selecionarProduto(produto) {
      if (!produtoBtnReferencia) return;

      produtoBtnReferencia.querySelector('[name*="[codigo]"]').value = produto.codigo;
      produtoBtnReferencia.querySelector('[name*="[produto]"]').value = produto.produto;
      produtoBtnReferencia.querySelector('[name*="[unidade]"]').value = produto.unidade;
      produtoBtnReferencia.querySelector('[name*="[origem]"]').value = produto.origem;
      produtoBtnReferencia.querySelector('[name*="[ncm]"]').value = produto.ncm;

      // Verifica se Suspensão de IPI está marcada como Sim
      const suspensaoIPI = document.querySelector('[name="suspensao_ipi"]').value === 'Sim';
      produtoBtnReferencia.querySelector('[name*="[ipi]"]').value = suspensaoIPI ? '0,00' : String(produto.ipi)
        .replace('.', ',');


      // Buscar alíquota com base na UF
      const uf = document.querySelector('input[name="uf"]').value;

      fetch(`buscar_icms.php?uf=${uf}`)
        .then(res => res.json())
        .then(data => {
          const origem = parseInt(produto.origem);
          const icmsInput = produtoBtnReferencia.querySelector('[name*="[icms]"]');
          const ufUpper = uf.trim().toUpperCase();

          if (origem === 1 || origem === 2) {
            icmsInput.value = (ufUpper === 'SP') ? '18%' : '4%';
          } else {
            // continua usando o valor da tabela de ICMS
            icmsInput.value = data.aliquota ? `${data.aliquota}%` : '';
          }
        })


      bootstrap.Modal.getInstance(document.getElementById('modalProdutos')).hide();
    }




    function filtrarProdutos() {
      const filtro = document.getElementById('buscaProduto').value.toLowerCase();
      const linhas = document.querySelectorAll('#tabelaProdutos tbody tr');
      linhas.forEach(linha => {
        const texto = linha.innerText.toLowerCase();
        linha.style.display = texto.includes(filtro) ? '' : 'none';
      });
    }

    function buscarProdutos() {
      const termo = document.getElementById('buscaProduto').value;
      const tbody = document.querySelector('#tabelaProdutos tbody');
      tbody.innerHTML = '<tr><td colspan="7">Buscando...</td></tr>';

      fetch(`buscar_produtos.php?q=${encodeURIComponent(termo)}`)
        .then(res => res.json())
        .then(produtos => {
          if (!Array.isArray(produtos)) throw new Error("Resposta não é um array");

          if (produtos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7">Nenhum produto encontrado.</td></tr>';
            return;
          }

          tbody.innerHTML = '';
          produtos.forEach(produto => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
          <td>${produto.codigo}</td>
          <td>${produto.produto}</td>
          <td>${produto.unidade}</td>
          <td>${produto.origem}</td>
          <td>${produto.ncm}</td>
          <td>${produto.ipi}</td>
          <td><button type="button" class="btn btn-sm btn-primary" onclick='selecionarProduto(${JSON.stringify(produto)})'>Selecionar</button></td>
        `;
            tbody.appendChild(tr);
          });
        })
        .catch(err => {
          console.error('Erro:', err);
          tbody.innerHTML = '<tr><td colspan="7">Erro ao buscar produtos.</td></tr>';
        });
    }

    document.getElementById('buscaProduto').addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault(); // evita comportamento padrão (como fechar o modal)
        buscarProdutos();
      }
    });

    function buscarICMS(uf, callback) {
      fetch(`buscar_icms.php?uf=${uf}`)
        .then(response => response.json())
        .then(data => {
          if (data.aliquota) {
            callback(data.aliquota);
          } else {
            console.warn(data.erro || 'Erro ao obter ICMS');
            callback(null);
          }
        })
        .catch(() => {
          console.error('Erro na requisição de ICMS');
          callback(null);
        });
    }

    function calcularPrecoFull(row) {
      const precoNetInput = row.querySelector('[name*="[preco_net]"]');
      const icmsInput = row.querySelector('[name*="[icms]"]');
      const precoFullInput = row.querySelector('[name*="[preco_full]"]');
      const suframaSelect = document.querySelector('[name="suframa"]');

      const precoNet = parseFloat(precoNetInput.value.replace(',', '.'));
      const icms = parseFloat(icmsInput.value.replace(',', '.'));
      const isSuframa = suframaSelect.value === 'Sim';

      if (isNaN(precoNet) || (isNaN(icms) && !isSuframa)) {
        precoFullInput.value = '';
        return;
      }

      // Cofatores por alíquota
      const cofatores = {
        4: 0.8712,
        7: 0.8440,
        12: 0.7986,
        18: 0.7442
      };

      let precoFull;
      if (isSuframa) {
        precoFull = precoNet / 0.82;
      } else {
        const cofator = cofatores[icms] || 1;
        precoFull = precoNet / cofator;
      }

      precoFullInput.value = precoFull.toFixed(4).replace('.', ',');
    }


    document.addEventListener('DOMContentLoaded', function() {
      const campoData = document.getElementById('data');
      if (campoData) {
        const hoje = new Date();
        const ano = hoje.getFullYear();
        const mes = String(hoje.getMonth() + 1).padStart(2, '0');
        const dia = String(hoje.getDate()).padStart(2, '0');
        campoData.value = `${ano}-${mes}-${dia}`;
      }
    });


    document.addEventListener('DOMContentLoaded', function() {
      const cotadoInput = document.getElementById('cotado_por');
      if (cotadoInput) {
        // Toda vez que digitar, transforma em maiúscula
        cotadoInput.addEventListener('input', function() {
          this.value = this.value.toUpperCase();
        });

        // Segurança extra: ao enviar o formulário, converte também
        const form = cotadoInput.closest('form');
        if (form) {
          form.addEventListener('submit', function() {
            cotadoInput.value = cotadoInput.value.toUpperCase();
          });
        }
      }
    });

    function abrirModalCliente() {
      const modal = new bootstrap.Modal(document.getElementById('modalClientes'));
      document.getElementById('buscaCliente').value = '';
      document.getElementById('listaClientes').innerHTML = '<tr><td colspan="3">Digite para buscar...</td></tr>';
      modal.show();
    }

    document.getElementById('buscaCliente').addEventListener('keyup', function() {
      const termo = this.value.trim();
      if (termo.length < 3) return;

      fetch(`buscar_clientes.php?q=${encodeURIComponent(termo)}`)
        .then(res => res.json())
        .then(clientes => {
          const lista = document.getElementById('listaClientes');
          lista.innerHTML = '';

          if (clientes.length === 0) {
            lista.innerHTML = '<tr><td colspan="3">Nenhum cliente encontrado.</td></tr>';
            return;
          }

          clientes.forEach(cli => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
          <td>${cli.razao_social}</td>
          <td>${cli.uf}</td>
          <td><button type="button" class="btn btn-sm btn-primary" onclick='selecionarCliente(${JSON.stringify(cli)})'>Selecionar</button></td>
        `;
            lista.appendChild(tr);
          });
        });
    });

    function selecionarCliente(cliente) {
      document.getElementById('cliente').value = cliente.razao_social;
      document.getElementById('uf').value = cliente.uf;
      bootstrap.Modal.getInstance(document.getElementById('modalClientes')).hide();
    }

    document.querySelector('[name="suframa"]').addEventListener('change', () => {
      document.querySelectorAll('.item-row').forEach(row => calcularPrecoFull(row));
    });

    // Sempre que mudar a Suspensão de IPI, atualizar os itens
    document.querySelector('[name="suspensao_ipi"]').addEventListener('change', () => {
      const suspensaoIPI = document.querySelector('[name="suspensao_ipi"]').value === 'Sim';

      document.querySelectorAll('.item-row').forEach(row => {
        const ipiInput = row.querySelector('[name*="[ipi]"]');
        if (suspensaoIPI) {
          ipiInput.value = '0,00';
        }
      });
    });


    window.addEventListener('DOMContentLoaded', () => {
      fetch("ptax.php")
        .then(response => response.json())
        .then(data => {
          if (data && data.value && data.value.length > 0) {
            const ptaxVenda = parseFloat(data.value[0].cotacaoVenda).toFixed(4).replace('.', ',');
            document.querySelector('[name="dolar"]').value = ptaxVenda;
          } else {
            console.error("Nenhuma cotação PTAX encontrada.");
          }
        })
        .catch(error => {
          console.error("Erro ao buscar a PTAX via ptax.php:", error);
        });
    });

    document.querySelectorAll('input[name="dolar"], input[name*="[volume]"], input[name*="[embalagem]"]').forEach(
      input => {
        input.addEventListener('input', () => {
          input.value = input.value.replace(/[^0-9,\.]/g, '');
        });
      });
  </script>
  <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1): ?>
    <!-- Modal -->
    <div class="modal fade" id="modalSucesso" tabindex="-1" aria-labelledby="modalSucessoLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="modalSucessoLabel">Sucesso!</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <div class="modal-body">
            Orçamento salvo com sucesso.
            <div class="alert alert-warning mt-3" role="alert">
              Uma cópia do orçamento foi enviada por e-mail. Caso não localize-a, verifique sua caixa de
              <strong>lixo eletrônico</strong> ou <strong>spam</strong>.
            </div>
            <div class="alert alert-danger mt-3" role="alert">
              Clique em não é <strong>lixo eletrônico</strong> para acessar o PDF.
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            <a href="tmp/orcamento_<?= htmlspecialchars($_GET['num_orcamento'] ?? '') ?>.pdf"
              class="btn btn-primary"
              target="_blank">
              Visualizar PDF
            </a>

          </div>
        </div>
      </div>
    </div>

    <script>
      const sucessoModal = new bootstrap.Modal(document.getElementById('modalSucesso'));
      window.onload = () => sucessoModal.show();
    </script>
  <?php endif; ?>

  <script>
    function gerarPDF() {
      const cliente = document.getElementById('cliente').value;
      const uf = document.getElementById('uf').value;
      const data = document.getElementById('data').value;
      const cotado_por = document.getElementById('cotado_por').value;

      const url =
        `gerar_pdf.php?cliente=${encodeURIComponent(cliente)}&uf=${encodeURIComponent(uf)}&data=${data}&cotado_por=${encodeURIComponent(cotado_por)}`;
      window.open(url, '_blank');
    }
  </script>




  <script>
    document.getElementById("btnSalvar").addEventListener("click", function() {
      document.getElementById("loading").style.display = "block";
    });
  </script>
  
  <script>
document.addEventListener('DOMContentLoaded', function () {
  const params = new URLSearchParams(window.location.search);
  const numOrcamento = params.get("num_orcamento");
  const incluirNet = params.get("incluir_net");

  if (params.get("sucesso") === "1" && numOrcamento && incluirNet !== null) {
    fetch('gerar_pdf_orcamento.php?num=' + numOrcamento + '&incluir_net=' + incluirNet)
      .then(response => {
        if (!response.ok) throw new Error("Erro ao gerar PDF");
        return response.blob(); // retorna o conteúdo como blob
      })
      .then(blob => {
        const blobUrl = URL.createObjectURL(blob);
        window.open(blobUrl, '_blank'); // abre em nova aba o PDF real
      })
      .catch(error => {
        console.error(error);
        alert("Erro ao gerar o PDF.");
      });
  }
});

</script>





</body>

</html>
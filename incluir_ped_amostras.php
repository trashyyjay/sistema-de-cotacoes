<?php
session_start();
date_default_timezone_set('America/Sao_Paulo'); 

$pagina_ativa = 'incluir_amostra'; 

require_once 'header.php'; 
require_once 'conexao.php'; 

if (!isset($_SESSION['representante_email'])) {
    header('Location: index.html');
    exit();
}

// --- LÓGICA DE PERMISSÃO ADICIONADA ---
$isAdmin = (isset($_SESSION['admin']) && $_SESSION['admin'] == 1);
$campoAutorizadorDisabled = !$isAdmin ? 'disabled' : '';
// --- FIM DA LÓGICA ---

$responsavel_email = $_SESSION['representante_email'] ?? 'Não Logado';
$primeiro_nome = $_SESSION['representante_nome'] ?? '';
$sobrenome = $_SESSION['representante_sobrenome'] ?? '';

$responsavel_nome_completo = trim($primeiro_nome . ' ' . $sobrenome);

if (empty($responsavel_nome_completo)) {
     $responsavel_nome_final = $responsavel_email;
} else {
    $responsavel_nome_final = ucwords(strtolower($responsavel_nome_completo));
}

$data_atual = date('d/m/Y');
$numero_pedido = date('YmdHis');

// Exibe mensagens flash da sessão
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . htmlspecialchars($_SESSION['message_type']) . ' alert-dismissible fade show" role="alert">';
    echo $_SESSION['message'];
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incluir Pedido de Amostra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-section { margin-bottom: 2rem; padding: 1.5rem; border: 1px solid #dee2e6; border-radius: 0.375rem; background-color: #f8f9fa; }
        label { font-weight: bold; }
        #displayClienteNome { background-color: #e9ecef; cursor: pointer; }
    </style>
</head>
<body>

<div class="container mt-4">
    <h2>Novo Pedido de Amostra (Nº: <?php echo htmlspecialchars($numero_pedido); ?>)</h2>
    <hr>

    <form id="formPedidoAmostra" action="processa_pedido_amostra.php" method="POST">
        <input type="hidden" name="numero_pedido" value="<?php echo htmlspecialchars($numero_pedido); ?>">

        <div class="row mb-3 form-section">
            <div class="col-md-6">
                <label for="responsavel" class="form-label">Responsável:</label>
                <input type="text" id="responsavel" name="responsavel_display" class="form-control" value="<?php echo htmlspecialchars($responsavel_nome_final); ?>" readonly>
            </div>
            <div class="col-md-6">
                <label for="data_pedido" class="form-label">Data:</label>
                <input type="text" id="data_pedido" name="data_pedido" class="form-control" value="<?php echo $data_atual; ?>" readonly>
            </div>
        </div>

        <div class="row mb-3 form-section">
             <legend>Dados do Cliente</legend>
             <input type="hidden" id="idCliente" name="id_cliente">
             <div class="col-md-12 mb-3">
                <label for="displayClienteNome" class="form-label">Cliente:</label>
                <div class="input-group">
                    <input type="text" id="displayClienteNome" class="form-control" placeholder="Clique em Buscar para selecionar..." readonly>
                    <button class="btn btn-outline-secondary" type="button" id="btnBuscarCliente" onclick="abrirModalCliente()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg> Buscar
                    </button>
                </div>
             </div>
             <div class="col-md-4 mb-3">
                <label for="cliente_contato" class="form-label">Contato:</label>
                <input type="text" id="cliente_contato" name="cliente_contato" class="form-control" readonly>
            </div>
            <div class="col-md-4 mb-3">
                <label for="cliente_email" class="form-label">E-mail:</label>
                <input type="email" id="cliente_email" name="cliente_email" class="form-control" readonly>
            </div>
            <div class="col-md-4 mb-3">
                <label for="cliente_telefone" class="form-label">Telefone:</label>
                <input type="text" id="cliente_telefone" name="cliente_telefone" class="form-control" readonly>
            </div>
             <small class="form-text text-muted">Os dados de Contato, E-mail e Telefone serão preenchidos automaticamente. Você poderá editá-los se necessário.</small>
        </div>

        <div class="row mb-3 form-section" id="secaoProdutos">
             <legend>Produtos da Amostra</legend>
             <div class="col-12 mb-3">
                 <button type="button" class="btn btn-success" onclick="abrirModalProdutos()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/></svg> Adicionar Produto
                 </button>
             </div>
             <div class="col-12">
                 <table class="table table-bordered table-hover" id="tabelaItensPedido">
                     <thead class="table-light">
                         <tr>
                             <th>Produto</th>
                             <th>Qtd.</th>
                             <th>Unid.</th>
                             <th>Fabricante</th>
                             <th>Estoque?</th>
                             <th>Fracionar?</th>
                             <th>Ação</th>
                         </tr>
                     </thead>
                     <tbody>
                         <tr id="nenhumProdutoRow"><td colspan="7" class="text-center text-muted">Nenhum produto adicionado ainda.</td></tr>
                     </tbody>
                 </table>
             </div>
        </div>

        <div class="row mb-3 form-section">
            <legend>Informações Adicionais</legend>
             <div class="col-md-12 mb-3">
                <label for="info_projeto" class="form-label">Informações sobre o Projeto:</label>
                <textarea id="info_projeto" name="info_projeto" class="form-control" rows="3"></textarea>
            </div>
             <div class="col-md-6 mb-3">
                <label for="etapa_projeto" class="form-label">Etapa do Projeto:</label>
                <input type="text" id="etapa_projeto" name="etapa_projeto" class="form-control">
            </div>
             <div class="col-md-6 mb-3">
                <label for="data_limite" class="form-label">Data Limite para Atendimento:</label>
                <input type="date" id="data_limite" name="data_limite" class="form-control">
            </div>
            <div class="col-md-12 mb-3">
                <label for="autorizado_por" class="form-label">Autorizado Por:</label>
                <select id="autorizado_por" name="autorizado_por" class="form-select" required <?= $campoAutorizadorDisabled ?>>
                    <option value="" selected disabled>Selecione o aprovador...</option>
                    <option value="carina.apassite@innovasell.com.br">Carina Apassite <carina.apassite@innovasell.com.br></option>
                    <option value="selmo.araujo@innovasell.com.br">Selmo Araujo <selmo.araujo@innovasell.com.br></option>
                    <option value="renaldo.rocha@innovasell.com.br">Renaldo Rocha <renaldo.rocha@innovasell.com.br></option>
                    <option value="ti@innovasell.com.br">Hector Hansen <ti@innovasell.com.br></option>
                </select>
                <?php if (!$isAdmin): ?>
                    <small class="form-text text-muted">Apenas administradores podem selecionar o aprovador.</small>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
             <button type="submit" class="btn btn-primary">
                 <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send" viewBox="0 0 16 16"><path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/></svg> Enviar Pedido de Amostra
             </button>
        </div>
    </form>
</div>

<div class="modal fade" id="modalClientes" tabindex="-1" aria-labelledby="modalClientesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="modalClientesLabel">Selecionar Cliente</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <div class="input-group mb-3"><input type="text" id="buscaCliente" class="form-control" placeholder="Digite parte do Nome para buscar..."><button class="btn btn-outline-secondary" type="button" onclick="buscarCliente()">Buscar</button></div>
        <table class="table table-hover">
          <thead><tr><th>Razão Social / Nome</th><th>UF</th><th>Ação</th></tr></thead>
          <tbody id="listaClientes"><tr><td colspan="3">Digite para buscar...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="modalProdutos" tabindex="-1" aria-labelledby="modalProdutosLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="modalProdutosLabel">Selecionar Produto</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
         <div class="input-group mb-3"><input type="text" id="buscaProduto" class="form-control" placeholder="Buscar por nome, código..."><button class="btn btn-outline-secondary" type="button" onclick="buscarProdutos()">Pesquisar</button></div>
         <table class="table table-bordered table-hover" id="tabelaProdutosModal">
             <thead><tr><th>Código</th><th>Produto</th><th>Unidade</th><th>Ação</th></tr></thead>
             <tbody id="listaProdutos"><tr><td colspan="4">Digite para buscar...</td></tr></tbody>
         </table>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="modalDetalhesProduto" tabindex="-1" aria-labelledby="modalDetalhesProdutoLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="modalDetalhesProdutoLabel">Adicionar Produto ao Pedido</h5></div>
      <div class="modal-body">
        <input type="hidden" id="produtoSelecionadoJson">
        <div class="mb-3"><label class="form-label">Produto:</label><p class="form-control-plaintext" id="modalProdutoNome"><strong>-- Nome do Produto --</strong></p></div>
        <div class="mb-3"><label for="modalProdutoQuantidade" class="form-label">Quantidade:</label><input type="number" class="form-control" id="modalProdutoQuantidade" step="any" min="0.001" required><small class="form-text text-muted">Ex: 1.5</small></div>
        <div class="mb-3"><label class="form-label">Unidade:</label><p class="form-control-plaintext" id="modalProdutoUnidade">-- Unidade --</p></div>
        <div class="mb-3"><label class="form-label">Fabricante (Automático):</label><p class="form-control-plaintext" id="modalProdutoFabricante"><strong>-- Fabricante --</strong></p></div>
        <div class="mb-3"><label for="modalProdutoEstoque" class="form-label">DISPONIVEL EM ESTOQUE:</label><select class="form-select" id="modalProdutoEstoque" required><option value="" selected disabled>Selecione...</option><option value="SIM">SIM</option><option value="NÃO">NÃO</option></select></div>
        <div class="mb-3"><label for="modalProdutoFracionamento" class="form-label">NECESSITA FRACIONAMENTO:</label><select class="form-select" id="modalProdutoFracionamento" required><option value="" selected disabled>Selecione...</option><option value="SIM">SIM</option><option value="NÃO">NÃO</option></select></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" class="btn btn-primary" onclick="confirmarAdicaoProduto()">Confirmar Adição</button></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const mapaFabricantes={'001':'CODIF','002':'BIOLAND','003':'CITRÓLEO','004':'HOCK CHEMIE','005':'ECO-SHELL','006':'NATURAL AMAZON','007':'MPR','008':'KPT','009':'COPRA','010':'ARGILE DU VELAY','011':'CHEMLAND','012':'ETHOX','013':'GALACTIC','014':'HERRMAN','015':'RADIFIX','016':'WUHU','017':'SIDDARTH CARBOCHEM','018':'BH BIOTECH','019':'TC-USA','020':'TNJ','021':'ECHEMI','022':'SOHO ANECO','023':'COMPOMAT','024':'ANHUI HERRMAN','025':'SENTINALCO','026':'MICRO POWDER´S','027':'THE INNOVATION COMPANY','028':'JEDWARDS','029':'DISTRIOL','030':'SPEC CHEM','031':'KUMAR','032':'NATURAL HERB','033':'WATEC','034':'WENDA','035':'GRANASUR','036':'IPEL','037':'NANOGEM','038':'JOYVO','039':'MB SUGARS','040':'GREAF','041':'AIGLON','042':'FUMEI','043':'INTERFAT','044':'RESPHARMA','045':'CHOISUN BIOTECH','046':'GREEN BIO','047':'KONCEPTNUTRA','048':'RAWMAP','049':'AF SUTER','050':'VAN WANKUM INGREDIENTS','051':'DERMALAB','052':'SEKISUI','053':'SINOLION','054':'HAITIAN','055':'BAOFENG','056':'CARBONADO','057':'GRAN OILS','058':'CHARKIT CHEMICAL','059':'NATURAL OILS','060':'AROMA HOLLY','061':'FOCUS CHEM','062':'JIANGSU MIAOYI','063':'INTERCARE','064':'GREEN ANDINA','065':'ABC NANOTECH','066':'SALICYLATES','067':'CHEMAXCEL','068':'BISOR','069':'INFINITEC','070':'AROMAAZ','071':'ZHONGLAN','072':'HAIHANG','073':'A AZEVEDO','074':'BIOSYNTHETIC','075':'ONETECH SOLUTION','076':'PUERZAN','077':'SHANGHAI OLI ENTERPRISES','078':'COSROMA','079':'NUTRANOVO','080':'HERBALTEC','081':'Reachin Chemical','082':'Xi\'an DN Biology Co.,Ltd','083':'GUARAMEX / BRASCARBO','084':'KRAEBER & Co GmbH','085':'XI´AN GREEN SPRING TECHNOLOGY','086':'CHANGSHA STAHERB NATURAL','087':'NANOVEX','088':'LIPOMIZE','089':'KANGCARE','090':'WINKEY','091':'ENSINCE','092':'CARBONWAVE','093':'ACTERA','094':'TAUROS (VIPOTECH)','095':'TAEKYUNG','096':'GIGA FINE','097':'BERI PHARMA','098':'HNB BIO','099':'NORMACTIVE','100':'SF SCIENCE','101':'VIABLIFE','102':'COACHCHEM','103':'CITREFINE','104':'UNI POWDER','105':'ZHEJIANG NHU COMPANY LTD'};
    function getFabricantePorCodigo(codigoProduto){if(!codigoProduto||codigoProduto.length<3){return'N/A (Código Inválido)'}const prefixo=codigoProduto.substring(0,3);return mapaFabricantes[prefixo]||'Fabricante Desconhecido'}
    function abrirModalCliente(){document.getElementById('buscaCliente').value='';document.getElementById('listaClientes').innerHTML='<tr><td colspan="4">Digite para buscar...</td></tr>';var modalClientes=new bootstrap.Modal(document.getElementById('modalClientes'));modalClientes.show()}
    function buscarCliente(){const termo=document.getElementById('buscaCliente').value.trim();const lista=document.getElementById('listaClientes');lista.innerHTML='<tr><td colspan="3">Buscando...</td></tr>';if(termo.length<3){lista.innerHTML='<tr><td colspan="3">Digite ao menos 3 caracteres...</td></tr>';return}
    fetch(`buscar_clientes_amostra.php?q=${encodeURIComponent(termo)}`).then(response=>{if(!response.ok){throw new Error(`HTTP error! status: ${response.status}`)}
    return response.json()}).then(clientes=>{lista.innerHTML='';if(!Array.isArray(clientes)){console.error("Resposta do servidor (clientes) não é um array:",clientes);lista.innerHTML='<tr><td colspan="3" class="text-danger">Erro: Resposta inválida do servidor.</td></tr>';return}
    if(clientes.length===0){lista.innerHTML='<tr><td colspan="3">Nenhum cliente encontrado.</td></tr>';return}
    clientes.forEach(cli=>{const tr=document.createElement('tr');tr.innerHTML=`<td>${cli.razao_social||'N/A'}</td><td>${cli.uf||'N/A'}</td><td><button type="button" class="btn btn-sm btn-primary" onclick='selecionarCliente(${JSON.stringify(cli)})'>Selecionar</button></td>`;lista.appendChild(tr)})}).catch(error=>{console.error('Erro ao buscar clientes:',error);lista.innerHTML=`<tr><td colspan="3" class="text-danger">Erro ao buscar clientes: ${error.message}</td></tr>`})}
    function selecionarCliente(cliente){console.log("Cliente selecionado (completo):",cliente);document.getElementById('idCliente').value=cliente.id||'';document.getElementById('displayClienteNome').value=`${cliente.razao_social||''} (${cliente.cnpj||'Sem CNPJ'})`;const contatoInput=document.getElementById('cliente_contato');const emailInput=document.getElementById('cliente_email');const telefoneInput=document.getElementById('cliente_telefone');contatoInput.value=cliente.contato||'';emailInput.value=cliente.email||'';telefoneInput.value=cliente.telefone||'';var modalClientes=bootstrap.Modal.getInstance(document.getElementById('modalClientes'));modalClientes.hide()}
    document.getElementById('buscaCliente').addEventListener('keyup',function(event){if(event.key!=='Enter'){}});document.getElementById('buscaCliente').addEventListener('keypress',function(event){if(event.key==='Enter'){event.preventDefault();buscarCliente()}});function abrirModalProdutos(){document.getElementById('buscaProduto').value='';document.getElementById('listaProdutos').innerHTML='<tr><td colspan="4">Digite para buscar...</td></tr>';var modalProdutos=new bootstrap.Modal(document.getElementById('modalProdutos'));modalProdutos.show()}
    function buscarProdutos(){const termo=document.getElementById('buscaProduto').value.trim();const tbody=document.getElementById('listaProdutos');tbody.innerHTML='<tr><td colspan="4">Buscando...</td></tr>';if(termo.length<2){tbody.innerHTML='<tr><td colspan="4">Digite ao menos 2 caracteres...</td></tr>';return}
    fetch(`buscar_produtos.php?q=${encodeURIComponent(termo)}`).then(response=>{if(!response.ok){throw new Error(`HTTP error! status: ${response.status}`)}
    return response.json()}).then(produtos=>{tbody.innerHTML='';if(!Array.isArray(produtos)){throw new Error("Resposta do servidor não é um array")}
    if(produtos.length===0){tbody.innerHTML='<tr><td colspan="4">Nenhum produto encontrado.</td></tr>';return}
    produtos.forEach(produto=>{const tr=document.createElement('tr');tr.innerHTML=`<td>${produto.codigo||produto.id}</td><td>${produto.produto||'Sem Nome'}</td><td>${produto.unidade||'N/A'}</td><td><button type="button" class="btn btn-sm btn-primary" onclick='selecionarProduto(${JSON.stringify(produto)})'>Selecionar</button></td>`;tbody.appendChild(tr)})}).catch(error=>{console.error('Erro ao buscar produtos:',error);tbody.innerHTML=`<tr><td colspan="4" class="text-danger">Erro ao buscar: ${error.message}</td></tr>`})}
    function selecionarProduto(produto){console.log("Produto selecionado para detalhes:",produto);document.getElementById('produtoSelecionadoJson').value=JSON.stringify(produto);document.getElementById('modalProdutoNome').textContent=produto.produto||'Sem Nome';document.getElementById('modalProdutoUnidade').textContent=produto.unidade||'N/A';const fabricanteNome=getFabricantePorCodigo(produto.codigo);document.getElementById('modalProdutoFabricante').textContent=fabricanteNome;document.getElementById('modalProdutoQuantidade').value='';document.getElementById('modalProdutoEstoque').value='';document.getElementById('modalProdutoFracionamento').value='';var modalDetalhes=new bootstrap.Modal(document.getElementById('modalDetalhesProduto'));modalDetalhes.show();var modalBuscaProdutos=bootstrap.Modal.getInstance(document.getElementById('modalProdutos'));if(modalBuscaProdutos){modalBuscaProdutos.hide()}}
    function confirmarAdicaoProduto(){const produtoJson=document.getElementById('produtoSelecionadoJson').value;if(!produtoJson){alert("Erro: Não foi possível recuperar os dados do produto selecionado.");return}
    const produtoOriginal=JSON.parse(produtoJson);const quantidade=document.getElementById('modalProdutoQuantidade').value;const estoque=document.getElementById('modalProdutoEstoque').value;const fracionamento=document.getElementById('modalProdutoFracionamento').value;const fabricante=document.getElementById('modalProdutoFabricante').textContent;if(quantidade===""||isNaN(parseFloat(quantidade))||parseFloat(quantidade)<=0){alert("Por favor, informe uma quantidade válida.");document.getElementById('modalProdutoQuantidade').focus();return}
    if(!estoque){alert("Por favor, selecione se o produto está disponível em estoque.");document.getElementById('modalProdutoEstoque').focus();return}
    if(!fracionamento){alert("Por favor, selecione se o produto necessita fracionamento.");document.getElementById('modalProdutoFracionamento').focus();return}
    const itemParaAdicionar={id:produtoOriginal.id,nome:produtoOriginal.produto,unidade:produtoOriginal.unidade,quantidade:parseFloat(quantidade),fabricante:fabricante,estoque:estoque,fracionamento:fracionamento};adicionarProdutoAoPedido(itemParaAdicionar);var modalDetalhes=bootstrap.Modal.getInstance(document.getElementById('modalDetalhesProduto'));if(modalDetalhes){modalDetalhes.hide()}}
    function adicionarProdutoAoPedido(item){const tbody=document.getElementById('tabelaItensPedido').getElementsByTagName('tbody')[0];const nenhumProdutoRow=document.getElementById('nenhumProdutoRow');if(nenhumProdutoRow){nenhumProdutoRow.remove()}
    const newRow=tbody.insertRow();newRow.innerHTML=`<td>${item.nome}<input type="hidden" name="produto_id[]" value="${item.id}"><input type="hidden" name="produto_nome[]" value="${item.nome}"></td><td>${item.quantidade}<input type="hidden" name="quantidade[]" value="${item.quantidade}"></td><td>${item.unidade||'N/A'}<input type="hidden" name="unidade[]" value="${item.unidade||''}"></td><td>${item.fabricante||'N/A'}<input type="hidden" name="fabricante[]" value="${item.fabricante||''}"></td><td><span class="badge ${item.estoque==='SIM'?'bg-success':'bg-danger'}">${item.estoque}</span><input type="hidden" name="estoque[]" value="${item.estoque}"></td><td><span class="badge ${item.fracionamento==='SIM'?'bg-warning text-dark':'bg-secondary'}">${item.fracionamento}</span><input type="hidden" name="fracionamento[]" value="${item.fracionamento}"></td><td><button type="button" class="btn btn-sm btn-danger" onclick="removerLinhaProduto(this)"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg></button></td>`}
    function removerLinhaProduto(button){const row=button.closest('tr');const tbody=row.parentNode;row.remove();if(tbody.rows.length===0){tbody.innerHTML='<tr id="nenhumProdutoRow"><td colspan="7" class="text-center text-muted">Nenhum produto adicionado ainda.</td></tr>'}}
    document.getElementById('buscaProduto').addEventListener('keypress',function(event){if(event.key==='Enter'){event.preventDefault();buscarProdutos()}});
</script>

</body>
</html>
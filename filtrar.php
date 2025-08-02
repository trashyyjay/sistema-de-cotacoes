<?php
session_start();
date_default_timezone_set('America/Sao_Paulo'); // Define fuso horário
// !!! DEFINA A PÁGINA ATIVA AQUI !!!
$pagina_ativa = 'filtrar'; // Exemplo para consultar_amostras.php
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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesquisar Cotações</title>
  <div class="alert alert-danger text-center fw-bold" role="alert">
  ⚠ PROIBIDO O COMPARTILHAMENTO DESTA PESQUISA COM PESSOAS EXTERNAS
</div>

  <link 
    rel="stylesheet" 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  >
</head>
<body >
      <!-- ÁREA PRINCIPAL -->
      <main class="container py-4">

        <div class="d-flex justify-content-between align-items-center py-4">
          <h2 class="mb-0">Pesquisar Cotações</h2>

        </div>

        <div class="alert alert-warning mt-2" style="font-size: 0.95rem;">
          <strong>Atenção:</strong> os campos <strong>SUFRAMA</strong> e <strong>SUSPENSÃO DE IPI</strong> estão disponíveis somente a partir de <strong>fevereiro de 2025</strong>.
        </div>

        <!-- SEÇÃO DE FILTROS -->
        <div class="mb-3 p-3 border rounded">
          <h5>Filtros de Pesquisa</h5>
          <form method="GET" action="pesquisar.php">
            <input type="hidden" id="usuarioLogado" value="<?= $_SESSION['representante_nome'] ?? '' ?>">
            <div class="row g-3">

<!-- CAMPOS PRINCIPAIS -->
<div class="row g-3">
<div class="row g-3 align-items-end">
<div class="row g-3 align-items-end">
  <div class="col-md-4">
    <label class="form-label">Cliente</label>
    <input type="text" name="razao_social" class="form-control">
  </div>

  <div class="col-md-2">
    <label class="form-label">UF</label>
    <input type="text" name="uf" class="form-control text-uppercase" maxlength="2">
  </div>

  <div class="col-md-3">
    <label class="form-label">Data Inicial</label>
    <input type="date" name="data_inicial" class="form-control">
  </div>

  <div class="col-md-3">
    <label class="form-label">Data Final</label>
    <input type="date" name="data_final" class="form-control">
  </div>

  <!-- Linha separada para Código do Produto -->
  <div class="col-md-2">
    <label class="form-label">Código do Produto</label>
    <input type="text" name="codigo" class="form-control">
  </div>

  <div class="col-md-4">
    <label class="form-label">Produto</label>
    <input type="text" name="produto" class="form-control">
  </div>
</div>

<!-- BOTÃO MOSTRAR MAIS -->
<div class="mt-3">
  <a class="btn btn-outline-secondary" data-bs-toggle="collapse" href="#filtrosAvancados" role="button" aria-expanded="false" aria-controls="filtrosAvancados">
    Mostrar mais filtros
  </a>
</div>

<!-- CAMPOS AVANÇADOS (COLLAPSE) -->
<div class="collapse mt-3" id="filtrosAvancados">
  <div class="row g-3">

    <div class="col-md-4">
      <label class="form-label">Origem</label>
      <input type="text" name="origem" class="form-control">
    </div>

    <div class="col-md-4">
      <label class="form-label">Embalagem</label>
      <input type="text" name="embalagem" class="form-control">
    </div>

    <div class="col-md-4">
      <label class="form-label">NCM</label>
      <input type="text" name="ncm" class="form-control">
    </div>

    <div class="col-md-4">
      <label class="form-label">Volume</label>
      <input type="text" name="volume" class="form-control">
    </div>

    <div class="col-md-4">
      <label class="form-label">IPI %</label>
      <input type="text" name="ipi" class="form-control">
    </div>

    <div class="col-md-4">
      <label class="form-label">Preço Net USD/Kg</label>
      <input type="text" name="preco_net" class="form-control">
    </div>

    <div class="col-md-4">
      <label class="form-label">ICMS</label>
      <input type="text" name="icms" class="form-control">
    </div>

    <div class="col-md-4">
      <label class="form-label">Preço Full USD/Kg</label>
      <input type="text" name="preco_full" class="form-control">
    </div>

    <div class="col-md-4">
      <label class="form-label">Disponibilidade</label>
      <input type="text" name="disponibilidade" class="form-control">
    </div>

    <div class="col-md-4">
      <label class="form-label">Kg Disponível</label>
      <input type="text" name="kg_disponivel" class="form-control">
    </div>

    <div class="col-md-4">
      <label class="form-label">Cotado Por</label>
      <input type="text" id="cotadoPor" name="cotado_por" class="form-control" readonly>
    </div>

    <div class="col-md-4">
      <label class="form-label">Dólar Cotado</label>
      <input type="text" name="dolar" class="form-control">
    </div>

    <div class="col-md-4">
      <label class="form-label">GM%</label>
      <input type="text" name="gm" class="form-control">
    </div>

    <div class="col-md-4">
      <label class="form-label">SUFRAMA</label>
      <input type="text" name="suframa" class="form-control">
    </div>

    <div class="col-md-4">
      <label class="form-label">Suspensão de IPI</label>
      <input type="text" name="suspensao_ipi" class="form-control">
    </div>

    <div class="col-md-8">
      <label class="form-label">Observações</label>
      <input type="text" name="observacoes" class="form-control">
    </div>

  </div>
</div>


              <div class="col-12 text-end mt-3">
                <button type="submit" class="btn btn-primary">Buscar</button>
              </div>
            </div>
          </form>
        </div>

      </main>
    </div>
  

  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>

</body>
</html>
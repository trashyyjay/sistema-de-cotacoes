<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

// Define a página ativa para o header
$pagina_ativa = 'pesquisar_amostras'; 

require_once 'header.php'; 
require_once 'conexao.php'; 

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
  <title>Pesquisar Itens de Amostras</title>
  <link 
    rel="stylesheet" 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  >
</head>
<body>
      <main class="container py-4">
        <h2 class="mb-4">Pesquisar Itens em Solicitações de Amostra</h2>
        <p class="text-muted">Use os filtros para encontrar itens específicos dentro de todas as solicitações de amostra.</p>

        <div class="mb-3 p-3 border rounded bg-light">
          <h5 class="mb-3">Filtros de Pesquisa</h5>
          <form method="GET" action="pesquisar_amostras.php">
            <div class="row g-3">
              
              <div class="col-md-6">
                <label for="produto" class="form-label">Produto (Nome ou Código)</label>
                <input type="text" id="produto" name="produto" class="form-control" placeholder="Ex: ACIDO HIALURONICO ou 001.123">
              </div>

              <div class="col-md-6">
                <label for="cliente_razao_social" class="form-label">Cliente (Razão Social)</label>
                <input type="text" id="cliente_razao_social" name="cliente_razao_social" class="form-control">
              </div>

              <div class="col-md-6">
                <label for="responsavel_pedido" class="form-label">Representante (E-mail)</label>
                <input type="text" id="responsavel_pedido" name="responsavel_pedido" class="form-control">
              </div>
              
              <div class="col-md-6">
                <label for="status" class="form-label">Status da Solicitação</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="Pendente">Pendente</option>
                    <option value="Enviado">Enviado</option>
                    <option value="Cancelado">Cancelado</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="data_inicial" class="form-label">Data Inicial da Solicitação</label>
                <input type="date" id="data_inicial" name="data_inicial" class="form-control">
              </div>

              <div class="col-md-6">
                <label for="data_final" class="form-label">Data Final da Solicitação</label>
                <input type="date" id="data_final" name="data_final" class="form-control">
              </div>

              <div class="col-12 text-end mt-4">
                <a href="filtrar_amostras.php" class="btn btn-secondary">Limpar Filtros</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Pesquisar Itens
                </button>
              </div>
            </div>
          </form>
        </div>
      </main>
  
  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>
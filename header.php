<?php
// header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['representante_email'])) {
    if (basename($_SERVER['PHP_SELF']) != 'index.html') {
       header('Location: index.html');
       exit();
    }
}

$nomeUsuarioLogado = '';
if (isset($_SESSION['representante_nome'])) {
    $nomeUsuarioLogado = trim(($_SESSION['representante_nome'] ?? '') . ' ' . ($_SESSION['representante_sobrenome'] ?? ''));
    if(empty($nomeUsuarioLogado) && isset($_SESSION['representante_email'])) {
        $nomeUsuarioLogado = $_SESSION['representante_email'];
    }
}

// Garante que a variável exista para evitar erros
if (!isset($pagina_ativa)) {
    $pagina_ativa = '';
}

// Lógica para destacar o menu ativo
function is_active($item_key, $active_key, $grupo = false) {
    if ($grupo) {
        $grupos = [
            'orcamentos' => ['incluir_orcamento', 'filtrar', 'consultar_orcamentos', 'previsao'],
            'amostras'   => ['incluir_amostra', 'pesquisar_amostras'],
            'estoque'    => ['incluir_produto'], // Novo grupo para o menu de estoque
            'clientes'   => ['gerenciar_cliente']
        ];
        return isset($grupos[$item_key]) && in_array($active_key, $grupos[$item_key]);
    } else {
        return $item_key === $active_key;
    }
}

?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background-color: #40883c;">
  <div class="container-fluid">
    <a class="navbar-brand" href="filtrar.php">Menu principal > </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item dropdown <?php echo is_active('orcamentos', $pagina_ativa, true) ? 'active' : ''; ?>">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownOrcamentos" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            ORÇAMENTOS
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdownOrcamentos">
            <li><a class="dropdown-item <?php echo is_active('incluir_orcamento', $pagina_ativa) ? 'active' : ''; ?>" href="incluir_orcamento.php">Incluir Orçamento</a></li>
            <li><a class="dropdown-item <?php echo is_active('filtrar', $pagina_ativa) ? 'active' : ''; ?>" href="filtrar.php">Pesquisar Detalhado</a></li>
            <li><a class="dropdown-item <?php echo is_active('consultar_orcamentos', $pagina_ativa) ? 'active' : ''; ?>" href="consultar_orcamentos.php">Consultar Orçamentos</a></li>
            <li><a class="dropdown-item <?php echo is_active('previsao', $pagina_ativa) ? 'active' : ''; ?>" href="previsao.php">Previsão de Datas</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown <?php echo is_active('amostras', $pagina_ativa, true) ? 'active' : ''; ?>">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAmostras" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            SOLIC. AMOSTRAS
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdownAmostras">
            <li><a class="dropdown-item <?php echo is_active('incluir_amostra', $pagina_ativa) ? 'active' : ''; ?>" href="incluir_ped_amostras.php">Incluir Pedido</a></li>
            <li><a class="dropdown-item <?php echo is_active('pesquisar_amostras', $pagina_ativa) ? 'active' : ''; ?>" href="filtrar_amostras.php">Pesquisar Amostras</a></li>
          </ul>
        </li>
        
        <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
        <li class="nav-item dropdown <?php echo is_active('estoque', $pagina_ativa, true) ? 'active' : ''; ?>">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownEstoque" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            ESTOQUE
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdownEstoque">
            <li><a class="dropdown-item <?php echo is_active('incluir_produto', $pagina_ativa) ? 'active' : ''; ?>" href="incluir_produto.php">Incluir Produto</a></li>
          </ul>
        </li>
        <?php endif; ?>

        <li class="nav-item <?php echo is_active('clientes', $pagina_ativa, true) ? 'active' : ''; ?>">
          <a class="nav-link" href="gerenciar_cliente.php">GERENCIAR CLIENTES</a>
        </li>

      </ul>

      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <?php if (!empty($nomeUsuarioLogado)): ?>
             <li class="nav-item">
                <span class="navbar-text me-3">
                    Olá, <?php echo htmlspecialchars(ucwords(strtolower($nomeUsuarioLogado))); ?>
                </span>
             </li>
          <?php endif; ?>
          <li class="nav-item">
            <a class="nav-link" href="javascript:history.back()">VOLTAR</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="logout.php">SAIR</a>
          </li>
      </ul>
    </div>
  </div>
</nav>
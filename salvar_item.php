<?php
require_once 'conexao.php';

if (!isset($_POST['id_linha'], $_POST['num_orcamento'])) {
  die("Dados incompletos.");
}

$id = $_POST['id_linha'];
$num_orcamento = $_POST['num_orcamento'];

// Campos a serem atualizados
$produto = $_POST['produto'];
$origem = $_POST['origem'];
$volume = $_POST['volume'];
$preco_net = $_POST['preco_net'];
$icms = $_POST['icms'];

try {
  $stmt = $pdo->prepare("
    UPDATE cot_cotacoes_importadas
    SET 
      `PRODUTO` = :produto,
      `ORIGEM` = :origem,
      `VOLUME` = :volume,
      `PREÇO NET USD/KG` = :preco_net,
      `ICMS` = :icms
    WHERE id = :id
  ");

  $stmt->execute([
    ':produto' => $produto,
    ':origem' => $origem,
    ':volume' => $volume,
    ':preco_net' => $preco_net,
    ':icms' => $icms,
    ':id' => $id
  ]);

  // Redireciona de volta para o orçamento
  header("Location: atualizar_orcamento.php?num=" . urlencode($num_orcamento));
  exit;
} catch (PDOException $e) {
  die("Erro ao atualizar: " . $e->getMessage());
}

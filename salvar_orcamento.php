<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

require_once 'conexao.php';

if (!isset($_SESSION['representante_email'])) {
    header('Location: index.html');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acesso inválido.");
}

// Coleta de Dados
$data = $_POST['data'] ?? date('Y-m-d');
$cliente = strtoupper($_POST['cliente'] ?? '');
$uf = $_POST['uf'] ?? '';
$suframa = $_POST['suframa'] ?? 'Não';
$suspensao_ipi = $_POST['suspensao_ipi'] ?? 'Não';
$cotado_por = strtoupper($_POST['cotado_por'] ?? '');
$dolar = str_replace(',', '.', $_POST['dolar'] ?? '');
$itens = $_POST['itens'] ?? [];
$incluir_net = $_POST['incluir_net'] ?? 'false';
$num_orcamento = date('YmdHis');

try {
    $pdo->beginTransaction();
    $sql_insert = "INSERT INTO `cot_cotacoes_importadas` (`DATA`, `RAZÃO SOCIAL`, `UF`, `COD DO PRODUTO`, `PRODUTO`, `UNIDADE`, `ORIGEM`, `NCM`, `VOLUME`, `EMBALAGEM_KG`, `IPI %`, `ICMS`, `PREÇO NET USD/KG`, `PREÇO FULL USD/KG`, `SUFRAMA`, `SUSPENCAO_IPI`, `COTADO_POR`, `DOLAR COTADO`, `NUM_ORCAMENTO`, `DISPONIBILIDADE`) VALUES (:data, :razao_social, :uf, :codigo, :produto, :unidade, :origem, :ncm, :volume, :embalagem, :ipi, :icms, :preco_net, :preco_full, :suframa, :suspensao_ipi, :cotado_por, :dolar, :num_orcamento, :disponibilidade)";
    $stmt_insert = $pdo->prepare($sql_insert);

    foreach ($itens as $item) {
        if (empty($item['codigo'])) continue;
        $stmt_insert->execute([
            ':data' => $data, ':razao_social' => $cliente, ':uf' => $uf, ':codigo' => $item['codigo'],
            ':produto' => $item['produto'], ':unidade' => $item['unidade'], ':origem' => $item['origem'],
            ':ncm' => $item['ncm'], ':volume' => str_replace(',', '.', $item['volume']), ':embalagem' => str_replace(',', '.', $item['embalagem']),
            ':ipi' => str_replace(',', '.', $item['ipi']), ':icms' => str_replace(',', '.', $item['icms']),
            ':preco_net' => str_replace(',', '.', $item['preco_net']), ':preco_full' => str_replace(',', '.', $item['preco_full']),
            ':suframa' => $suframa, ':suspensao_ipi' => $suspensao_ipi, 
            ':cotado_por' => $cotado_por, ':dolar' => $dolar, ':num_orcamento' => $num_orcamento, 
            ':disponibilidade' => $item['disponibilidade']
        ]);
    }
    $pdo->commit();

    // Redireciona de volta com os parâmetros para o JavaScript acionar o próximo passo
    header("Location: incluir_orcamento.php?sucesso=1&num_orcamento=$num_orcamento&incluir_net=$incluir_net");
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("Erro ao salvar orçamento: " . $e->getMessage());
}
?>
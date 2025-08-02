<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');
require_once 'conexao.php'; 

require_once __DIR__ . '/vendor/autoload.php'; 

function logToFile($message, $logFileName = 'erroslog.txt') {
    // ... (sua função de log aqui) ...
}

if (!isset($_SESSION['representante_email'])) {
    die("Acesso não autorizado. Faça login."); 
}

$pedido_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$pedido_id || $pedido_id <= 0) {
    die("Erro: ID do pedido inválido ou não fornecido.");
}

$pedido = null;
$cliente = null;
$itens_pedido = [];
$responsavel_nome = $_SESSION['representante_nome'] ?? $_SESSION['representante_email'] ?? 'N/A';

try {
    $sql_pedido_cliente = "SELECT pa.*, cc.razao_social, cc.cnpj
                           FROM pedidos_amostra pa
                           INNER JOIN cot_clientes cc ON pa.id_cliente = cc.id
                           WHERE pa.id = :pedido_id";
    $stmt_pedido_cliente = $pdo->prepare($sql_pedido_cliente);
    $stmt_pedido_cliente->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
    $stmt_pedido_cliente->execute();
    $pedido = $stmt_pedido_cliente->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) { throw new Exception("Pedido de amostra com ID {$pedido_id} não encontrado."); }

    $sql_itens = "SELECT ipa.*, ce.produto as produto_nome, ce.unidade
                  FROM itens_pedido_amostra ipa
                  INNER JOIN cot_estoque ce ON ipa.id_produto = ce.id
                  WHERE ipa.id_pedido_amostra = :pedido_id
                  ORDER BY ipa.id ASC";
    $stmt_itens = $pdo->prepare($sql_itens);
    $stmt_itens->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
    $stmt_itens->execute();
    $itens_pedido = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException | Exception $e) {
    logToFile("ERRO ao buscar dados para PDF pedido ID {$pedido_id}: " . $e->getMessage());
    die("Erro ao carregar dados do pedido para gerar o PDF: " . htmlspecialchars($e->getMessage()));
}


// --- CSS Básico ---
$css = "
<style>
    body { font-family: sans-serif; font-size: 10pt; }
    h2, h3 { color: #333; }
    h2 { text-align: center; margin-bottom: 20px; }
    h3 { border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 25px; margin-bottom: 10px; font-size: 12pt; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    th, td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; }
    thead th { font-weight: bold; }
    .header-info p { margin: 2px 0; }
    .section-cliente td:first-child { background-color: #EEDDEE; font-weight: bold; width: 140px; } 
    .section-contato td:first-child { background-color: #FFA07A; font-weight: bold; width: 140px; } 

    /* ALTERAÇÃO AQUI: Estilo do cabeçalho da tabela de produtos */
    .section-produtos th { background-color: #6A0DAD; color: white; } 

    .section-info td:first-child { background-color: #FFA07A; font-weight: bold; width: 200px; } 
    .logo { float: right; max-width: 150px; max-height: 60px; margin-bottom: 10px;}
    .clear { clear: both; }
    .text-center { text-align: center; }
    .produto-linha td { font-size: 9pt; }
    .no-border { border: none; } 
</style>
";

// --- HTML ---
$html = "<html><head>{$css}</head><body>";

// Cabeçalho com Logo e Título
$logoPath = 'assets/LOGO.svg'; 
$html .= "<table class='no-border'><tr>";
// ALTERAÇÃO AQUI: Título do documento
$html .= "<td class='no-border' style='width: 70%; vertical-align: middle;'><h2>Pedido de Amostras Especiais</h2></td>";
$html .= "<td class='no-border' style='width: 30%; text-align: right; vertical-align: top;'><img src='{$logoPath}' alt='Logo' class='logo'></td>";
$html .= "</tr></table><div class='clear'></div>";


// Informações Gerais
$html .= "<div class='header-info'>";
$html .= "<p><strong>Pedido Nº:</strong> " . htmlspecialchars($pedido['numero_referencia']) . "</p>";
$html .= "<p><strong>Solicitado por:</strong> " . htmlspecialchars(ucwords(strtolower($responsavel_nome))) . "</p>";
$html .= "<p><strong>Data da Solicitação:</strong> " . date('d/m/Y H:i:s', strtotime($pedido['data_pedido'])) . "</p>";
$html .= "</div><hr>";

// Dados do Cliente
$html .= "<h3>Dados do Cliente</h3>";
$html .= "<table class='section-cliente'>";
$html .= "<tr><td><strong>Razão Social:</strong></td><td>" . htmlspecialchars($pedido['razao_social'] ?? 'N/A') . "</td></tr>";
$html .= "<tr><td><strong>CNPJ:</strong></td><td>" . htmlspecialchars($pedido['cnpj'] ?? 'N/A') . "</td></tr>";
$html .= "</table>";

// ALTERAÇÃO AQUI: E-mail e Telefone foram removidos
$html .= "<table class='section-contato'>";
$html .= "<tr><td><strong>Contato:</strong></td><td>" . htmlspecialchars($pedido['contato_cliente'] ?? 'N/A') . "</td></tr>";
$html .= "</table>";

// Produtos Solicitados
$html .= "<h3>Produtos Solicitados</h3>";
$html .= "<table class='section-produtos'>";
$html .= "<thead><tr><th>Produto</th><th>Quantidade</th><th>Unidade</th><th>Fabricante</th><th>Estoque?</th><th>Fracionar?</th></tr></thead>";
$html .= "<tbody>";
if (!empty($itens_pedido)) {
    foreach ($itens_pedido as $item) {
        $html .= "<tr class='produto-linha'>";
        $html .= "<td>" . htmlspecialchars($item['produto_nome'] ?? 'N/A') . "</td>";
        $html .= "<td class='text-center'>" . htmlspecialchars(number_format($item['quantidade'], 3, ',', '.')) . "</td>";
        $html .= "<td class='text-center'>" . htmlspecialchars($item['unidade'] ?? 'N/A') . "</td>";
        $html .= "<td>" . htmlspecialchars($item['fabricante'] ?? 'N/A') . "</td>";
        $html .= "<td class='text-center'>" . htmlspecialchars($item['disponivel_estoque'] ?? 'N/A') . "</td>";
        $html .= "<td class='text-center'>" . htmlspecialchars($item['necessita_fracionamento'] ?? 'N/A') . "</td>";
        $html .= "</tr>";
    }
} else {
    $html .= "<tr><td colspan='6' class='text-center'>Nenhum produto listado.</td></tr>";
}
$html .= "</tbody></table>";

// Informações Adicionais
$html .= "<h3>Informações Adicionais</h3>";
$html .= "<table class='section-info'>";
$html .= "<tr><td><strong>Informações sobre o Projeto:</strong></td><td>" . nl2br(htmlspecialchars($pedido['info_projeto'] ?? 'N/A')) . "</td></tr>";
$html .= "<tr><td><strong>Etapa do Projeto:</strong></td><td>" . htmlspecialchars($pedido['etapa_projeto'] ?? 'N/A') . "</td></tr>";
$html .= "<tr><td><strong>Data Limite para Atendimento:</strong></td><td>" . (!empty($pedido['data_limite']) ? date('d/m/Y', strtotime($pedido['data_limite'])) : 'N/A') . "</td></tr>";
$html .= "<tr><td><strong>Autorizado Por:</strong></td><td>" . htmlspecialchars($pedido['autorizado_por'] ?? 'N/A') . "</td></tr>";
$html .= "</table>";

$html .= "</body></html>";

// Gerar o PDF com mPDF
try {
    $mpdfConfig = [
        'mode' => 'utf-8', 'format' => 'A4',
        'margin_left' => 15, 'margin_right' => 15, 'margin_top' => 18,
        'margin_bottom' => 15, 'margin_header' => 9, 'margin_footer' => 9,
        'tempDir' => __DIR__ . '/tmp'
    ];
    $mpdf = new \Mpdf\Mpdf($mpdfConfig);
    $mpdf->SetTitle("Pedido de Amostras Especiais Nº " . $pedido['numero_referencia']);
    $mpdf->SetAuthor("Sistema Innovasell");
    $mpdf->WriteHTML($html);
    $outputFilename = "Pedido_Amostra_Especial_" . $pedido['numero_referencia'] . ".pdf";
    $mpdf->Output($outputFilename, 'I'); 
    exit;
} catch (\Mpdf\MpdfException $e) { 
    logToFile("ERRO mPDF ao gerar PDF pedido ID {$pedido_id}: " . $e->getMessage());
    die ('Erro ao gerar o PDF: ' . $e->getMessage());
} catch (Exception $e) { 
    logToFile("ERRO GERAL ao gerar PDF pedido ID {$pedido_id}: " . $e->getMessage());
    die ('Erro inesperado ao gerar o PDF: ' . $e->getMessage());
}
?>
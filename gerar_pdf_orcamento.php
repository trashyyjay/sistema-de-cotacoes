<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexao.php';
require_once __DIR__ . '/vendor/autoload.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['representante_email'])) {
    header('Location: index.html');
    exit();
}
use Mpdf\Mpdf;
$mpdf = new Mpdf();

$num_orcamento = $_GET['num'] ?? '';


require_once 'conexao.php';
$_POST['incluir_net'] = $_GET['incluir_net'] ?? 'false';

$incluir_net = isset($_GET['incluir_net']) && $_GET['incluir_net'] === 'true';
$num = trim($_GET['num'] ?? '');

if (strlen($num) < 10 || !ctype_digit($num)) {
  die("Número do orçamento inválido.");
}


$stmt = $pdo->prepare("SELECT * FROM cot_cotacoes_importadas WHERE NUM_ORCAMENTO = ?");
$stmt->execute([$num]);

$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($dados)) {
    ob_clean(); // limpar qualquer saída anterior
    $mpdf->WriteHTML('<h2>Orçamento não encontrado.</h2>');
    $mpdf->Output();
    exit;
}


// Montar o PDF

$html = '
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 8pt; }
        .logo { width: 200px; }
        .header-table td { padding: 4px; }
        .product-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .product-table th, .product-table td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: center;
        }
        .product-table th {
            background-color: #6c3483;
            color: white;
        }
        .observacoes {
            margin-top: 20px;
            font-size: 9pt;
        }
    </style>
</head>
<body>

    <table width="100%">
        <tr>
            <td><img src="assets/LOGO.svg" class="logo"></td>
            <td align="right">
                <h2 style="color: #6c3483;">PROPOSTA COMERCIAL</h2>
<p>' . date("d/m/Y") . '<br>Orçado por<br><strong>' 
. ucwords($_SESSION["representante_nome"] ?? "") . ' ' . ucwords($_SESSION["representante_sobrenome"] ?? "") . 
'</strong><br>' . ($_SESSION["representante_email"] ?? "email@desconhecido.com") . '</p>


            </td>
        </tr>
    </table>

    <table class="header-table" width="100%">
        <tr><td><strong>CLIENTE</strong> ' . ($dados[0]["RAZÃO SOCIAL"] ?? "") . '</td></tr>
        <tr><td><strong>UF</strong> ' . ($dados[0]["UF"] ?? "") . '</td></tr>
        <tr><td><strong>ORÇAMENTO Nº</strong> ' . $num . '</td></tr>
    </table>

    <table class="product-table">
        <tr>
  <th>Código</th>
  <th>Produto</th>
  <th>Emb./KG</th>
  <th>NCM</th>
  <th>Volume</th>
  <th>IPI %</th>
  <th>ICMS</th>' .
  ($incluir_net ? '<th>PREÇO NET USD/KG</th>' : '') . '
  <th>PREÇO FULL USD/KG</th>
  <th>DISPONIBILIDADE</th>
</tr>';

foreach ($dados as $d) {
    $html .= '<tr>
        <td>' . $d["COD DO PRODUTO"] . '</td>
        <td>' . $d["PRODUTO"] . '</td>
        <td>' . $d["EMBALAGEM_KG"] . '</td>
        <td>' . $d["NCM"] . '</td>
        <td>' . $d["VOLUME"] . '</td>
        <td>' . number_format((float)str_replace("%", "", $d["IPI %"]), 2, ',', '.') . '%</td>
        <td>' . number_format((float)str_replace("%", "", $d["ICMS"]), 2, ',', '.') . '%</td>';

    if ($incluir_net) {
        $html .= '<td>$' . number_format($d["PREÇO NET USD/KG"], 2) . '</td>';
    }

    $html .= '<td>$' . number_format($d["PREÇO FULL USD/KG"], 2) . '</td>
        <td>' . $d["DISPONIBILIDADE"] . '</td>
    </tr>';
}



$html .= '</table>
    <table width="100%" style="margin-top: 20px; font-size: 9pt; border-collapse: collapse;">
    <tr>
        <td style="padding: 3px;">1. Preço em Dólar (Convertido na data de Emissão da nota Fiscal pela taxa do dólar Ptax do dia anterior ao Faturamento);</td>
    </tr>
    <tr>
        <td style="padding: 3px;">2. Preço Full: Inclui PIS, COFINS, IPI e ICMS;</td>
    </tr>
    <tr>
        <td style="padding: 3px;">3. Condição de pagamento: primeira compra à vista;</td>
    </tr>
    <tr>
        <td style="padding: 3px;">4. Frete: FOB - Por conta do destinatário;</td>
    </tr>
    <tr>
        <td style="padding: 3px;">5. Frete: CIF - Acima de R$ 3.000,00 para grande SÃO PAULO;</td>
    </tr>
    <tr>
        <td style="padding: 3px;">6. Validade da proposta: 2 meses</td>
    </tr>
</table>

</body>
</html>';
$mpdf = new Mpdf([
    'format' => 'A4',
    'orientation' => 'P',
    'margin_top' => 10,
    'margin_bottom' => 15,
    'margin_left' => 10,
    'margin_right' => 10
]);
$mpdf->SetDisplayMode('fullpage'); // mostra o PDF ocupando a página inteira
$mpdf->SetDisplayMode('real');     // mostra com zoom 100%
$mpdf->SetDisplayMode('default');  // comportamento padrão
$mpdf->WriteHTML($html);
$arquivo = __DIR__ . "/tmp/orcamento_" . $num . ".pdf";
if (!is_writable(dirname($arquivo))) {
    die("❌ A pasta tmp/ não tem permissão de escrita.");
}
ob_clean(); // limpa qualquer buffer acidentalS
header('Content-Type: application/pdf');
$mpdf->Output('', \Mpdf\Output\Destination::INLINE);
$mpdf->Output($arquivo, \Mpdf\Output\Destination::FILE);


    // Enviar e-mail automaticamente
    $representante_email = urlencode($_SESSION['representante_email'] ?? '');
    $incluir_net = $_GET['incluir_net'] ?? 'false';
    $url_envio = "http://www.innovasell.com.br/sistema-cotacoes/enviar_orcamento.php?num={$num_orcamento}&email={$representante_email}&incluir_net={$incluir_net}";
        $response = @file_get_contents($url_envio);

    if ($response === false) {
        error_log("❌ Falha ao enviar orçamento {$num_orcamento} por e-mail.");
    } else {
        error_log("✅ E-mail enviado com sucesso para orçamento {$num_orcamento}.");
    }

    exit;
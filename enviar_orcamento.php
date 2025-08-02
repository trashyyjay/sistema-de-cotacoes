
<?php
$email = $_GET['email'] ?? null;
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("E-mail de destinatário inválido ou ausente.");
}

require_once 'conexao.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';

// Habilita exibição de erros (remova em produção)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Captura o número do orçamento
$num = $_GET['num'] ?? null;
if (!$num) {
    die("Número do orçamento não informado.");
}
$incluir_net = $_POST['incluir_net'] ?? $_GET['incluir_net'] ?? 'false';

file_put_contents(__DIR__ . '/tmp/log_incluir_net.txt', "🧪 incluir_net: " . $incluir_net . "\n", FILE_APPEND);


// Buscar dados do orçamento
$stmt = $pdo->prepare("SELECT * FROM cot_cotacoes_importadas WHERE NUM_ORCAMENTO = ?");
$stmt->execute([$num]);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$dados) {
    die("Orçamento não encontrado.");
}

// Dados do cliente
$cliente = htmlspecialchars($dados[0]['RAZÃO SOCIAL'] ?? '');
$uf = htmlspecialchars($dados[0]['UF'] ?? '');
$data = date('d/m/Y', strtotime($dados[0]['DATA'] ?? date('Y-m-d')));
$cotado_por = htmlspecialchars($dados[0]['COTADO_POR'] ?? '');

$html = "<h2 style='color: #2a7ae2; font-family: Arial, sans-serif;'>Orçamento Nº {$num}</h2>";
$html .= "<p style='font-family: Arial, sans-serif;'><strong>Cliente:</strong> {$cliente}</p>";
$html .= "<p style='font-family: Arial, sans-serif;'><strong>UF:</strong> {$uf}</p>";
$html .= "<p style='font-family: Arial, sans-serif;'><strong>Data:</strong> {$data}</p>";
$html .= "<p style='font-family: Arial, sans-serif;'><strong>Cotado por:</strong> {$cotado_por}</p>";
$html .= "<br>";
$html .= "<table style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 14px;'>";

$html .= "<tr style='background-color: #f2f2f2;'>
            <th style='border: 1px solid #ddd; padding: 8px;'>Código</th>
            <th style='border: 1px solid #ddd; padding: 8px;'>Produto</th>
            <th style='border: 1px solid #ddd; padding: 8px;'>Emb./KG</th>
            <th style='border: 1px solid #ddd; padding: 8px;'>NCM</th>
            <th style='border: 1px solid #ddd; padding: 8px;'>Volume</th>
            <th style='border: 1px solid #ddd; padding: 8px;'>IPI %</th>
            <th style='border: 1px solid #ddd; padding: 8px;'>ICMS</th>";

if ($incluir_net === 'true') {
    $html .= "<th style='border: 1px solid #ddd; padding: 8px;'>Preço NET USD/KG</th>";
}

$html .= "<th style='border: 1px solid #ddd; padding: 8px;'>Preço Full USD/KG</th>
          <th style='border: 1px solid #ddd; padding: 8px;'>Disponibilidade</th>
        </tr>";


        foreach ($dados as $d) {
            $codigo     = htmlspecialchars($d['COD DO PRODUTO'] ?? '');
            $produto    = htmlspecialchars($d['PRODUTO'] ?? '');
            $embalagem  = htmlspecialchars($d['EMBALAGEM_KG'] ?? '');
            $ncm        = htmlspecialchars($d['NCM'] ?? '');
            $volume     = htmlspecialchars($d['VOLUME'] ?? '');
            $ipi        = htmlspecialchars($d['IPI %'] ?? '');
            $icms       = htmlspecialchars($d['ICMS'] ?? '');
            $preco_net  = htmlspecialchars($d['PREÇO NET USD/KG'] ?? '');
            $preco_full = htmlspecialchars($d['PREÇO FULL USD/KG'] ?? '');
            $dispon     = htmlspecialchars($d['DISPONIBILIDADE'] ?? '');
        
            $html .= "<tr>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$codigo}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$produto}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$embalagem}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$ncm}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$volume}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$ipi}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$icms}</td>";
        
            if ($incluir_net === 'true') {
                $html .= "<td style='border: 1px solid #ddd; padding: 8px;'>{$preco_net}</td>";
            }
        
            $html .= "<td style='border: 1px solid #ddd; padding: 8px;'>{$preco_full}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$dispon}</td>
            </tr>";
        }
        

$html .= "</table>";


$pdfPath = __DIR__ . "/tmp/orcamento_" . $num . ".pdf";

// Enviar e-mail com PHPMailer
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'marketing@innovasell.com.br';
$mail->Password   = 'rqwu hpog vkjb zogr'; // Substituir por senha de app segura
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
$mail->CharSet    = 'UTF-8';
$mail->addAttachment($pdfPath); // Anexa o PDF salvo
$mail->addAddress($email); // ESSA LINHA É OBRIGATÓRIA
$mail->isHTML(true);
$mail->Subject = "Orçamento Nº {$num} | {$cliente}";
$mail->Body = $html;
if (empty($html)) {
    error_log("❌ ERRO: Corpo do e-mail está vazio para orçamento {$num} em " . date('Y-m-d H:i:s'));
    die("Erro: corpo do e-mail vazio");
}


$remetente = 'marketing@innovasell.com.br';
if (!filter_var($remetente, FILTER_VALIDATE_EMAIL)) {
    die("❌ Remetente inválido!");
}

try {
    $mail->send();

    // Log de sucesso
    file_put_contents(__DIR__ . "/tmp/log_enviar_orcamento.txt", 
        "✅ E-mail enviado para {$email} em " . date("Y-m-d H:i:s") . "\n", 
        FILE_APPEND
    );

    http_response_code(200);
    echo "✅ Orçamento enviado com sucesso.";
    exit;

} catch (Exception $e) {

    // Log de erro
    file_put_contents(__DIR__ . "/tmp/log_enviar_orcamento.txt", 
        "❌ ERRO ao enviar para {$email}: {$mail->ErrorInfo} em " . date("Y-m-d H:i:s") . "\n", 
        FILE_APPEND
    );

    http_response_code(500);
    echo "❌ Erro ao enviar e-mail: " . $mail->ErrorInfo;
    exit;
}


<?php
session_start();
// DEFINIR FUSO HORÁRIO LOCAL
date_default_timezone_set('America/Sao_Paulo');
require_once 'conexao.php'; // Inclui a conexão PDO

// --- INÍCIO: Includes e use statements para PHPMailer ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// VERIFIQUE ESTES CAMINHOS OU USE O AUTOLOAD SE ESTIVER USANDO COMPOSER
require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
// --- FIM: Includes e use statements ---


// ==================================================
// === FUNÇÃO PARA LOGAR EM ARQUIVO PERSONALIZADO ===
// ==================================================
function logToFile($message, $logFileName = 'erroslog.txt') {
    $logFilePath = __DIR__ . '/' . $logFileName;
    $timestamp = date("Y-m-d H:i:s");
    if (is_array($message) || is_object($message)) {
        $message = print_r($message, true);
    }
    $logEntry = "[{$timestamp}] " . $message . PHP_EOL;
    @file_put_contents($logFilePath, $logEntry, FILE_APPEND | LOCK_EX);
}
// ==================================================


// 1. Verificar se o usuário está logado
if (!isset($_SESSION['representante_email'])) {
    $_SESSION['message'] = "Erro: Acesso não autorizado. Faça login novamente.";
    $_SESSION['message_type'] = "danger";
    logToFile("Tentativa de acesso não autorizado a processa_pedido_amostra.php.");
    header('Location: index.html');
    exit();
}

// 2. Verificar se o método é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Erro: Método de requisição inválido.";
    $_SESSION['message_type'] = "danger";
     logToFile("Tentativa de acesso a processa_pedido_amostra.php com método inválido: " . $_SERVER['REQUEST_METHOD']);
    header('Location: incluir_ped_amostras.php');
    exit();
}

// =====================================================================
// === 3. RECEBER TODOS OS DADOS DO FORMULÁRIO E SESSÃO ===
// =====================================================================
$numero_referencia = trim($_POST['numero_pedido'] ?? '');

// --- Pega email e nome do responsável DIRETAMENTE DA SESSÃO ---
$responsavel_email = trim($_SESSION['representante_email'] ?? 'Email não encontrado na sessão');
$primeiro_nome = $_SESSION['representante_nome'] ?? '';
$sobrenome = $_SESSION['representante_sobrenome'] ?? '';

$responsavel_nome_completo = trim($primeiro_nome . ' ' . $sobrenome);
if (empty($responsavel_nome_completo)) {
    logToFile("Aviso: Nome completo vazio na sessão em processa_pedido. Usando email: " . $responsavel_email);
    $responsavel_nome = $responsavel_email; // $responsavel_nome será usado para o EMAIL
} else {
    $responsavel_nome = $responsavel_nome_completo; // $responsavel_nome será usado para o EMAIL
}
// --- Fim da combinação ---

// Verifica se o nome não está vazio, caso contrário, usa o email como nome também
if (empty($responsavel_nome)) {
    logToFile("Aviso: Nome do representante vazio na sessão. Usando email como nome: " . $responsavel_email);
    $responsavel_nome = $responsavel_email;
}
// --- Fim de pegar dados da sessão ---

$responsavel_nome_completo = ucwords(strtolower($responsavel_nome_completo));

// Definir o que salvar no banco (Assumindo que a coluna 'responsavel_pedido' guarda o email)
$responsavel_pedido_db = $responsavel_email;

// --- Restante do recebimento ---
$id_cliente = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
$contato_cliente = trim($_POST['cliente_contato'] ?? '');
$email_contato = filter_input(INPUT_POST, 'cliente_email', FILTER_VALIDATE_EMAIL);
$telefone_contato = trim($_POST['cliente_telefone'] ?? '');
$info_projeto = trim($_POST['info_projeto'] ?? '');
$etapa_projeto = trim($_POST['etapa_projeto'] ?? '');
$data_limite_str = trim($_POST['data_limite'] ?? '');
$autorizado_por_email = filter_input(INPUT_POST, 'autorizado_por', FILTER_VALIDATE_EMAIL);
$produto_ids = $_POST['produto_id'] ?? [];
$quantidades = $_POST['quantidade'] ?? [];
$fabricantes = $_POST['fabricante'] ?? [];
$estoques = $_POST['estoque'] ?? [];
$fracionamentos = $_POST['fracionamento'] ?? [];
// =====================================================================


// =====================================================================
// === 4. VALIDAÇÕES ===
// =====================================================================
$errors = [];
$data_limite_db = null;

if (empty($numero_referencia)) { $errors[] = "Número de referência do pedido está faltando."; }
// Verifica se o email (que será salvo no DB) é válido
if (empty($responsavel_pedido_db) || !filter_var($responsavel_pedido_db, FILTER_VALIDATE_EMAIL)) { $errors[] = "Email do responsável inválido na sessão."; }
if ($id_cliente === false || $id_cliente <= 0) { $errors[] = "Cliente inválido ou não selecionado."; }
if ($autorizado_por_email === false) { $errors[] = "Aprovador inválido ou não selecionado."; }

if (!empty($data_limite_str)) {
    $data_limite_obj = DateTime::createFromFormat('Y-m-d', $data_limite_str);
    if ($data_limite_obj === false) { $errors[] = "Formato da Data Limite inválido. Use AAAA-MM-DD."; }
    else { $data_limite_db = $data_limite_obj->format('Y-m-d'); }
}

if (!empty($_POST['cliente_email']) && $email_contato === false) { $errors[] = "Formato do E-mail do contato inválido."; }

$num_itens = count($produto_ids);
if ($num_itens === 0) { $errors[] = "Nenhum produto foi adicionado ao pedido."; }
elseif (count($quantidades) !== $num_itens || count($fabricantes) !== $num_itens || count($estoques) !== $num_itens || count($fracionamentos) !== $num_itens) {
    $errors[] = "Erro: Inconsistência nos dados dos produtos enviados.";
} else {
    for ($i = 0; $i < $num_itens; $i++) {
        if (!isset($quantidades[$i]) || !is_numeric($quantidades[$i]) || floatval($quantidades[$i]) <= 0) { $errors[] = "Quantidade inválida para o produto ID " . htmlspecialchars($produto_ids[$i]) . "."; }
        if (!isset($estoques[$i]) || !in_array($estoques[$i], ['SIM', 'NÃO'])) { $errors[] = "Valor inválido para 'Estoque' no produto ID " . htmlspecialchars($produto_ids[$i]) . "."; }
        if (!isset($fracionamentos[$i]) || !in_array($fracionamentos[$i], ['SIM', 'NÃO'])) { $errors[] = "Valor inválido para 'Fracionamento' no produto ID " . htmlspecialchars($produto_ids[$i]) . "."; }
    }
}
// =====================================================================


// 5. Se houver erros de validação, redireciona de volta
if (!empty($errors)) {
    $_SESSION['message'] = "Erro ao processar o pedido:<br>" . implode("<br>", $errors);
    $_SESSION['message_type'] = "danger";
    logToFile("Erro de validação no pedido {$numero_referencia}: " . implode("; ", $errors));
    header('Location: incluir_ped_amostras.php');
    exit();
}


// --- BLOCO PRINCIPAL: Banco de Dados e Email ---
$pedidoSalvo = false;
$emailEnviado = false;
$erroEmail = null;
$id_pedido_amostra = null;

$pdo->beginTransaction();
try {
    // 1. Inserir pedido principal
    $sql_pedido = "INSERT INTO pedidos_amostra
                     (numero_referencia, id_cliente, responsavel_pedido, contato_cliente, telefone_contato, email_contato, info_projeto, etapa_projeto, data_limite, autorizado_por, data_pedido)
                   VALUES
                     (:numero_referencia, :id_cliente, :responsavel_pedido, :contato_cliente, :telefone_contato, :email_contato, :info_projeto, :etapa_projeto, :data_limite, :autorizado_por, NOW())";
    $stmt_pedido = $pdo->prepare($sql_pedido);

    // Binds (usando o email do responsável para salvar no DB)
    $stmt_pedido->bindParam(':numero_referencia', $numero_referencia);
    $stmt_pedido->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt_pedido->bindParam(':responsavel_pedido', $responsavel_pedido_db); // << Salva o email
    $stmt_pedido->bindParam(':contato_cliente', $contato_cliente);
    $stmt_pedido->bindParam(':telefone_contato', $telefone_contato);
    $stmt_pedido->bindParam(':email_contato', $email_contato);
    $stmt_pedido->bindParam(':info_projeto', $info_projeto);
    $stmt_pedido->bindParam(':etapa_projeto', $etapa_projeto);
    $stmt_pedido->bindParam(':data_limite', $data_limite_db);
    $stmt_pedido->bindParam(':autorizado_por', $autorizado_por_email);
    $stmt_pedido->execute();
    $id_pedido_amostra = $pdo->lastInsertId();

    // 2. Inserir itens
    if ($id_pedido_amostra && $num_itens > 0) {
        $sql_item = "INSERT INTO itens_pedido_amostra
                       (id_pedido_amostra, id_produto, quantidade, fabricante, disponivel_estoque, necessita_fracionamento)
                     VALUES
                       (:id_pedido_amostra, :id_produto, :quantidade, :fabricante, :disponivel_estoque, :necessita_fracionamento)";
        $stmt_item = $pdo->prepare($sql_item);
        for ($i = 0; $i < $num_itens; $i++) {
            $stmt_item->bindParam(':id_pedido_amostra', $id_pedido_amostra, PDO::PARAM_INT);
            $stmt_item->bindParam(':id_produto', $produto_ids[$i], PDO::PARAM_INT);
            $stmt_item->bindParam(':quantidade', $quantidades[$i]);
            $stmt_item->bindParam(':fabricante', $fabricantes[$i]);
            $stmt_item->bindParam(':disponivel_estoque', $estoques[$i]);
            $stmt_item->bindParam(':necessita_fracionamento', $fracionamentos[$i]);
            $stmt_item->execute();
        }
    } elseif (!$id_pedido_amostra) {
         throw new Exception("Falha ao obter o ID do pedido principal após o INSERT.");
    }

    $pdo->commit();
    $pedidoSalvo = true;
    logToFile("Pedido {$id_pedido_amostra} (Ref: {$numero_referencia}) salvo com sucesso no BD.");

} catch (PDOException $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    logToFile("ERRO PDOException ao salvar Pedido {$numero_referencia}: " . $e->getMessage());
    $detailed_error_message = "ERRO DETALHADO DO BANCO DE DADOS (PDOException): <br><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    $_SESSION['message'] = $detailed_error_message; // Manter para debug
    $_SESSION['message_type'] = "danger";
    header('Location: incluir_ped_amostras.php');
    exit();
} catch (Exception $e) {
     if ($pdo->inTransaction()) { $pdo->rollBack(); }
     logToFile("ERRO Exception GERAL em processa_pedido_amostra (Pedido {$numero_referencia}): " . $e->getMessage());
     $_SESSION['message'] = "Ocorreu um erro inesperado durante o processamento (" . htmlspecialchars($e->getMessage()) ."). Verifique os logs ou contate o suporte.";
     $_SESSION['message_type'] = "danger";
     header('Location: incluir_ped_amostras.php');
     exit();
}


// --- INÍCIO: Enviar Email se o Pedido foi Salvo ---
if ($pedidoSalvo && $id_pedido_amostra) {

    // ** Buscar dados adicionais para o Email (Cliente e Produtos) **
    $cliente_info = null;
    $produtos_info = [];
    try {
        $stmt_cli = $pdo->prepare("SELECT razao_social, cnpj FROM cot_clientes WHERE id = :id_cliente LIMIT 1");
        $stmt_cli->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $stmt_cli->execute();
        $cliente_info = $stmt_cli->fetch(PDO::FETCH_ASSOC);
        if ($cliente_info === false) { $cliente_info = []; logToFile("Aviso: Cliente com ID {$id_cliente} não encontrado no BD para o email do pedido {$id_pedido_amostra}."); }

        if (!empty($produto_ids)) {
            $placeholders = implode(',', array_fill(0, count($produto_ids), '?'));
            $sql_prod_info = "SELECT id, produto, unidade FROM cot_estoque WHERE id IN ($placeholders)";
            $stmt_prod_info = $pdo->prepare($sql_prod_info);
            $stmt_prod_info->execute(array_values($produto_ids));
            $produtos_db_assoc = [];
            while ($p = $stmt_prod_info->fetch(PDO::FETCH_ASSOC)) { $produtos_db_assoc[$p['id']] = ['nome' => $p['produto'], 'unidade' => $p['unidade']]; }

            for ($i = 0; $i < $num_itens; $i++) {
                 $pid = $produto_ids[$i];
                 $produtos_info[] = [
                    'id' => $pid,
                    'nome' => $produtos_db_assoc[$pid]['nome'] ?? 'Prod ID ' . $pid . ' s/ nome',
                    'unidade' => $produtos_db_assoc[$pid]['unidade'] ?? 'N/A',
                    'quantidade' => $quantidades[$i],
                    'fabricante' => $fabricantes[$i],
                    'estoque' => $estoques[$i],
                    'fracionamento' => $fracionamentos[$i]
                 ];
            }
        }
    } catch (PDOException $e) { logToFile("ERRO PDOException ao buscar dados adicionais p/ email (Ped {$id_pedido_amostra}): " . $e->getMessage());
    } catch (Exception $e) { logToFile("ERRO Exception ao buscar dados adicionais p/ email (Ped {$id_pedido_amostra}): " . $e->getMessage()); }


    // ** Montar o Corpo do Email HTML **
    $numero_ref_html = htmlspecialchars($numero_referencia ?? 'N/A');
    // << USA O NOME DO RESPONSÁVEL VINDO DA SESSÃO >>
    $responsavel_html = htmlspecialchars($responsavel_nome_completo ?? 'N/A');
    $cliente_razao_html = htmlspecialchars($cliente_info['razao_social'] ?? 'N/A');
    $cliente_cnpj_html = htmlspecialchars($cliente_info['cnpj'] ?? 'N/A');
    $contato_cliente_html = htmlspecialchars($contato_cliente ?? 'N/A');
    $email_contato_html = htmlspecialchars($email_contato ?? 'N/A');
    $telefone_contato_html = htmlspecialchars($telefone_contato ?? 'N/A');
    $info_projeto_html = nl2br(htmlspecialchars($info_projeto ?? 'N/A'));
    $etapa_projeto_html = htmlspecialchars($etapa_projeto ?? 'N/A');
    $data_limite_html = ($data_limite_db ? date('d/m/Y', strtotime($data_limite_db)) : 'N/A');
    $autorizado_por_html = htmlspecialchars($autorizado_por_email ?? 'N/A');

    $emailBodyHtml = "<h2>Novo Pedido de Amostra Recebido</h2>";
    $emailBodyHtml .= "<p><strong>Pedido Nº:</strong> {$numero_ref_html}</p>";
    $emailBodyHtml .= "<p><strong>Solicitado por:</strong> {$responsavel_html}</p>";
    $emailBodyHtml .= "<p><strong>Data da Solicitação:</strong> " . date('d/m/Y H:i:s') . "</p>"; // << USA HORA LOCAL
    $emailBodyHtml .= "<hr>";
    // Seção Cliente
    $emailBodyHtml .= "<h3>Dados do Cliente</h3>";
    $emailBodyHtml .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%; font-family: sans-serif; font-size: 10pt;'>";
    $emailBodyHtml .= "<tr><td style='background-color: #D8BFD8; width: 150px;'><strong>Razão Social:</strong></td><td>{$cliente_razao_html}</td></tr>";
    $emailBodyHtml .= "<tr><td style='background-color: #D8BFD8;'><strong>CNPJ:</strong></td><td>{$cliente_cnpj_html}</td></tr>";
    $emailBodyHtml .= "<tr><td style='background-color: #DC143C; color: white;'><strong>Contato:</strong></td><td>{$contato_cliente_html}</td></tr>";
    $emailBodyHtml .= "<tr><td style='background-color: #DC143C; color: white;'><strong>E-mail:</strong></td><td>{$email_contato_html}</td></tr>";
    $emailBodyHtml .= "<tr><td style='background-color: #DC143C; color: white;'><strong>Telefone:</strong></td><td>{$telefone_contato_html}</td></tr>";
    $emailBodyHtml .= "</table><br>";
    // Seção Produtos
    $emailBodyHtml .= "<h3>Produtos Solicitados</h3>";
    $emailBodyHtml .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%; font-family: sans-serif; font-size: 10pt;'>";
    $emailBodyHtml .= "<thead><tr style='background-color: #9370DB; color: white;'><th>Produto</th><th>Quantidade</th><th>Unidade</th><th>Fabricante</th><th>Estoque?</th><th>Fracionar?</th></tr></thead>";
    $emailBodyHtml .= "<tbody>";
    if (!empty($produtos_info)) {
         foreach ($produtos_info as $item) {
             $item_nome_html = htmlspecialchars($item['nome'] ?? 'N/A');
             $item_qtd_html = htmlspecialchars(number_format($item['quantidade'] ?? 0, 3, ',', '.'));
             $item_unidade_html = htmlspecialchars($item['unidade'] ?? 'N/A');
             $item_fabricante_html = htmlspecialchars($item['fabricante'] ?? 'N/A');
             $item_estoque_html = htmlspecialchars($item['estoque'] ?? 'N/A');
             $item_fracionamento_html = htmlspecialchars($item['fracionamento'] ?? 'N/A');
             $emailBodyHtml .= "<tr><td>{$item_nome_html}</td><td>{$item_qtd_html}</td><td>{$item_unidade_html}</td><td>{$item_fabricante_html}</td><td>{$item_estoque_html}</td><td>{$item_fracionamento_html}</td></tr>";
         }
    } else { $emailBodyHtml .= "<tr><td colspan='6'>Nenhum produto listado.</td></tr>"; }
    $emailBodyHtml .= "</tbody></table><br>";
    // Seção Informações Adicionais
    $emailBodyHtml .= "<h3>Informações Adicionais</h3>";
    $emailBodyHtml .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%; font-family: sans-serif; font-size: 10pt;'>";
    $emailBodyHtml .= "<tr><td style='background-color: #DC143C; color: white; width: 200px;'><strong>Informações sobre o Projeto:</strong></td><td>{$info_projeto_html}</td></tr>";
    $emailBodyHtml .= "<tr><td style='background-color: #DC143C; color: white;'><strong>Etapa do Projeto:</strong></td><td>{$etapa_projeto_html}</td></tr>";
    $emailBodyHtml .= "<tr><td style='background-color: #DC143C; color: white;'><strong>Data Limite para Atendimento:</strong></td><td>{$data_limite_html}</td></tr>";
    $emailBodyHtml .= "<tr><td style='background-color: #DC143C; color: white;'><strong>Autorizado Por:</strong></td><td>{$autorizado_por_html}</td></tr>";
    $emailBodyHtml .= "</table>";


    // ** Configurar e Enviar o Email com PHPMailer **
    $mail = new PHPMailer(true);
    try {
        // Configurações SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'marketing@innovasell.com.br';
        $mail->Password   = 'rqwu hpog vkjb zogr'; // SENHA CORRETA
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Remetente e Destinatário
        $mail->setFrom('marketing@innovasell.com.br', 'Sistema de Pedidos Innovasell');
        $mail->addAddress($autorizado_por_email);

        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = 'Novo Pedido de Amostra Recebido - N. '. htmlspecialchars($numero_referencia ?? 'N/A');
        $mail->Body    = $emailBodyHtml;

        $mail->send();
        $emailEnviado = true;
        logToFile("Email de notificação para {$autorizado_por_email} (Pedido {$id_pedido_amostra}) enviado com sucesso.");

    } catch (Exception $e) {
        $emailEnviado = false;
        $erroEmail = $mail->ErrorInfo;
        logToFile("ERRO PHPMailer para {$autorizado_por_email} (Pedido {$id_pedido_amostra}): " . $erroEmail);
    }
} else {
     logToFile("AVISO: Envio de email pulado para Pedido Ref {$numero_referencia} porque pedidoSalvo=false ou id_pedido_amostra=null.");
}
// --- FIM: Enviar Email ---


// --- Resposta Final ---
if ($pedidoSalvo) {
    if ($emailEnviado) {
        $_SESSION['message'] = "Pedido de amostra Nº {$numero_referencia} incluído com sucesso e notificação enviada!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Pedido de amostra Nº {$numero_referencia} incluído com sucesso, MAS FALHOU ao enviar a notificação por email para o aprovador. Verifique o arquivo erroslog.txt.";
        if ($erroEmail) { $_SESSION['message'] .= " <br><small>Detalhe: " . htmlspecialchars($erroEmail) . "</small>"; }
        $_SESSION['message_type'] = "warning";
    }
} else {
     $_SESSION['message'] = "Falha crítica ao processar o pedido (não foi salvo). Verifique o arquivo erroslog.txt.";
     $_SESSION['message_type'] = "danger";
}

header('Location: incluir_ped_amostras.php');
exit();

?>
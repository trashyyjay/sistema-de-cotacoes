<?php
$host = "iad2p01-23-floater.hwpcdb.int.gdcorp.tools:3308";
$usuario = "y5ce42917897960";
$senha = "q!Jnvyo5IF";
$banco = "y5ce42917897960";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$banco;charset=utf8", $usuario, $senha);
    echo "✅ Conexão realizada com sucesso!";
} catch (PDOException $e) {
    echo "❌ Erro na conexão: " . $e->getMessage();
}
?>

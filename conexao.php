<?php
$host = "iad2p01-23-floater.hwpcdb.int.gdcorp.tools";
$porta = "3308";
$usuario = "y5ce42917897960";
$senha = "q!Jnvyo5IF";
$banco = "y5ce42917897960";

try {
    $pdo = new PDO("mysql:host=$host;port=$porta;dbname=$banco;charset=utf8", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
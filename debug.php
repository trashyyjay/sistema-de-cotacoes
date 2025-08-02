<?php
file_put_contents(__DIR__ . "/tmp/log_envio_debug.txt", 
    "DEBUG: Teste executado com sucesso em " . date("Y-m-d H:i:s") . "\n", 
    FILE_APPEND
);
echo "OK";

<?php
/**
 * Configuração de Conexão com o Banco de Dados
 * Este arquivo gerencia a conexão com MySQL usando mysqli
 */

// Dados da conexão com XAMPP
$host = 'localhost';      // Host do servidor MySQL
$usuario = 'root';        // Usuário padrão do XAMPP
$senha = '';              // Senha padrão (vazia no XAMPP)
$banco = 'kitnet_db';     // Nome do banco de dados

// Criar conexão
$conexao = new mysqli($host, $usuario, $senha, $banco);

// Verificar se a conexão foi bem-sucedida
if ($conexao->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
}

// Definir charset para UTF-8 (acentuação correta)
$conexao->set_charset("utf8mb4");

?>

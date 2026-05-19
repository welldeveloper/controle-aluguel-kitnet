-- Tabela de Usuários para o Sistema
-- Gerencia login e autenticação dos administradores

CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    usuario VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'operador') DEFAULT 'operador',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso TIMESTAMP NULL
);

-- Inserir usuário admin padrão
-- Usuário: admin
-- Senha: admin123
INSERT INTO usuarios (nome, email, usuario, senha, tipo, status) VALUES
('Administrador', 'admin@kitnet.com', 'admin', '$2y$10$YourHashedPasswordHere', 'admin', 'ativo');

-- Inserir a senha corretamente com hash bcrypt
-- UPDATE usuarios SET senha = '$2y$10$6f6c.0jb4p5FpKV9N7J3VeV6I7L9Q2M1N3P5R7T9V1X3Z5B7D9' WHERE usuario = 'admin';

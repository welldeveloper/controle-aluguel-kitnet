-- Adicionar tabela de usuários ao banco de dados existente
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    usuario VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'operador') DEFAULT 'operador',
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso DATETIME
);

-- Inserir usuário admin padrão
-- Usuario: admin | Senha: admin123
INSERT INTO usuarios (nome, email, usuario, senha, tipo, ativo) VALUES
('Administrador', 'admin@kitnet.com', 'admin', '$2y$10$YourHashedPasswordHere', 'admin', TRUE);

-- Criar índices para melhor performance
CREATE INDEX idx_usuario_usuario ON usuarios(usuario);
CREATE INDEX idx_usuario_email ON usuarios(email);
CREATE INDEX idx_usuario_ativo ON usuarios(ativo);

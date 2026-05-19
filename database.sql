-- Database para Sistema de Controle de Aluguel
-- Criado para gerenciar inquilinos, quartos, aluguéis e gastos de kitnet

CREATE DATABASE IF NOT EXISTS kitnet_db;
USE kitnet_db;

-- Tabela de Inquilinos
CREATE TABLE inquilinos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    rg VARCHAR(20) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    data_entrada DATE NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE
);

-- Tabela de Quartos/Unidades
CREATE TABLE quartos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_quarto VARCHAR(10) UNIQUE NOT NULL,
    descricao TEXT,
    valor_aluguel DECIMAL(10, 2) NOT NULL,
    status ENUM('disponivel', 'ocupado') DEFAULT 'disponivel',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Alocação (Inquilino + Quarto)
CREATE TABLE alocacoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inquilino_id INT NOT NULL,
    quarto_id INT NOT NULL,
    data_inicio DATE NOT NULL,
    data_saida DATE,
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inquilino_id) REFERENCES inquilinos(id) ON DELETE CASCADE,
    FOREIGN KEY (quarto_id) REFERENCES quartos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_alocacao (inquilino_id, quarto_id, data_inicio)
);

-- Tabela de Aluguéis
CREATE TABLE alugueis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alocacao_id INT NOT NULL,
    mes_referencia DATE NOT NULL,
    valor_aluguel DECIMAL(10, 2) NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE,
    status ENUM('pendente', 'pago', 'atrasado') DEFAULT 'pendente',
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alocacao_id) REFERENCES alocacoes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_aluguel (alocacao_id, mes_referencia)
);

-- Tabela de Gastos/Despesas
CREATE TABLE gastos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo_gasto ENUM('manutencao', 'funcionario', 'agua', 'luz', 'internet', 'outro') NOT NULL,
    descricao TEXT NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data_gasto DATE NOT NULL,
    responsavel VARCHAR(255),
    comprovante_url VARCHAR(500),
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Funcionários
CREATE TABLE funcionarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    funcao VARCHAR(100) NOT NULL,
    salario DECIMAL(10, 2) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    data_inicio DATE NOT NULL,
    data_saida DATE,
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Pagamentos de Funcionários
CREATE TABLE pagamentos_funcionarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    funcionario_id INT NOT NULL,
    mes_referencia DATE NOT NULL,
    valor_pago DECIMAL(10, 2) NOT NULL,
    data_pagamento DATE,
    status ENUM('pendente', 'pago') DEFAULT 'pendente',
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_pagamento (funcionario_id, mes_referencia)
);

-- Índices para melhorar performance
CREATE INDEX idx_inquilino_cpf ON inquilinos(cpf);
CREATE INDEX idx_quarto_numero ON quartos(numero_quarto);
CREATE INDEX idx_aluguel_status ON alugueis(status);
CREATE INDEX idx_aluguel_data_vencimento ON alugueis(data_vencimento);
CREATE INDEX idx_gasto_data ON gastos(data_gasto);
CREATE INDEX idx_alocacao_inquilino ON alocacoes(inquilino_id);
CREATE INDEX idx_alocacao_quarto ON alocacoes(quarto_id);

-- Inserir alguns dados de exemplo
INSERT INTO quartos (numero_quarto, descricao, valor_aluguel, status) VALUES
('101', 'Quarto simples com banheiro privado', 800.00, 'disponivel'),
('102', 'Quarto com varanda', 900.00, 'disponivel'),
('201', 'Quarto espaçoso com janelas amplas', 950.00, 'ocupado'),
('202', 'Quarto com ar-condicionado', 1000.00, 'disponivel');

INSERT INTO funcionarios (nome, funcao, salario, cpf, telefone, data_inicio, ativo) VALUES
('João Silva', 'Zelador', 1500.00, '12345678901234', '11999999999', '2025-01-15', TRUE),
('Maria Santos', 'Limpeza', 1200.00, '12345678901235', '11999999998', '2025-02-01', TRUE);

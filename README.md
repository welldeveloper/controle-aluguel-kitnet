# 🏠 Sistema de Controle de Aluguel para Kitnet

Um sistema completo e profissional para gerenciar aluguéis, inquilinos, quartos, gastos e gerar relatórios financeiros de uma kitnet.

## 📋 Funcionalidades

✅ **Gerenciamento de Inquilinos**
- Cadastro com CPF, RG, telefone
- Data de entrada
- Unidades associadas

✅ **Controle de Quartos/Unidades**
- Cadastro de quartos disponíveis
- Valores de aluguel por quarto
- Status (ocupado/disponível)

✅ **Registro de Aluguéis**
- Controle de pagamentos
- Datas de vencimento
- Aviso de vencimento próximo

✅ **Dashboard Estatístico**
- Resumo financeiro
- Aluguéis a vencer
- Ocupação de quartos

✅ **Faturamento Mensal**
- Relatório de receitas
- Análise por período

✅ **Controle de Gastos**
- Manutenção
- Salários de funcionários
- Outras despesas

## 🛠️ Tecnologias

- **PHP 7.4+** - Backend
- **MySQL** - Banco de dados
- **Tailwind CSS** - Frontend (estilização moderna)
- **JavaScript** - Interatividade

## 📦 Instalação

### 1. Requisitos
- XAMPP instalado
- PHP 7.4+
- MySQL

### 2. Clonar o repositório
```bash
git clone https://github.com/welldeveloper/controle-aluguel-kitnet.git
```

### 3. Importar banco de dados
- Acesse `http://localhost/phpmyadmin`
- Crie um novo banco de dados chamado `kitnet_db`
- Importe o arquivo `database.sql`

### 4. Configurar conexão
- Edite `config/database.php` com suas credenciais MySQL

### 5. Acessar a aplicação
```
http://localhost/controle-aluguel-kitnet
```

## 📁 Estrutura de Pastas

```
controle-aluguel-kitnet/
├── config/
│   └── database.php          # Conexão com banco de dados
├── pages/
│   ├── inquilinos.php        # Gerenciar inquilinos
│   ├── quartos.php           # Gerenciar quartos
│   ├── alugueis.php          # Gerenciar aluguéis
│   ├── gastos.php            # Controlar gastos
│   └── relatorios.php        # Relatórios financeiros
├── assets/
│   ├── css/
│   │   └── style.css         # Estilos customizados
│   └── js/
│       └── script.js         # Scripts JavaScript
├── database.sql              # Script SQL
├── index.php                 # Dashboard principal
└── README.md                 # Este arquivo
```

## 🎯 Como Usar

### Cadastro de Inquilino
1. Clique em "Inquilinos" no menu
2. Preencha: Nome, CPF, RG, Telefone, Data de Entrada
3. Selecione o quarto
4. Clique em "Cadastrar"

### Registro de Aluguel
1. Vá em "Aluguéis"
2. Selecione o inquilino
3. Defina a data de vencimento
4. Registre o pagamento quando receber

### Ver Avisos de Vencimento
- O dashboard mostra automaticamente aluguéis vencendo em breve

## 📊 Dashboard

A página inicial exibe:
- Total de quartos ocupados
- Receita do mês
- Aluguéis a vencer
- Despesas do mês
- Gráficos de ocupação

## 👤 Autor

Desenvolvido para controle profissional de kitnets.

## 📄 Licença

MIT License - Use livremente!

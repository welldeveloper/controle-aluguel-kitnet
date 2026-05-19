<?php
/**
 * Página de Gerenciamento de Quartos
 * CRUD completo para cadastro, edição e exclusão de quartos
 */

require_once '../config/database.php';

// Verificar ação
$acao = $_GET['acao'] ?? 'listar';
$msg = '';
$tipo_msg = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['acao'] === 'adicionar') {
        $numero_quarto = $conexao->real_escape_string($_POST['numero_quarto']);
        $descricao = $conexao->real_escape_string($_POST['descricao']);
        $valor_aluguel = str_replace(',', '.', $_POST['valor_aluguel']);

        // Validar número de quarto único
        $sql_check = "SELECT id FROM quartos WHERE numero_quarto = '$numero_quarto'";
        $result_check = $conexao->query($sql_check);

        if ($result_check->num_rows > 0) {
            $msg = "❌ Quarto já cadastrado!";
            $tipo_msg = "error";
        } else {
            $sql = "INSERT INTO quartos (numero_quarto, descricao, valor_aluguel, status) 
                    VALUES ('$numero_quarto', '$descricao', $valor_aluguel, 'disponivel')";
            
            if ($conexao->query($sql)) {
                $msg = "✅ Quarto cadastrado com sucesso!";
                $tipo_msg = "success";
            } else {
                $msg = "❌ Erro ao cadastrar quarto!";
                $tipo_msg = "error";
            }
        }
    }

    if ($_POST['acao'] === 'editar') {
        $id = intval($_POST['id']);
        $descricao = $conexao->real_escape_string($_POST['descricao']);
        $valor_aluguel = str_replace(',', '.', $_POST['valor_aluguel']);

        $sql = "UPDATE quartos SET descricao = '$descricao', valor_aluguel = $valor_aluguel WHERE id = $id";
        
        if ($conexao->query($sql)) {
            $msg = "✅ Quarto atualizado com sucesso!";
            $tipo_msg = "success";
        } else {
            $msg = "❌ Erro ao atualizar!";
            $tipo_msg = "error";
        }
    }

    if ($_POST['acao'] === 'deletar') {
        $id = intval($_POST['id']);
        
        // Verificar se quarto está ocupado
        $sql_check = "SELECT COUNT(*) as total FROM alocacoes WHERE quarto_id = $id AND ativo = TRUE";
        $result_check = $conexao->query($sql_check);
        $count = $result_check->fetch_assoc()['total'];

        if ($count > 0) {
            $msg = "❌ Não é possível deletar um quarto ocupado!";
            $tipo_msg = "error";
        } else {
            $sql = "DELETE FROM quartos WHERE id = $id";
            
            if ($conexao->query($sql)) {
                $msg = "✅ Quarto removido com sucesso!";
                $tipo_msg = "success";
            } else {
                $msg = "❌ Erro ao remover!";
                $tipo_msg = "error";
            }
        }
    }
}

// Obter lista de quartos com informações do inquilino
$sql_quartos = "SELECT q.*, 
                       CASE WHEN q.status = 'ocupado' THEN i.nome ELSE NULL END as inquilino
                FROM quartos q
                LEFT JOIN alocacoes a ON q.id = a.quarto_id AND a.ativo = TRUE AND a.data_saida IS NULL
                LEFT JOIN inquilinos i ON a.inquilino_id = i.id
                ORDER BY q.numero_quarto";
$result_quartos = $conexao->query($sql_quartos);

// Se for editar, obter dados do quarto
$quarto_edit = null;
if ($acao === 'editar' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM quartos WHERE id = $id";
    $result = $conexao->query($sql);
    $quarto_edit = $result->fetch_assoc();
}

// Obter estatísticas
$sql_stats = "SELECT 
              COUNT(*) as total_quartos,
              SUM(CASE WHEN status = 'disponivel' THEN 1 ELSE 0 END) as quartos_livres,
              SUM(CASE WHEN status = 'ocupado' THEN 1 ELSE 0 END) as quartos_ocupados,
              AVG(valor_aluguel) as valor_medio
              FROM quartos";
$result_stats = $conexao->query($sql_stats);
$stats = $result_stats->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quartos - Sistema de Controle de Aluguel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <nav class="bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold">🚪 Gerenciar Quartos</h1>
                <a href="../index.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100">
                    ← Voltar
                </a>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Menu Lateral -->
        <aside class="w-64 bg-white shadow-lg">
            <nav class="p-6 space-y-2">
                <a href="../index.php" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    📊 Dashboard
                </a>
                <a href="inquilinos.php" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    👥 Inquilinos
                </a>
                <a href="quartos.php" class="block px-4 py-3 rounded-lg bg-blue-600 text-white font-semibold">
                    🚪 Quartos
                </a>
                <a href="alugueis.php" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    💰 Aluguéis
                </a>
                <a href="gastos.php" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    💸 Gastos
                </a>
                <a href="funcionarios.php" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    👔 Funcionários
                </a>
                <a href="relatorios.php" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
                    📈 Relatórios
                </a>
            </nav>
        </aside>

        <!-- Conteúdo Principal -->
        <main class="flex-1 p-8">
            <!-- Mensagem de Sucesso/Erro -->
            <?php if ($msg): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $tipo_msg === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <!-- Cards de Estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-600">
                    <p class="text-gray-500 text-sm font-semibold">TOTAL DE QUARTOS</p>
                    <p class="text-4xl font-bold text-blue-600 mt-2"><?php echo $stats['total_quartos']; ?></p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-600">
                    <p class="text-gray-500 text-sm font-semibold">QUARTOS LIVRES</p>
                    <p class="text-4xl font-bold text-green-600 mt-2"><?php echo $stats['quartos_livres']; ?></p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-600">
                    <p class="text-gray-500 text-sm font-semibold">QUARTOS OCUPADOS</p>
                    <p class="text-4xl font-bold text-red-600 mt-2"><?php echo $stats['quartos_ocupados']; ?></p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-600">
                    <p class="text-gray-500 text-sm font-semibold">VALOR MÉDIO</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">R$ <?php echo number_format($stats['valor_medio'], 2, ',', '.'); ?></p>
                </div>
            </div>

            <!-- Formulário de Cadastro/Edição -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <?php echo $acao === 'editar' ? '✏️ Editar Quarto' : '➕ Novo Quarto'; ?>
                </h2>

                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="hidden" name="acao" value="<?php echo $acao === 'editar' ? 'editar' : 'adicionar'; ?>">
                    
                    <?php if ($acao === 'editar'): ?>
                        <input type="hidden" name="id" value="<?php echo $quarto_edit['id']; ?>">
                    <?php endif; ?>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Número do Quarto *</label>
                        <input type="text" name="numero_quarto" required placeholder="Ex: 101, 201, A1" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                               <?php echo $acao === 'editar' ? 'disabled' : ''; ?>
                               value="<?php echo $acao === 'editar' ? htmlspecialchars($quarto_edit['numero_quarto']) : ''; ?>">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Valor do Aluguel *</label>
                        <input type="number" name="valor_aluguel" required placeholder="0.00" step="0.01" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                               value="<?php echo $acao === 'editar' ? htmlspecialchars($quarto_edit['valor_aluguel']) : ''; ?>">
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">Descrição</label>
                        <textarea name="descricao" rows="3" placeholder="Ex: Quarto com banheiro privado, varanda, janelas amplas..."
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"><?php echo $acao === 'editar' ? htmlspecialchars($quarto_edit['descricao']) : ''; ?></textarea>
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition">
                            <?php echo $acao === 'editar' ? '💾 Atualizar' : '➕ Cadastrar'; ?>
                        </button>
                    </div>
                </form>

                <?php if ($acao === 'editar'): ?>
                    <a href="quartos.php" class="mt-4 inline-block text-blue-600 hover:underline">← Voltar para lista</a>
                <?php endif; ?>
            </div>

            <!-- Lista de Quartos -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">🚪 Lista de Quartos</h2>

                <?php if ($result_quartos->num_rows > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php while ($quarto = $result_quartos->fetch_assoc()): ?>
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition">
                                <!-- Header do Card -->
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-800">Quarto <?php echo htmlspecialchars($quarto['numero_quarto']); ?></h3>
                                        <p class="text-sm text-gray-500">ID: <?php echo $quarto['id']; ?></p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        <?php echo $quarto['status'] === 'disponivel' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $quarto['status'] === 'disponivel' ? '✅ Disponível' : '❌ Ocupado'; ?>
                                    </span>
                                </div>

                                <!-- Informações -->
                                <div class="mb-4 space-y-2">
                                    <p class="text-gray-600 text-sm">
                                        <strong>Valor:</strong> R$ <?php echo number_format($quarto['valor_aluguel'], 2, ',', '.'); ?>
                                    </p>
                                    <p class="text-gray-600 text-sm">
                                        <strong>Descrição:</strong> <?php echo htmlspecialchars($quarto['descricao'] ?? 'Sem descrição'); ?>
                                    </p>
                                    <?php if ($quarto['inquilino']): ?>
                                        <p class="text-gray-600 text-sm">
                                            <strong>Inquilino:</strong> <?php echo htmlspecialchars($quarto['inquilino']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <!-- Botões de Ação -->
                                <div class="flex gap-2">
                                    <a href="quartos.php?acao=editar&id=<?php echo $quarto['id']; ?>" class="flex-1 text-center bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 text-sm font-semibold transition">
                                        ✏️ Editar
                                    </a>
                                    <form method="POST" class="flex-1" onsubmit="return confirm('Tem certeza que deseja remover?');">
                                        <input type="hidden" name="acao" value="deletar">
                                        <input type="hidden" name="id" value="<?php echo $quarto['id']; ?>">
                                        <button type="submit" class="w-full bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 text-sm font-semibold transition"
                                                <?php echo $quarto['status'] === 'ocupado' ? 'disabled title="Não pode deletar quartos ocupados"' : ''; ?>>
                                            🗑️ Remover
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-6">Nenhum quarto cadastrado ainda.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

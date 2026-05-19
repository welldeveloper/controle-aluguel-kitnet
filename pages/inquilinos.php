<?php
/**
 * Página de Gerenciamento de Inquilinos
 * CRUD completo para cadastro, edição e exclusão de inquilinos
 */

require_once '../config/database.php';

// Verificar ação
$acao = $_GET['acao'] ?? 'listar';
$msg = '';
$tipo_msg = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['acao'] === 'adicionar') {
        $nome = $conexao->real_escape_string($_POST['nome']);
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
        $rg = $conexao->real_escape_string($_POST['rg']);
        $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']);
        $data_entrada = $_POST['data_entrada'];
        $quarto_id = intval($_POST['quarto_id']);

        // Validar CPF único
        $sql_check = "SELECT id FROM inquilinos WHERE cpf = '$cpf'";
        $result_check = $conexao->query($sql_check);

        if ($result_check->num_rows > 0) {
            $msg = "❌ CPF já cadastrado!";
            $tipo_msg = "error";
        } else {
            // Inserir inquilino
            $sql = "INSERT INTO inquilinos (nome, cpf, rg, telefone, data_entrada) 
                    VALUES ('$nome', '$cpf', '$rg', '$telefone', '$data_entrada')";
            
            if ($conexao->query($sql)) {
                $inquilino_id = $conexao->insert_id;

                // Adicionar alocação
                $sql_alocacao = "INSERT INTO alocacoes (inquilino_id, quarto_id, data_inicio) 
                                 VALUES ($inquilino_id, $quarto_id, '$data_entrada')";
                
                // Atualizar status do quarto
                $sql_quarto = "UPDATE quartos SET status = 'ocupado' WHERE id = $quarto_id";

                if ($conexao->query($sql_alocacao) && $conexao->query($sql_quarto)) {
                    $msg = "✅ Inquilino cadastrado com sucesso!";
                    $tipo_msg = "success";
                } else {
                    $msg = "❌ Erro ao alocar quarto!";
                    $tipo_msg = "error";
                }
            } else {
                $msg = "❌ Erro ao cadastrar inquilino!";
                $tipo_msg = "error";
            }
        }
    }

    if ($_POST['acao'] === 'editar') {
        $id = intval($_POST['id']);
        $nome = $conexao->real_escape_string($_POST['nome']);
        $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']);

        $sql = "UPDATE inquilinos SET nome = '$nome', telefone = '$telefone' WHERE id = $id";
        
        if ($conexao->query($sql)) {
            $msg = "✅ Inquilino atualizado com sucesso!";
            $tipo_msg = "success";
        } else {
            $msg = "❌ Erro ao atualizar!";
            $tipo_msg = "error";
        }
    }

    if ($_POST['acao'] === 'deletar') {
        $id = intval($_POST['id']);
        
        // Obter quarto do inquilino
        $sql_get = "SELECT quarto_id FROM alocacoes WHERE inquilino_id = $id AND ativo = TRUE LIMIT 1";
        $result_get = $conexao->query($sql_get);
        $row = $result_get->fetch_assoc();
        
        if ($row) {
            // Desativar alocação
            $sql_alocacao = "UPDATE alocacoes SET ativo = FALSE, data_saida = NOW() WHERE inquilino_id = $id";
            
            // Liberar quarto
            $sql_quarto = "UPDATE quartos SET status = 'disponivel' WHERE id = " . $row['quarto_id'];
            
            // Desativar inquilino
            $sql = "UPDATE inquilinos SET ativo = FALSE WHERE id = $id";
            
            if ($conexao->query($sql_alocacao) && $conexao->query($sql_quarto) && $conexao->query($sql)) {
                $msg = "✅ Inquilino removido com sucesso!";
                $tipo_msg = "success";
            }
        }
    }
}

// Obter lista de inquilinos ativos
$sql_inquilinos = "SELECT i.*, GROUP_CONCAT(q.numero_quarto SEPARATOR ', ') as quartos
                   FROM inquilinos i
                   LEFT JOIN alocacoes a ON i.id = a.inquilino_id AND a.ativo = TRUE AND a.data_saida IS NULL
                   LEFT JOIN quartos q ON a.quarto_id = q.id
                   WHERE i.ativo = TRUE
                   GROUP BY i.id
                   ORDER BY i.data_entrada DESC";
$result_inquilinos = $conexao->query($sql_inquilinos);

// Obter quartos disponíveis
$sql_quartos = "SELECT id, numero_quarto, valor_aluguel FROM quartos WHERE status = 'disponivel' ORDER BY numero_quarto";
$result_quartos = $conexao->query($sql_quartos);

// Se for editar, obter dados do inquilino
$inquilino_edit = null;
if ($acao === 'editar' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM inquilinos WHERE id = $id";
    $result = $conexao->query($sql);
    $inquilino_edit = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquilinos - Sistema de Controle de Aluguel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <nav class="bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold">👥 Gerenciar Inquilinos</h1>
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
                <a href="inquilinos.php" class="block px-4 py-3 rounded-lg bg-blue-600 text-white font-semibold">
                    👥 Inquilinos
                </a>
                <a href="quartos.php" class="block px-4 py-3 rounded-lg hover:bg-gray-100">
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

            <!-- Formulário de Cadastro/Edição -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <?php echo $acao === 'editar' ? '✏️ Editar Inquilino' : '➕ Novo Inquilino'; ?>
                </h2>

                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="hidden" name="acao" value="<?php echo $acao === 'editar' ? 'editar' : 'adicionar'; ?>">
                    
                    <?php if ($acao === 'editar'): ?>
                        <input type="hidden" name="id" value="<?php echo $inquilino_edit['id']; ?>">
                    <?php endif; ?>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Nome *</label>
                        <input type="text" name="nome" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" 
                               value="<?php echo $acao === 'editar' ? htmlspecialchars($inquilino_edit['nome']) : ''; ?>">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">CPF *</label>
                        <input type="text" name="cpf" required placeholder="000.000.000-00" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                               <?php echo $acao === 'editar' ? 'disabled' : ''; ?>
                               value="<?php echo $acao === 'editar' ? htmlspecialchars($inquilino_edit['cpf']) : ''; ?>">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">RG *</label>
                        <input type="text" name="rg" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                               <?php echo $acao === 'editar' ? 'disabled' : ''; ?>
                               value="<?php echo $acao === 'editar' ? htmlspecialchars($inquilino_edit['rg']) : ''; ?>">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Telefone *</label>
                        <input type="tel" name="telefone" required placeholder="(11) 99999-9999" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                               value="<?php echo $acao === 'editar' ? htmlspecialchars($inquilino_edit['telefone']) : ''; ?>">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Data de Entrada *</label>
                        <input type="date" name="data_entrada" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                               <?php echo $acao === 'editar' ? 'disabled' : ''; ?>
                               value="<?php echo $acao === 'editar' ? htmlspecialchars($inquilino_edit['data_entrada']) : date('Y-m-d'); ?>">
                    </div>

                    <?php if ($acao !== 'editar'): ?>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Quarto *</label>
                            <select name="quarto_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                                <option value="">Selecione um quarto</option>
                                <?php while ($quarto = $result_quartos->fetch_assoc()): ?>
                                    <option value="<?php echo $quarto['id']; ?>">
                                        Quarto <?php echo $quarto['numero_quarto']; ?> - R$ <?php echo number_format($quarto['valor_aluguel'], 2, ',', '.'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="col-span-1 md:col-span-2">
                        <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition">
                            <?php echo $acao === 'editar' ? '💾 Atualizar' : '➕ Cadastrar'; ?>
                        </button>
                    </div>
                </form>

                <?php if ($acao === 'editar'): ?>
                    <a href="inquilinos.php" class="mt-4 inline-block text-blue-600 hover:underline">← Voltar para lista</a>
                <?php endif; ?>
            </div>

            <!-- Lista de Inquilinos -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">📋 Lista de Inquilinos</h2>

                <?php if ($result_inquilinos->num_rows > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-100 border-b-2 border-gray-300">
                                <tr>
                                    <th class="px-4 py-3 text-gray-700 font-semibold">Nome</th>
                                    <th class="px-4 py-3 text-gray-700 font-semibold">CPF</th>
                                    <th class="px-4 py-3 text-gray-700 font-semibold">Telefone</th>
                                    <th class="px-4 py-3 text-gray-700 font-semibold">Quarto</th>
                                    <th class="px-4 py-3 text-gray-700 font-semibold">Data Entrada</th>
                                    <th class="px-4 py-3 text-gray-700 font-semibold">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($inquilino = $result_inquilinos->fetch_assoc()): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($inquilino['nome']); ?></td>
                                        <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($inquilino['cpf']); ?></td>
                                        <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($inquilino['telefone']); ?></td>
                                        <td class="px-4 py-3 text-gray-800 font-semibold">
                                            <?php echo $inquilino['quartos'] ? htmlspecialchars($inquilino['quartos']) : '---'; ?>
                                        </td>
                                        <td class="px-4 py-3 text-gray-800"><?php echo date('d/m/Y', strtotime($inquilino['data_entrada'])); ?></td>
                                        <td class="px-4 py-3">
                                            <a href="inquilinos.php?acao=editar&id=<?php echo $inquilino['id']; ?>" class="text-blue-600 hover:underline mr-3">✏️ Editar</a>
                                            <form method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja remover?');">
                                                <input type="hidden" name="acao" value="deletar">
                                                <input type="hidden" name="id" value="<?php echo $inquilino['id']; ?>">
                                                <button type="submit" class="text-red-600 hover:underline">🗑️ Remover</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-6">Nenhum inquilino cadastrado ainda.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

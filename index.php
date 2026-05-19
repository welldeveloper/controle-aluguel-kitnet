<?php
/**
 * Dashboard Principal - Sistema de Controle de Aluguel
 * Exibe estatísticas, aluguéis vencendo e resumo financeiro
 */

require_once 'config/database.php';

// Obter estatísticas do banco de dados

// 1. Total de quartos ocupados
$sql_ocupados = "SELECT COUNT(*) as total FROM alocacoes WHERE ativo = TRUE AND data_saida IS NULL";
$result_ocupados = $conexao->query($sql_ocupados);
$quartos_ocupados = $result_ocupados->fetch_assoc()['total'];

// 2. Total de quartos disponíveis
$sql_disponivel = "SELECT COUNT(*) as total FROM quartos WHERE status = 'disponivel'";
$result_disponivel = $conexao->query($sql_disponivel);
$quartos_disponivel = $result_disponivel->fetch_assoc()['total'];

// 3. Total de quartos
$sql_total_quartos = "SELECT COUNT(*) as total FROM quartos";
$result_total = $conexao->query($sql_total_quartos);
$total_quartos = $result_total->fetch_assoc()['total'];

// 4. Receita do mês (aluguéis pagos)
$sql_receita = "SELECT SUM(valor_aluguel) as total FROM alugueis 
                WHERE MONTH(data_pagamento) = MONTH(NOW()) 
                AND YEAR(data_pagamento) = YEAR(NOW()) 
                AND status = 'pago'";
$result_receita = $conexao->query($sql_receita);
$receita_mes = $result_receita->fetch_assoc()['total'] ?? 0;

// 5. Aluguéis pendentes (a receber)
$sql_pendentes = "SELECT SUM(valor_aluguel) as total FROM alugueis 
                  WHERE status IN ('pendente', 'atrasado') 
                  AND MONTH(mes_referencia) = MONTH(NOW()) 
                  AND YEAR(mes_referencia) = YEAR(NOW())";
$result_pendentes = $conexao->query($sql_pendentes);
$pendentes_mes = $result_pendentes->fetch_assoc()['total'] ?? 0;

// 6. Gastos do mês
$sql_gastos = "SELECT SUM(valor) as total FROM gastos 
               WHERE MONTH(data_gasto) = MONTH(NOW()) 
               AND YEAR(data_gasto) = YEAR(NOW())";
$result_gastos = $conexao->query($sql_gastos);
$gastos_mes = $result_gastos->fetch_assoc()['total'] ?? 0;

// 7. Aluguéis vencendo nos próximos 7 dias
$sql_vencendo = "SELECT a.mes_referencia, a.valor_aluguel, a.data_vencimento, 
                        i.nome, q.numero_quarto
                 FROM alugueis a
                 JOIN alocacoes al ON a.alocacao_id = al.id
                 JOIN inquilinos i ON al.inquilino_id = i.id
                 JOIN quartos q ON al.quarto_id = q.id
                 WHERE a.status IN ('pendente', 'atrasado')
                 AND a.data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                 ORDER BY a.data_vencimento ASC
                 LIMIT 10";
$result_vencendo = $conexao->query($sql_vencendo);

// 8. Aluguéis atrasados
$sql_atrasados = "SELECT COUNT(*) as total FROM alugueis 
                  WHERE status = 'atrasado' AND data_vencimento < CURDATE()";
$result_atrasados = $conexao->query($sql_atrasados);
$alugueis_atrasados = $result_atrasados->fetch_assoc()['total'];

// 9. Lucro do mês (receita - gastos)
$lucro_mes = $receita_mes - $gastos_mes;

// 10. Taxa de ocupação
$taxa_ocupacao = $total_quartos > 0 ? round(($quartos_ocupados / $total_quartos) * 100) : 0;

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Controle de Aluguel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">
    <!-- Header / Navbar -->
    <nav class="bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">🏠 Controle Aluguel Kitnet</h1>
                    <p class="text-blue-100 text-sm">Sistema de Gerenciamento</p>
                </div>
                <div class="text-right">
                    <p class="text-sm">Bem-vindo!</p>
                    <p class="text-xs text-blue-100"><?php echo date('d/m/Y H:i'); ?></p>
                </div>
            </div>
        </div>
    </nav>

    <!-- Menu Lateral -->
    <div class="flex">
        <aside class="w-64 bg-white shadow-lg">
            <nav class="p-6 space-y-2">
                <a href="index.php" class="block px-4 py-3 rounded-lg bg-blue-600 text-white font-semibold">
                    📊 Dashboard
                </a>
                <a href="pages/inquilinos.php" class="block px-4 py-3 rounded-lg hover:bg-gray-100 transition">
                    👥 Inquilinos
                </a>
                <a href="pages/quartos.php" class="block px-4 py-3 rounded-lg hover:bg-gray-100 transition">
                    🚪 Quartos
                </a>
                <a href="pages/alugueis.php" class="block px-4 py-3 rounded-lg hover:bg-gray-100 transition">
                    💰 Aluguéis
                </a>
                <a href="pages/gastos.php" class="block px-4 py-3 rounded-lg hover:bg-gray-100 transition">
                    💸 Gastos
                </a>
                <a href="pages/funcionarios.php" class="block px-4 py-3 rounded-lg hover:bg-gray-100 transition">
                    👔 Funcionários
                </a>
                <a href="pages/relatorios.php" class="block px-4 py-3 rounded-lg hover:bg-gray-100 transition">
                    📈 Relatórios
                </a>
            </nav>
        </aside>

        <!-- Conteúdo Principal -->
        <main class="flex-1 p-8">
            <!-- Cards de Estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Card: Ocupação -->
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-600">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 text-sm font-semibold">OCUPAÇÃO</p>
                            <p class="text-4xl font-bold text-blue-600 mt-2"><?php echo $taxa_ocupacao; ?>%</p>
                            <p class="text-gray-400 text-xs mt-2"><?php echo $quartos_ocupados; ?>/<?php echo $total_quartos; ?> quartos</p>
                        </div>
                        <span class="text-4xl">🏢</span>
                    </div>
                </div>

                <!-- Card: Receita do Mês -->
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-600">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 text-sm font-semibold">RECEITA MÊS</p>
                            <p class="text-4xl font-bold text-green-600 mt-2">R$ <?php echo number_format($receita_mes, 2, ',', '.'); ?></p>
                            <p class="text-gray-400 text-xs mt-2">Aluguéis recebidos</p>
                        </div>
                        <span class="text-4xl">💵</span>
                    </div>
                </div>

                <!-- Card: Pendências -->
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-600">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 text-sm font-semibold">A RECEBER</p>
                            <p class="text-4xl font-bold text-yellow-600 mt-2">R$ <?php echo number_format($pendentes_mes, 2, ',', '.'); ?></p>
                            <p class="text-gray-400 text-xs mt-2">Aluguéis pendentes</p>
                        </div>
                        <span class="text-4xl">⏰</span>
                    </div>
                </div>

                <!-- Card: Gastos do Mês -->
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-600">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 text-sm font-semibold">GASTOS MÊS</p>
                            <p class="text-4xl font-bold text-red-600 mt-2">R$ <?php echo number_format($gastos_mes, 2, ',', '.'); ?></p>
                            <p class="text-gray-400 text-xs mt-2">Despesas do mês</p>
                        </div>
                        <span class="text-4xl">📉</span>
                    </div>
                </div>
            </div>

            <!-- Segundo Bloco de Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Card: Lucro -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                    <p class="text-sm font-semibold opacity-90">LUCRO LÍQUIDO</p>
                    <p class="text-4xl font-bold mt-2">R$ <?php echo number_format($lucro_mes, 2, ',', '.'); ?></p>
                    <p class="text-xs mt-2 opacity-75">Receita - Gastos</p>
                </div>

                <!-- Card: Quartos Disponíveis -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                    <p class="text-sm font-semibold opacity-90">QUARTOS DISPONÍVEIS</p>
                    <p class="text-4xl font-bold mt-2"><?php echo $quartos_disponivel; ?></p>
                    <p class="text-xs mt-2 opacity-75">Prontos para alugar</p>
                </div>

                <!-- Card: Atrasados -->
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
                    <p class="text-sm font-semibold opacity-90">ALUGUÉIS ATRASADOS</p>
                    <p class="text-4xl font-bold mt-2"><?php echo $alugueis_atrasados; ?></p>
                    <p class="text-xs mt-2 opacity-75">Pendentes de cobrança</p>
                </div>
            </div>

            <!-- Seção: Aluguéis Vencendo -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">⚠️ Aluguéis Vencendo nos Próximos 7 Dias</h2>
                
                <?php if ($result_vencendo->num_rows > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-100 border-b-2 border-gray-300">
                                <tr>
                                    <th class="px-4 py-3 text-gray-700 font-semibold">Inquilino</th>
                                    <th class="px-4 py-3 text-gray-700 font-semibold">Quarto</th>
                                    <th class="px-4 py-3 text-gray-700 font-semibold">Valor</th>
                                    <th class="px-4 py-3 text-gray-700 font-semibold">Vencimento</th>
                                    <th class="px-4 py-3 text-gray-700 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result_vencendo->fetch_assoc()): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($row['nome']); ?></td>
                                        <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($row['numero_quarto']); ?></td>
                                        <td class="px-4 py-3 text-gray-800 font-semibold">R$ <?php echo number_format($row['valor_aluguel'], 2, ',', '.'); ?></td>
                                        <td class="px-4 py-3 text-gray-800"><?php echo date('d/m/Y', strtotime($row['data_vencimento'])); ?></td>
                                        <td class="px-4 py-3">
                                            <span class="px-3 py-1 rounded-full text-sm font-semibold 
                                                <?php echo $row['data_vencimento'] < date('Y-m-d') ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                <?php echo $row['data_vencimento'] < date('Y-m-d') ? 'ATRASADO' : 'VENCENDO'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-6">✅ Nenhum aluguel vencendo nos próximos 7 dias!</p>
                <?php endif; ?>
            </div>

            <!-- Gráfico de Receitas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Receita vs Gastos (Últimos 6 Meses)</h3>
                    <canvas id="chartReceitas"></canvas>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Taxa de Ocupação por Quarto</h3>
                    <canvas id="chartOcupacao"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/script.js"></script>
    <script>
        // Gráfico de Receita vs Gastos
        const ctxReceitas = document.getElementById('chartReceitas').getContext('2d');
        new Chart(ctxReceitas, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                datasets: [
                    {
                        label: 'Receita',
                        data: [5000, 5500, 6000, 5800, 6200, 6500],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Gastos',
                        data: [1500, 1600, 1700, 1650, 1800, 1900],
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Gráfico de Ocupação
        const ctxOcupacao = document.getElementById('chartOcupacao').getContext('2d');
        new Chart(ctxOcupacao, {
            type: 'doughnut',
            data: {
                labels: ['Ocupado', 'Disponível'],
                datasets: [{
                    data: [<?php echo $quartos_ocupados; ?>, <?php echo $quartos_disponivel; ?>],
                    backgroundColor: ['#3b82f6', '#d1d5db'],
                    borderColor: ['#1e40af', '#9ca3af']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>

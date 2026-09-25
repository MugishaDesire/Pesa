<?php
// dashboard.php
require "config.php";
require "functions.php";
requireLogin();

$userId = $_SESSION["user_id"];

$selectedMonth = (isset($_GET["month"]) && preg_match('/^\d{4}-\d{2}$/', $_GET["month"]))
    ? $_GET["month"]
    : date("Y-m");

$totalSpent = getMonthlyTotalSpent($conn, $userId, $selectedMonth);
$budgets = getBudgets($conn, $userId, $selectedMonth);
$totalBudget = array_sum($budgets);

$monthLabel = date("F Y", strtotime($selectedMonth . "-01"));
$percentUsed = $totalBudget > 0 ? min(100, round(($totalSpent / $totalBudget) * 100)) : 0;
$overBudget = $totalBudget > 0 && $totalSpent > $totalBudget;
$barClass = $overBudget ? 'progress-fill--over' : ($percentUsed >= 80 ? 'progress-fill--warn' : 'progress-fill--safe');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard · Pesa</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* dashboard.php - page-specific styling */

        .dashboard-header {
            text-align: center;
            margin-bottom: var(--space-5);
        }

        .dashboard-header h2 {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .dashboard-header .subtitle {
            font-size: 13px;
            color: var(--text-muted);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-3);
            margin-bottom: var(--space-5);
        }

        .stat-card {
            background: var(--surface-muted);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: var(--space-4);
            text-align: center;
        }

        .stat-card .label {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 6px;
        }

        .stat-card .value {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: -0.01em;
            font-variant-numeric: tabular-nums;
        }

        .stat-card.no-budget {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--surface);
            border-style: dashed;
            border-color: var(--border-strong);
            color: var(--text-muted);
            font-size: 13px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-3);
        }

        .actions-grid .btn-primary {
            grid-column: 1 / -1;
        }

        @media (max-width: 640px) {
            .stats-grid,
            .actions-grid {
                grid-template-columns: 1fr;
            }

            .actions-grid .btn-primary {
                grid-column: auto;
            }
        }
    </style>
</head>
<body class="site-page">
    <?php include "nav.php"; ?>

    <main class="page-shell">
        <div class="card">
            <div class="dashboard-header">
                <h2>Welcome back, <?php echo htmlspecialchars($_SESSION["username"]); ?></h2>
                <p class="subtitle">Your spending overview for <?php echo htmlspecialchars($monthLabel); ?></p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="label">Spent this month</div>
                    <div class="value"><?php echo number_format($totalSpent, 2); ?></div>
                </div>

                <?php if ($totalBudget > 0): ?>
                    <div class="stat-card">
                        <div class="label">Total budget</div>
                        <div class="value"><?php echo number_format($totalBudget, 2); ?></div>
                    </div>
                <?php else: ?>
                    <div class="stat-card no-budget">
                        No budgets set for <?php echo htmlspecialchars($monthLabel); ?> yet.
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($totalBudget > 0): ?>
                <div class="mb-5">
                    <div class="progress-meta">
                        <span class="muted">Budget used</span>
                        <span class="tabular"><strong><?php echo $percentUsed; ?>%</strong></span>
                    </div>
                    <div class="progress">
                        <div class="progress-fill <?php echo $barClass; ?>" style="width: <?php echo $percentUsed; ?>%;"></div>
                    </div>
                    <?php if ($overBudget): ?>
                        <div class="budget-note budget-note--over">You're over budget this month.</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="actions-grid">
                <a class="btn btn-primary" href="AddExpense.php">+ Add Expense</a>
                <a class="btn btn-secondary" href="expenses.php?month=<?php echo urlencode($selectedMonth); ?>">View Expenses</a>
                <a class="btn btn-secondary" href="budget.php?month=<?php echo urlencode($selectedMonth); ?>">Set Budgets</a>
                <a class="btn btn-secondary" href="reports.php?month=<?php echo urlencode($selectedMonth); ?>">View Reports</a>
            </div>

            <hr class="divider">

            <form method="post" action="logout.php">
                <button type="submit" class="btn btn-danger btn-block">Log Out</button>
            </form>
        </div>
    </main>
</body>
</html>
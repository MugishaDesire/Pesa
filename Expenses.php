<?php
// expenses.php
require "config.php";
require "functions.php";
requireLogin();

$userId = $_SESSION["user_id"];

$selectedMonth = (isset($_GET["month"]) && preg_match('/^\d{4}-\d{2}$/', $_GET["month"]))
    ? $_GET["month"]
    : date("Y-m");

if (isset($_GET["delete"])) {
    $deleteId = (int) $_GET["delete"];

    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $deleteId, $userId);
    $stmt->execute();
    $stmt->close();

    header("Location: expenses.php?month=" . urlencode($selectedMonth));
    exit();
}

$stmt = $conn->prepare(
    "SELECT id, category, amount, description, expense_date
     FROM expenses
     WHERE user_id = ?
       AND DATE_FORMAT(expense_date, '%Y-%m') = ?
     ORDER BY expense_date DESC, id DESC"
);
$stmt->bind_param("is", $userId, $selectedMonth);
$stmt->execute();
$expenses = $stmt->get_result();

$totalSpent = getMonthlyTotalSpent($conn, $userId, $selectedMonth);
$budgets = getBudgets($conn, $userId, $selectedMonth);
$totalBudget = array_sum($budgets);
$percentUsed = $totalBudget > 0 ? min(100, round(($totalSpent / $totalBudget) * 100)) : 0;
$overBudget = $totalBudget > 0 && $totalSpent > $totalBudget;
$barClass = $overBudget ? 'progress-fill--over' : ($percentUsed >= 80 ? 'progress-fill--warn' : 'progress-fill--safe');

$monthLabel = date("F Y", strtotime($selectedMonth . "-01"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Expenses · Pesa</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* expenses.php - inline copy of shared component styles.
           Kept in style.css too (other pages depend on them); duplicated
           here so this page still renders correctly on its own. */

        .filter-bar {
            display: flex;
            align-items: end;
            gap: var(--space-3);
            padding: var(--space-3) var(--space-4);
            background: var(--surface-muted);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: var(--space-5);
        }

        .filter-bar .field {
            margin-bottom: 0;
            flex: 1;
        }

        .filter-bar .btn {
            flex-shrink: 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .data-table th,
        .data-table td {
            text-align: left;
            padding: 12px 10px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .data-table th {
            color: var(--text-muted);
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: var(--surface-muted);
        }

        .data-table th:first-child { border-top-left-radius: var(--radius-sm); }
        .data-table th:last-child  { border-top-right-radius: var(--radius-sm); }

        .data-table tbody tr:hover td {
            background: var(--surface-muted);
        }

        .data-table .num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .data-table .empty-row td {
            text-align: center;
            color: var(--text-muted);
            padding: var(--space-6);
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--surface);
        }

        .table-wrap .data-table th,
        .table-wrap .data-table td {
            border-bottom: 1px solid var(--border);
        }

        .table-wrap .data-table tr:last-child td {
            border-bottom: none;
        }

        .empty-state {
            text-align: center;
            padding: var(--space-6) var(--space-4);
            color: var(--text-muted);
            font-size: 14px;
        }

        .empty-state .btn {
            margin-top: var(--space-3);
        }

        .inline-links {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-3);
            justify-content: center;
            font-size: 14px;
            margin-top: var(--space-5);
            padding-top: var(--space-5);
            border-top: 1px solid var(--border);
        }

        .inline-links a {
            color: var(--text-muted);
            font-weight: 500;
        }

        .inline-links a:hover {
            color: var(--primary);
            text-decoration: none;
        }
    </style>
</head>
<body class="site-page">
    <?php include "nav.php"; ?>

    <main class="page-shell page-shell--wide">
        <div class="card">
            <h2 class="card-title">My Expenses</h2>
            <p class="card-subtitle">Everything you recorded in <?php echo htmlspecialchars($monthLabel); ?>.</p>

            <form method="get" action="expenses.php" class="filter-bar">
                <div class="field">
                    <label for="month">Viewing month</label>
                    <input type="month" id="month" name="month" value="<?php echo htmlspecialchars($selectedMonth); ?>">
                </div>
                <button type="submit" class="btn btn-secondary">Go</button>
            </form>

            <!-- Spent vs budget summary -->
            <div class="mb-5">
                <div class="progress-meta">
                    <span>Spent: <strong class="tabular"><?php echo number_format($totalSpent, 2); ?></strong></span>
                    <?php if ($totalBudget > 0): ?>
                        <span class="muted">Budget: <strong class="tabular"><?php echo number_format($totalBudget, 2); ?></strong></span>
                    <?php endif; ?>
                </div>

                <?php if ($totalBudget > 0): ?>
                    <div class="progress">
                        <div class="progress-fill <?php echo $barClass; ?>" style="width: <?php echo $percentUsed; ?>%;"></div>
                    </div>
                    <?php if ($overBudget): ?>
                        <div class="budget-note budget-note--over">Over budget by <?php echo number_format($totalSpent - $totalBudget, 2); ?>.</div>
                    <?php else: ?>
                        <div class="budget-note"><?php echo $percentUsed; ?>% of budget used</div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="budget-note">No budget set for this month. <a href="budget.php?month=<?php echo urlencode($selectedMonth); ?>">Set one</a>.</div>
                <?php endif; ?>
            </div>

            <?php if ($expenses->num_rows === 0): ?>
                <div class="empty-state">
                    <p>No expenses recorded for <?php echo htmlspecialchars($monthLabel); ?>.</p>
                    <a class="btn btn-primary" href="AddExpense.php">+ Add your first expense</a>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th class="num">Amount</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $expenses->fetch_assoc()): ?>
                                <tr>
                                    <td class="tabular"><?php echo htmlspecialchars($row["expense_date"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["category"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["description"]); ?></td>
                                    <td class="num tabular"><?php echo number_format($row["amount"], 2); ?></td>
                                    <td class="num">
                                        <a class="btn btn-danger btn-sm"
                                           href="expenses.php?delete=<?php echo $row['id']; ?>&month=<?php echo urlencode($selectedMonth); ?>"
                                           onclick="return confirm('Delete this expense?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="inline-links">
                <a href="AddExpense.php">+ Add Expense</a>
                <a href="reports.php?month=<?php echo urlencode($selectedMonth); ?>">View Reports</a>
                <a href="dashboard.php?month=<?php echo urlencode($selectedMonth); ?>">&larr; Back to Dashboard</a>
            </div>
        </div>
    </main>
</body>
</html>
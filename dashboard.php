<?php
// dashboard.php
require "config.php";
require "functions.php";
requireLogin(); // same gatekeeper check, now reused from functions.php

$userId = $_SESSION["user_id"];

// Selected month for the dashboard stats, format "Y-m" (e.g. "2026-09").
// Falls back to the current month if none is selected or the value looks invalid.
$selectedMonth = (isset($_GET["month"]) && preg_match('/^\d{4}-\d{2}$/', $_GET["month"]))
    ? $_GET["month"]
    : date("Y-m");

$totalSpent = getMonthlyTotalSpent($conn, $userId, $selectedMonth);
$budgets = getBudgets($conn, $userId, $selectedMonth);
$totalBudget = array_sum($budgets);

// Friendly label like "September 2026" for the heading
$monthLabel = date("F Y", strtotime($selectedMonth . "-01"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-box">
        <div class="dashboard-header">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
            <p class="subtitle">Here's your spending overview for <?php echo htmlspecialchars($monthLabel); ?></p>
        </div>

        <form method="get" action="dashboard.php" style="display:flex; gap:8px; align-items:end; margin-bottom:20px;">
            <div style="flex:1;">
                <label for="month" style="display:block; font-size:14px; color:var(--text-muted); margin-bottom:4px;">Viewing month</label>
                <input type="month" id="month" name="month" value="<?php echo htmlspecialchars($selectedMonth); ?>"
                       style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
            </div>
            <button type="submit" class="btn btn-secondary" style="width:auto; padding:10px 16px;">Go</button>
        </form>

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
                <div class="stat-card no-budget">No budgets set yet.</div>
            <?php endif; ?>
        </div>

        <div class="actions-grid">
            <a class="btn btn-primary" href="AddExpense.php">Add Expense</a>
            <a class="btn btn-primary" href="expenses.php">View Expenses</a>
            <a class="btn btn-primary" href="budget.php">Set Budgets</a>
            <a class="btn btn-primary" href="Report.php">View Reports</a>
        </div>

        <hr class="divider">

        <form method="post" action="Login.php">
            <button type="submit" class="btn btn-danger">Log Out</button>
        </form>
    </div>
</body>
</html>
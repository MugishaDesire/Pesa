<?php
// budget.php
require "config.php";
require "functions.php";
requireLogin();

$userId = $_SESSION["user_id"];
$categories = getCategories();

$selectedMonth = (isset($_REQUEST["month"]) && preg_match('/^\d{4}-\d{2}$/', $_REQUEST["month"]))
    ? $_REQUEST["month"]
    : date("Y-m");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($categories as $cat) {
        $fieldName = "budget_" . $cat;
        $value = trim($_POST[$fieldName] ?? "");

        if ($value !== "" && is_numeric($value) && $value >= 0) {
            $stmt = $conn->prepare(
                "INSERT INTO budgets (user_id, category, budget_month, monthly_limit)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE monthly_limit = VALUES(monthly_limit)"
            );
            $stmt->bind_param("issd", $userId, $cat, $selectedMonth, $value);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: budget.php?month=" . urlencode($selectedMonth) . "&saved=1");
    exit();
}

$currentBudgets = getBudgets($conn, $userId, $selectedMonth);
$hasBudgets = !empty($currentBudgets);

$editMode = !$hasBudgets || (isset($_GET["edit"]) && $_GET["edit"] === "1");
$justSaved = isset($_GET["saved"]) && $_GET["saved"] === "1";

$monthLabel = date("F Y", strtotime($selectedMonth . "-01"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set Budgets · Pesa</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* budget.php - inline copy of shared component styles.
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

    <main class="page-shell">
        <div class="card">
            <h2 class="card-title">Monthly Budgets</h2>
            <p class="card-subtitle">
                Set a spending limit per category for <strong><?php echo htmlspecialchars($monthLabel); ?></strong>.
            </p>

            <?php if ($justSaved): ?>
                <div class="alert alert-success">
                    <span class="alert-icon">✓</span>
                    <span>Budgets updated for <?php echo htmlspecialchars($monthLabel); ?>.</span>
                </div>
            <?php endif; ?>

            <form method="get" action="budget.php" class="filter-bar">
                <div class="field">
                    <label for="month">Editing month</label>
                    <input type="month" id="month" name="month" value="<?php echo htmlspecialchars($selectedMonth); ?>">
                </div>
                <button type="submit" class="btn btn-secondary">Go</button>
            </form>

            <?php if ($editMode): ?>
                <form method="post" action="budget.php">
                    <input type="hidden" name="month" value="<?php echo htmlspecialchars($selectedMonth); ?>">

                    <?php foreach ($categories as $cat): ?>
                        <div class="field">
                            <label for="budget_<?php echo $cat; ?>"><?php echo htmlspecialchars($cat); ?></label>
                            <input
                                type="number" step="0.01" min="0"
                                id="budget_<?php echo $cat; ?>"
                                name="budget_<?php echo $cat; ?>"
                                value="<?php echo isset($currentBudgets[$cat]) ? htmlspecialchars($currentBudgets[$cat]) : ''; ?>"
                                placeholder="0.00">
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" class="btn btn-primary btn-block">Save Budgets</button>

                    <?php if ($hasBudgets): ?>
                        <div class="inline-links">
                            <a href="budget.php?month=<?php echo urlencode($selectedMonth); ?>">Cancel</a>
                        </div>
                    <?php endif; ?>
                </form>
            <?php else: ?>
                <div class="table-wrap mb-4">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="num">Monthly Limit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($currentBudgets as $cat => $limit): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cat); ?></td>
                                    <td class="num tabular"><?php echo number_format($limit, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <a class="btn btn-primary btn-block"
                   href="budget.php?month=<?php echo urlencode($selectedMonth); ?>&edit=1">Edit Budget</a>
            <?php endif; ?>

            <div class="inline-links">
                <a href="expenses.php?month=<?php echo urlencode($selectedMonth); ?>">View Expenses</a>
                <a href="reports.php?month=<?php echo urlencode($selectedMonth); ?>">View Reports</a>
                <a href="dashboard.php?month=<?php echo urlencode($selectedMonth); ?>">&larr; Back to Dashboard</a>
            </div>
        </div>
    </main>
</body>
</html>
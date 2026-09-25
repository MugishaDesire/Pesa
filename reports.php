<?php
// reports.php
require "config.php";
require "functions.php";
requireLogin();

$userId = $_SESSION["user_id"];
$categories = getCategories();

$selectedMonth = (isset($_GET["month"]) && preg_match('/^\d{4}-\d{2}$/', $_GET["month"]))
    ? $_GET["month"]
    : date("Y-m");

$budgets = getBudgets($conn, $userId, $selectedMonth);
$totalSpent = getMonthlyTotalSpent($conn, $userId, $selectedMonth);
$totalBudget = array_sum($budgets);

$monthLabel = date("F Y", strtotime($selectedMonth . "-01"));

$rows = [];
foreach ($categories as $cat) {
    $spent = getMonthlySpent($conn, $userId, $cat, $selectedMonth);
    $limit = $budgets[$cat] ?? 0;

    if ($spent == 0 && $limit == 0) {
        continue;
    }

    $percent = $limit > 0 ? min(100, round(($spent / $limit) * 100)) : 0;
    $rows[] = [
        "category" => $cat,
        "spent" => $spent,
        "limit" => $limit,
        "percent" => $percent,
        "over" => $limit > 0 && $spent > $limit
    ];
}

// ---------- CSV export ----------
if (isset($_GET["export"]) && $_GET["export"] === "csv") {
    $filename = "report_" . $selectedMonth . ".csv";

    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $out = fopen("php://output", "w");

    fputcsv($out, ["Category", "Spent", "Budget", "Percent Used", "Over Budget"]);

    foreach ($rows as $r) {
        fputcsv($out, [
            $r["category"],
            number_format($r["spent"], 2, ".", ""),
            $r["limit"] > 0 ? number_format($r["limit"], 2, ".", "") : "",
            $r["limit"] > 0 ? $r["percent"] . "%" : "",
            $r["over"] ? "Yes" : "No"
        ]);
    }

    fputcsv($out, []);
    fputcsv($out, [
        "Total",
        number_format($totalSpent, 2, ".", ""),
        $totalBudget > 0 ? number_format($totalBudget, 2, ".", "") : "",
        "",
        ""
    ]);

    fclose($out);
    exit;
}

$showPreview = isset($_GET["preview"]) && $_GET["preview"] === "csv";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reports · Pesa</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* reports.php - inline styling.
           .filter-bar / .data-table / .table-wrap / .empty-state / .inline-links
           / .stats-grid / .stat-card are duplicated here (kept in style.css too,
           since dashboard.php/expenses.php/budget.php also use them).
           .budget-row / .budget-row-top are moved for real — this is the only
           page that uses them. */

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

        .budget-row {
            margin-bottom: var(--space-5);
        }

        .budget-row:last-child {
            margin-bottom: 0;
        }

        .budget-row-top {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .budget-row-top .name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .budget-row-top .amounts {
            color: var(--text-muted);
            font-variant-numeric: tabular-nums;
        }

        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="site-page">
    <?php include "nav.php"; ?>

    <main class="page-shell page-shell--wide">
        <div class="card">
            <h2 class="card-title"><?php echo htmlspecialchars($monthLabel); ?> Report</h2>
            <p class="card-subtitle">A breakdown of spending vs. budget across categories.</p>

            <form method="get" action="reports.php" class="filter-bar">
                <div class="field">
                    <label for="month">Viewing month</label>
                    <input type="month" id="month" name="month" value="<?php echo htmlspecialchars($selectedMonth); ?>">
                </div>
                <button type="submit" class="btn btn-secondary">Go</button>
            </form>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="label">Total spent</div>
                    <div class="value tabular"><?php echo number_format($totalSpent, 2); ?></div>
                </div>
                <div class="stat-card">
                    <div class="label">Total budget</div>
                    <div class="value tabular">
                        <?php echo $totalBudget > 0 ? number_format($totalBudget, 2) : '—'; ?>
                    </div>
                </div>
            </div>

            <?php if (!$showPreview): ?>
                <?php if (empty($rows)): ?>
                    <div class="empty-state">
                        <p>No expenses or budgets recorded for <?php echo htmlspecialchars($monthLabel); ?>.</p>
                        <a class="btn btn-primary" href="AddExpense.php">+ Add an expense</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <?php
                            $fillClass = $r["over"] ? 'progress-fill--over'
                                      : ($r["percent"] >= 80 ? 'progress-fill--warn' : 'progress-fill--safe');
                        ?>
                        <div class="budget-row">
                            <div class="budget-row-top">
                                <span class="name"><?php echo htmlspecialchars($r["category"]); ?></span>
                                <span class="amounts tabular">
                                    <?php echo number_format($r["spent"], 2); ?>
                                    <?php if ($r["limit"] > 0): ?>
                                        / <?php echo number_format($r["limit"], 2); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php if ($r["limit"] > 0): ?>
                                <div class="progress">
                                    <div class="progress-fill <?php echo $fillClass; ?>" style="width: <?php echo $r['percent']; ?>%;"></div>
                                </div>
                                <?php if ($r["over"]): ?>
                                    <div class="budget-note budget-note--over">Over budget</div>
                                <?php else: ?>
                                    <div class="budget-note"><?php echo $r["percent"]; ?>% of budget used</div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="budget-note">No budget set for this category</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($showPreview): ?>
                <hr class="divider">
                <h3 class="card-title" style="font-size:16px;">Export Preview</h3>
                <p class="card-subtitle">This is exactly what the CSV file will contain.</p>

                <?php if (empty($rows)): ?>
                    <div class="empty-state"><p>Nothing to export yet.</p></div>
                <?php else: ?>
                    <div class="table-wrap mb-4">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th class="num">Spent</th>
                                    <th class="num">Budget</th>
                                    <th class="num">% Used</th>
                                    <th>Over Budget</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $r): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($r["category"]); ?></td>
                                        <td class="num tabular"><?php echo number_format($r["spent"], 2); ?></td>
                                        <td class="num tabular"><?php echo $r["limit"] > 0 ? number_format($r["limit"], 2) : "—"; ?></td>
                                        <td class="num tabular"><?php echo $r["limit"] > 0 ? $r["percent"] . "%" : "—"; ?></td>
                                        <td><?php echo $r["over"] ? "Yes" : "No"; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td><strong>Total</strong></td>
                                    <td class="num tabular"><strong><?php echo number_format($totalSpent, 2); ?></strong></td>
                                    <td class="num tabular"><strong><?php echo $totalBudget > 0 ? number_format($totalBudget, 2) : "—"; ?></strong></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <a href="?month=<?php echo urlencode($selectedMonth); ?>&export=csv" class="btn btn-primary btn-block">Download CSV</a>
                <?php endif; ?>
            <?php endif; ?>

            <div class="inline-links">
                <?php if ($showPreview): ?>
                    <a href="reports.php?month=<?php echo urlencode($selectedMonth); ?>">&larr; Back to report</a>
                <?php else: ?>
                    <a href="?month=<?php echo urlencode($selectedMonth); ?>&preview=csv">Preview &amp; Export</a>
                    <a href="budget.php?month=<?php echo urlencode($selectedMonth); ?>">Edit Budgets</a>
                    <a href="expenses.php?month=<?php echo urlencode($selectedMonth); ?>">View Expenses</a>
                    <a href="dashboard.php?month=<?php echo urlencode($selectedMonth); ?>">&larr; Back to Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
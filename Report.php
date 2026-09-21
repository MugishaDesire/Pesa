<?php
// reports.php
require "config.php";
require "functions.php";
requireLogin();

$userId = $_SESSION["user_id"];
$categories = getCategories();
$budgets = getBudgets($conn, $userId);
$totalSpent = getMonthlyTotalSpent($conn, $userId);
$totalBudget = array_sum($budgets);

// Build one row of data per category: spent, limit, and % used
$rows = [];
foreach ($categories as $cat) {
    $spent = getMonthlySpent($conn, $userId, $cat);
    $limit = $budgets[$cat] ?? 0;

    // Skip categories with no spending and no budget set — nothing to report
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
// Must run before any HTML/whitespace is output, since it sends its own headers.
if (isset($_GET["export"]) && $_GET["export"] === "csv") {
    $filename = "report_" . date("Y-m-d") . ".csv";

    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $out = fopen("php://output", "w");

    // Header row
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

    // Totals row
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

// Preview mode: show the exact export data in a table before downloading
$showPreview = isset($_GET["preview"]) && $_GET["preview"] === "csv";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-box" style="max-width: 700px;">
        <h2>This Month's Report</h2>

        <p style="margin: 15px 0; text-align:center; font-size: 16px;">
            Total spent: <strong><?php echo number_format($totalSpent, 2); ?></strong>
            <?php if ($totalBudget > 0): ?>
                &nbsp;/&nbsp; Total budget: <strong><?php echo number_format($totalBudget, 2); ?></strong>
            <?php endif; ?>
        </p>

        <?php if (!$showPreview): ?>
            <?php if (empty($rows)): ?>
                <p style="text-align:center;">No expenses or budgets recorded yet this month.</p>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                    <div style="margin-bottom: 16px;">
                        <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:4px;">
                            <span><?php echo htmlspecialchars($r["category"]); ?></span>
                            <span>
                                <?php echo number_format($r["spent"], 2); ?>
                                <?php if ($r["limit"] > 0): ?>
                                    / <?php echo number_format($r["limit"], 2); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php if ($r["limit"] > 0): ?>
                            <!-- The bar's width in % visually shows how much of the budget is used -->
                            <div style="background:#eee; border-radius:5px; height:10px; overflow:hidden;">
                                <div style="width:<?php echo $r['percent']; ?>%; height:100%; background:<?php echo $r['over'] ? '#e53935' : '#4a6cf7'; ?>;"></div>
                            </div>
                            <?php if ($r["over"]): ?>
                                <div style="color:#b71c1c; font-size:12px; margin-top:2px;">Over budget</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="font-size:12px; color:#888;">No budget set</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($showPreview): ?>
            <hr class="divider">
            <h3 style="margin-bottom: 12px;">Export Preview</h3>

            <?php if (empty($rows)): ?>
                <p style="text-align:center;">Nothing to export yet.</p>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Spent</th>
                                <th>Budget</th>
                                <th>% Used</th>
                                <th>Over Budget</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r["category"]); ?></td>
                                    <td><?php echo number_format($r["spent"], 2); ?></td>
                                    <td><?php echo $r["limit"] > 0 ? number_format($r["limit"], 2) : "—"; ?></td>
                                    <td><?php echo $r["limit"] > 0 ? $r["percent"] . "%" : "—"; ?></td>
                                    <td><?php echo $r["over"] ? "Yes" : "No"; ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong><?php echo number_format($totalSpent, 2); ?></strong></td>
                                <td><strong><?php echo $totalBudget > 0 ? number_format($totalBudget, 2) : "—"; ?></strong></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <a href="?export=csv" class="btn btn-primary" style="margin-top: 16px;">Download CSV</a>
            <?php endif; ?>

            <p style="margin-top:15px;">
                <a href="Report.php">&larr; Back to report</a>
            </p>
        <?php else: ?>
            <p style="margin-top:15px;">
                <a href="?preview=csv">Preview & Export</a> &nbsp;|&nbsp;
                <a href="budget.php">Edit Budgets</a> &nbsp;|&nbsp;
                <a href="expenses.php">View Expenses</a> &nbsp;|&nbsp;
                <a href="dashboard.php">&larr; Back to Dashboard</a>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// budget.php
require "config.php";
require "functions.php";
requireLogin();

$userId = $_SESSION["user_id"];
$categories = getCategories();
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // The form sends one amount field per category, named like "budget_Food", "budget_Transport", etc.
    foreach ($categories as $cat) {
        $fieldName = "budget_" . $cat;
        $value = trim($_POST[$fieldName] ?? "");

        // Only save categories where the user actually typed a number
        if ($value !== "" && is_numeric($value) && $value >= 0) {
            // "ON DUPLICATE KEY UPDATE" means: insert a new row, but if one already
            // exists for this user+category (our UNIQUE KEY from database.sql),
            // update it instead of creating a duplicate.
            $stmt = $conn->prepare(
                "INSERT INTO budgets (user_id, category, monthly_limit)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE monthly_limit = VALUES(monthly_limit)"
            );
            $stmt->bind_param("isd", $userId, $cat, $value);
            $stmt->execute();
            $stmt->close();
        }
    }
    $success = "Budgets updated.";
}

// Reload current budgets so the form shows existing values
$currentBudgets = getBudgets($conn, $userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Budgets</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-box">
        <h2>Monthly Budgets</h2>

        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post" action="budget.php">
            <?php foreach ($categories as $cat): ?>
                <label for="budget_<?php echo $cat; ?>"><?php echo htmlspecialchars($cat); ?></label>
                <input
                    type="number" step="0.01" min="0"
                    id="budget_<?php echo $cat; ?>"
                    name="budget_<?php echo $cat; ?>"
                    value="<?php echo isset($currentBudgets[$cat]) ? htmlspecialchars($currentBudgets[$cat]) : ''; ?>"
                    placeholder="0.00">
            <?php endforeach; ?>

            <button type="submit">Save Budgets</button>
        </form>

        <p style="margin-top:15px;"><a href="dashboard.php">&larr; Back to Dashboard</a></p>
    </div>
</body>
</html>

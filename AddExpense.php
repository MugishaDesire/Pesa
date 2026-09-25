<?php
// AddExpense.php
require "config.php";
require "functions.php";
requireLogin();

$error = "";
$success = "";
$categories = getCategories();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category    = $_POST["category"];
    $amount      = trim($_POST["amount"]);
    $description = trim($_POST["description"]);
    $expenseDate = $_POST["expense_date"];

    if ($category === "" || $amount === "" || $expenseDate === "") {
        $error = "Please fill in category, amount, and date.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Amount must be a positive number.";
    } elseif (!in_array($category, $categories)) {
        $error = "Please choose a valid category.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO expenses (user_id, category, amount, description, expense_date)
             VALUES (?, ?, ?, ?, ?)"
        );
        $userId = $_SESSION["user_id"];
        $stmt->bind_param("isdss", $userId, $category, $amount, $description, $expenseDate);

        if ($stmt->execute()) {
            $success = "Expense recorded successfully.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Expense · Pesa</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* AddExpense.php - inline copy of the shared .inline-links component.
           Kept in style.css too (other pages depend on it); duplicated
           here so this page still renders correctly on its own. */

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
    <main class="page-shell page-shell--narrow">
        <div class="card">
            <h2 class="card-title">Add Expense</h2>
            <p class="card-subtitle">Log what you spent — it only takes a moment.</p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">!</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <span class="alert-icon">✓</span>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <form method="post" action="AddExpense.php">
                <div class="field">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="">-- Select a category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="amount">Amount</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" placeholder="0.00" required>
                </div>

                <div class="field">
                    <label for="expense_date">Date</label>
                    <input type="date" id="expense_date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="field">
                    <label for="description">Description <span class="faint">(optional)</span></label>
                    <input type="text" id="description" name="description" placeholder="e.g. Lunch with client">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Save Expense</button>
            </form>

            <div class="inline-links">
                <a href="dashboard.php">&larr; Back to Dashboard</a>
                <a href="expenses.php">View Expenses</a>
            </div>
        </div>
    </main>
</body>
</html>
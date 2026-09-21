<?php
// add_expense.php
require "config.php";
require "functions.php";
requireLogin(); // only logged-in users can reach this page

$error = "";
$success = "";
$categories = getCategories();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category    = $_POST["category"];
    $amount      = trim($_POST["amount"]);
    $description = trim($_POST["description"]);
    $expenseDate = $_POST["expense_date"];

    // Validation
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
        // "isdss": i=int, s=string, d=double(decimal), s=string, s=string
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
    <title>Add Expense</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-box">
        <h2>Add Expense</h2>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post" action="AddExpense.php">
            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="">-- Select --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="amount">Amount</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>

            <label for="expense_date">Date</label>
            <input type="date" id="expense_date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>

            <label for="description">Description (optional)</label>
            <input type="text" id="description" name="description" placeholder="e.g. Lunch with client">

            <button type="submit">Save Expense</button>
        </form>

        <p class="back-link"><a href="dashboard.php">&larr; Back to Dashboard</a></p>
    </div>
</body>
</html>
<?php
// expenses.php
require "config.php";
require "functions.php";
requireLogin();

$userId = $_SESSION["user_id"];

// Handle deletion (a "?delete=5" link on this same page)
if (isset($_GET["delete"])) {
    $deleteId = (int) $_GET["delete"]; // cast to int so nothing but a number can be injected here

    // IMPORTANT: we also filter by user_id, so a user can only delete THEIR OWN expenses,
    // never someone else's by guessing an ID in the URL.
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $deleteId, $userId);
    $stmt->execute();
    $stmt->close();

    header("Location: expenses.php"); // redirect to avoid re-deleting on page refresh
    exit();
}

// Fetch all expenses for this user, most recent first
$stmt = $conn->prepare("SELECT id, category, amount, description, expense_date FROM expenses WHERE user_id = ? ORDER BY expense_date DESC, id DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$expenses = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Expenses</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-box" style="max-width: 700px;">
        <h2>My Expenses</h2>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($expenses->num_rows === 0): ?>
                    <tr><td colspan="5" style="text-align:center;">No expenses recorded yet.</td></tr>
                <?php endif; ?>

                <?php while ($row = $expenses->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row["expense_date"]); ?></td>
                        <td><?php echo htmlspecialchars($row["category"]); ?></td>
                        <td><?php echo htmlspecialchars($row["description"]); ?></td>
                        <td><?php echo number_format($row["amount"], 2); ?></td>
                        <td>
                            <a href="expenses.php?delete=<?php echo $row['id']; ?>"
                               onclick="return confirm('Delete this expense?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <p style="margin-top:15px;">
            <a href="AddExpense.php">+ Add Expense</a> &nbsp;|&nbsp;
            <a href="dashboard.php">&larr; Back to Dashboard</a>
        </p>
    </div>
</body>
</html>

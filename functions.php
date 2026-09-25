<?php
function requireLogin() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit();
    }
}


function getCategories() {
    return ["Food", "Transport", "Housing", "Utilities", "Entertainment", "Health", "Business", "Other"];
}


function getMonthlySpent($conn, $user_id, $category, $month = null) {
    $month = $month ?: date("Y-m");

    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(amount), 0) AS total
         FROM expenses
         WHERE user_id = ?
           AND category = ?
           AND DATE_FORMAT(expense_date, '%Y-%m') = ?"
    );
    
    $stmt->bind_param("iss", $user_id, $category, $month); // "i" = integer, "s" = string
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (float) $result["total"];
}

// Returns the total spent across ALL categories for the given month
// (format "Y-m"). Defaults to the current month when omitted.
function getMonthlyTotalSpent($conn, $user_id, $month = null) {
    $month = $month ?: date("Y-m");

    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(amount), 0) AS total
         FROM expenses
         WHERE user_id = ?
           AND DATE_FORMAT(expense_date, '%Y-%m') = ?"
    );
    $stmt->bind_param("is", $user_id, $month);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (float) $result["total"];
}

// Returns an associative array of category => monthly_limit for one user,
// for the given month (format "Y-m"). Defaults to the current month when
// no month is passed.
// Example result: ["Food" => 100.00, "Transport" => 50.00]
function getBudgets($conn, $user_id, $month = null) {
    $month = $month ?: date("Y-m");

    $stmt = $conn->prepare("SELECT category, monthly_limit FROM budgets WHERE user_id = ? AND budget_month = ?");
    $stmt->bind_param("is", $user_id, $month);
    $stmt->execute();
    $result = $stmt->get_result();

    $budgets = [];
    while ($row = $result->fetch_assoc()) {
        $budgets[$row["category"]] = (float) $row["monthly_limit"];
    }
    $stmt->close();
    return $budgets;
}
?>

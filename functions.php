<?php
// functions.php
// Reusable helper functions used by dashboard.php, reports.php, etc.
// This file assumes $conn (the database connection) already exists,
// because it's always require'd AFTER config.php.

// Call this at the top of any page that should only be visible to logged-in users.
// It's the same check we used in dashboard.php, just reused everywhere now.
function requireLogin() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit();
    }
}

// The fixed list of categories used across all forms and reports.
// Keeping this in one place means every dropdown stays consistent.
function getCategories() {
    return ["Food", "Transport", "Housing", "Utilities", "Entertainment", "Health", "Business", "Other"];
}

// Returns the total amount a user has spent in a given category,
// for the given month (format "Y-m", e.g. "2026-09"). Defaults to the
// current calendar month when no month is passed, so existing calls
// keep working exactly as before.
function getMonthlySpent($conn, $user_id, $category, $month = null) {
    $month = $month ?: date("Y-m");

    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(amount), 0) AS total
         FROM expenses
         WHERE user_id = ?
           AND category = ?
           AND DATE_FORMAT(expense_date, '%Y-%m') = ?"
    );
    // COALESCE(SUM(amount), 0) means: if there are no rows, return 0 instead of NULL
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

// Returns an associative array of category => monthly_limit for one user.
// Example result: ["Food" => 100.00, "Transport" => 50.00]
// Budgets are a standing per-category limit, not tied to a specific
// month, so this is unaffected by month filtering.
function getBudgets($conn, $user_id) {
    $stmt = $conn->prepare("SELECT category, monthly_limit FROM budgets WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
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

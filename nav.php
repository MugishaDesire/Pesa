<?php
// nav.php
// Shared navigation bar for authenticated pages.
// Include this AFTER config.php + functions.php + requireLogin() have
// already run on the including page, and after $selectedMonth is set
// (falls back to the current month if the page didn't define one).

$navMonth = $selectedMonth ?? date("Y-m");
$navSelf = basename($_SERVER["PHP_SELF"]);
?>
<style>
    /* nav.php - page-specific styling */

    .navbar {
        background: var(--surface);
        border-bottom: 1px solid var(--border);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .navbar-inner {
        max-width: 960px;
        margin: 0 auto;
        padding: 14px var(--space-5);
        display: flex;
        align-items: center;
        gap: var(--space-5);
    }

    .navbar-brand {
        font-weight: 800;
        font-size: 18px;
        color: var(--text-dark);
        letter-spacing: -0.01em;
    }

    .navbar-brand:hover {
        text-decoration: none;
        color: var(--primary);
    }

    .navbar-links {
        display: flex;
        gap: var(--space-4);
        flex: 1;
    }

    .navbar-links a {
        color: var(--text-muted);
        font-size: 14px;
        font-weight: 500;
        padding: 4px 0;
        border-bottom: 2px solid transparent;
    }

    .navbar-links a:hover {
        color: var(--text-dark);
        text-decoration: none;
    }

    .navbar-links a.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    .navbar-month {
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }

    .navbar-month label {
        font-size: 13px;
        color: var(--text-muted);
    }

    .navbar-month input[type="month"] {
        padding: 6px 10px;
        border: 1px solid var(--border-strong);
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-family: inherit;
        background: var(--surface);
    }

    .navbar-month button {
        padding: 7px 12px;
        background: var(--primary);
        color: #fff;
        border: none;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .navbar-month button:hover {
        background: var(--primary-dark);
    }

    @media (max-width: 640px) {
        .navbar-inner {
            flex-wrap: wrap;
            gap: var(--space-3);
        }

        .navbar-links {
            order: 3;
            flex-basis: 100%;
            border-top: 1px solid var(--border);
            padding-top: var(--space-3);
        }

        .navbar-month {
            margin-left: auto;
        }
    }
</style>
<nav class="navbar">
    <div class="navbar-inner">
        <a class="navbar-brand" href="index.php">Pesa</a>

        <div class="navbar-links">
            <a href="index.php" class="<?php echo $navSelf === 'index.php' ? 'active' : ''; ?>">Home</a>
            <a href="dashboard.php" class="<?php echo $navSelf === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        </div>

        <form method="get" action="<?php echo htmlspecialchars($navSelf); ?>" class="navbar-month">
            <label for="navbar-month-input">Month</label>
            <input type="month" id="navbar-month-input" name="month"
                   value="<?php echo htmlspecialchars($navMonth); ?>">
            <button type="submit">Go</button>
        </form>
    </div>
</nav>
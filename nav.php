<?php
// nav.php
// Shared navigation bar for authenticated pages.
// Include this AFTER config.php + functions.php + requireLogin() have
// already run on the including page, and after $selectedMonth is set
// (falls back to the current month if the page didn't define one).

$navMonth = $selectedMonth ?? date("Y-m");
$navSelf = basename($_SERVER["PHP_SELF"]);
$navLoggedIn = isset($_SESSION["user_id"]);
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

    .navbar-logout {
        flex-shrink: 0;
    }

    /* From Uiverse.io by vinodjangid07 — scoped to .navbar-logout to avoid
       clashing with other elements on the page (generic class names). */
    .navbar-logout .Btn {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        width: 45px;
        height: 45px;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition-duration: .3s;
        box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.199);
        background-color: rgb(255, 65, 65);
    }

    .navbar-logout .sign {
        width: 100%;
        transition-duration: .3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .navbar-logout .sign svg {
        width: 17px;
    }

    .navbar-logout .sign svg path {
        fill: white;
    }

    .navbar-logout .text {
        position: absolute;
        right: 0%;
        width: 0%;
        opacity: 0;
        color: white;
        font-size: 1.2em;
        font-weight: 600;
        transition-duration: .3s;
    }

    .navbar-logout .Btn:hover {
        width: 125px;
        border-radius: 40px;
        transition-duration: .3s;
    }

    .navbar-logout .Btn:hover .sign {
        width: 30%;
        transition-duration: .3s;
        padding-left: 20px;
    }

    .navbar-logout .Btn:hover .text {
        opacity: 1;
        width: 70%;
        transition-duration: .3s;
        padding-right: 10px;
    }

    .navbar-logout .Btn:active {
        transform: translate(2px, 2px);
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

        <?php if ($navLoggedIn): ?>
            <form method="post" action="logout.php" class="navbar-logout">
                <!-- From Uiverse.io by vinodjangid07 -->
                <button type="submit" class="Btn" aria-label="Log Out" title="Log Out">
                    <div class="sign"><svg viewBox="0 0 512 512"><path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"></path></svg></div>
                    <div class="text">Logout</div>
                </button>
            </form>
        <?php endif; ?>
    </div>
</nav>
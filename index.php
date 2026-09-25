<?php
// index.php
// Public homepage. No login required.
require "config.php";

$isLoggedIn = isset($_SESSION["user_id"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesa — track what you spend</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* index.php - page-specific styling.
           .topbar / .brand-mark / .btn-primary-sm are duplicated here (kept in
           style.css too) since login.php/register.php may share the same header.
           .hero / .lede / .preview-card family / .site-footer are moved for real
           — this landing page is the only place that uses them. */

        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .topbar-inner {
            max-width: 960px;
            margin: 0 auto;
            padding: 14px var(--space-5);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .topbar-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 20px;
            color: var(--text-dark);
            letter-spacing: -0.02em;
        }

        .topbar-brand:hover {
            text-decoration: none;
            color: var(--primary);
        }

        .brand-mark {
            display: grid;
            place-items: center;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: var(--primary);
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .topbar-actions .link {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
        }

        .topbar-actions .link:hover {
            color: var(--text-dark);
            text-decoration: none;
        }

        .btn-primary-sm {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            background: var(--primary);
            color: #fff;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.15s ease;
        }

        .btn-primary-sm:hover {
            background: var(--primary-dark);
            text-decoration: none;
        }

        .btn-primary-sm:focus-visible,
        .topbar-brand:focus-visible,
        .topbar-actions .link:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
            border-radius: var(--radius-sm);
        }

        .hero {
            padding: var(--space-8) var(--space-5) var(--space-7);
        }

        .hero-inner {
            max-width: 960px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
            gap: var(--space-7);
            align-items: center;
        }

        .hero h1 {
            font-size: clamp(34px, 5.2vw, 52px);
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--text-dark);
            margin-bottom: var(--space-4);
            line-height: 1.08;
        }

        .lede {
            font-size: 18px;
            line-height: 1.6;
            color: var(--text-muted);
            max-width: 460px;
            margin-bottom: var(--space-5);
        }

        .hero-cta-row {
            display: flex;
            gap: var(--space-3);
            flex-wrap: wrap;
        }

        .hero-cta-row .btn {
            padding: 13px 20px;
        }

        .preview-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            padding: var(--space-5);
        }

        .preview-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-3);
            margin-bottom: var(--space-5);
        }

        .preview-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .preview-sub {
            display: block;
            font-size: 12px;
            color: var(--text-faint);
            font-weight: 400;
        }

        .preview-row {
            margin-bottom: var(--space-4);
        }

        .preview-row-top {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .preview-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .preview-amounts {
            font-size: 13px;
            color: var(--text-muted);
            font-variant-numeric: tabular-nums;
        }

        .preview-bar-track {
            background: #eef0f5;
            border-radius: 999px;
            height: 8px;
            overflow: hidden;
        }

        .preview-bar-fill {
            height: 100%;
            width: var(--fill, 0%);
            background: var(--success);
            border-radius: 999px;
            animation: bar-fill 0.9s cubic-bezier(0.2, 0.7, 0.2, 1) both;
        }

        .preview-bar-fill--warn { background: var(--warning); }
        .preview-bar-fill--over { background: var(--danger); }

        .preview-row:nth-of-type(1) .preview-bar-fill { animation-delay: 0.05s; }
        .preview-row:nth-of-type(2) .preview-bar-fill { animation-delay: 0.15s; }
        .preview-row:nth-of-type(3) .preview-bar-fill { animation-delay: 0.25s; }

        .preview-note {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .preview-note--over {
            color: var(--danger-dark);
            font-weight: 600;
        }

        .preview-foot {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-top: var(--space-5);
            padding-top: var(--space-4);
            border-top: 1px solid var(--border);
            font-size: 14px;
            color: var(--text-muted);
        }

        .preview-foot strong {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text-dark);
            font-variant-numeric: tabular-nums;
        }

        .site-footer {
            text-align: center;
            padding: var(--space-6) var(--space-5);
            color: var(--text-muted);
            font-size: 13px;
            border-top: 1px solid var(--border);
        }

        .site-footer a {
            color: var(--text-muted);
            font-weight: 600;
        }

        .site-footer a:hover {
            color: var(--primary);
        }

        @media (max-width: 820px) {
            .hero {
                padding: var(--space-7) var(--space-4) var(--space-6);
            }

            .hero-inner {
                grid-template-columns: 1fr;
                gap: var(--space-6);
            }
        }

        @media (max-width: 640px) {
            .topbar-inner {
                padding: 12px var(--space-4);
            }

            .topbar-actions {
                gap: var(--space-3);
            }

            .hero-cta-row .btn {
                flex: 1;
                min-width: 0;
            }
        }

        /* From Uiverse.io by Smit-Prajapati — scaled down to fit the topbar */
        .button {
            --main-color: rgb(46, 213, 115);
            --main-bg-color: rgba(46, 213, 116, 0.36);
            --pattern-color: rgba(46, 213, 116, 0.073);

            filter: hue-rotate(0deg);

            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.12rem;
            background: radial-gradient(
                    circle,
                    var(--main-bg-color) 0%,
                    rgba(0, 0, 0, 0) 95%
                ),
                linear-gradient(var(--pattern-color) 1px, transparent 1px),
                linear-gradient(to right, var(--pattern-color) 1px, transparent 1px);
            background-size:
                cover,
                8px 8px,
                8px 8px;
            background-position:
                center center,
                center center,
                center center;
            border-image: radial-gradient(
                    circle,
                    var(--main-color) 0%,
                    rgba(0, 0, 0, 0) 100%
                )
                1;
            border-width: 1px 0 1px 0;
            color: var(--main-color);
            padding: 8px 14px;
            font-weight: 700;
            font-size: 0.8rem;
            line-height: 1;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: background-size 0.2s ease-in-out;
        }

        .button:hover {
            background-size:
                cover,
                6px 6px,
                6px 6px;
            text-decoration: none;
        }

        .button:active {
            filter: hue-rotate(250deg);
        }
    </style>
</head>
<body class="site-page">

    <div class="topbar">
        <div class="topbar-inner">
            <a class="topbar-brand" href="index.php">Pesa</a>
            <div class="topbar-actions">
                <?php if ($isLoggedIn): ?>
                    <a class="btn-primary-sm" href="dashboard.php">Go to Dashboard</a>
                <?php else: ?>
                    <a class="button" href="login.php">Log in</a>
                    <a class="btn-primary-sm" href="register.php">Get started</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <section class="hero">
        <div class="hero-inner">
            <h1>Know where every franc goes.</h1>
            <p class="lede">
                Pesa is a simple place to log what you spend, set a limit for each
                category, and see clearly whether you're on track before the month
                runs out.
            </p>

            <div class="hero-cta-row">
                <?php if ($isLoggedIn): ?>
                    <a class="btn btn-primary" href="dashboard.php">Dashboard</a>
                <?php else: ?>
                    <a class="btn btn-primary" href="login.php">Dashboard</a>
                    <a class="btn btn-outline" href="#how-it-works">See how it works</a>
                <?php endif; ?>
            </div>

            <div class="preview-card" id="preview-card">
                <div class="preview-title">A month, at a glance</div>

                <div class="preview-row">
                    <div class="preview-row-top">
                        <span>Food</span>
                        <span class="preview-amounts">29,000 / 40,000</span>
                    </div>
                    <div class="preview-bar-track"><div class="preview-bar-fill" data-target="72"></div></div>
                </div>

                <div class="preview-row">
                    <div class="preview-row-top">
                        <span>Transport</span>
                        <span class="preview-amounts">34,500 / 30,000</span>
                    </div>
                    <div class="preview-bar-track"><div class="preview-bar-fill" data-target="100"></div></div>
                </div>

                <div class="preview-row">
                    <div class="preview-row-top">
                        <span>Entertainment</span>
                        <span class="preview-amounts">7,000 / 20,000</span>
                    </div>
                    <div class="preview-bar-track"><div class="preview-bar-fill" data-target="35"></div></div>
                </div>
            </div>
        </div>
    </section>

    <section class="features-section" id="how-it-works">
        <h2>How it works</h2>

        <div class="feature-row">
            <div class="feature-name">Set a budget</div>
            <div class="feature-desc">Put a limit on what you spend, by category, for the month.</div>
        </div>

        <div class="feature-row">
            <div class="feature-name">Log expenses</div>
            <div class="feature-desc">Record what you spend as it happens, sorted by category and date.</div>
        </div>

        <div class="feature-row">
            <div class="feature-name">See it clearly</div>
            <div class="feature-desc">One glance at any month shows what's left, and what's already gone over.</div>
        </div>

        <div class="feature-row">
            <div class="feature-name">Export a report</div>
            <div class="feature-desc">Preview any month's numbers, then download them as a CSV when you need one.</div>
        </div>
    </section>

    <footer class="site-footer">
        Pesa — a simple way to track your money.
        <?php if (!$isLoggedIn): ?>
            &nbsp;·&nbsp; <a href="login.php">Log in</a>
        <?php endif; ?>
    </footer>

    <script>
        window.addEventListener("load", function () {
            document.querySelectorAll(".preview-bar-fill").forEach(function (bar) {
                var target = parseInt(bar.getAttribute("data-target"), 10);
                if (target > 100) {
                    bar.classList.add("over");
                }
                requestAnimationFrame(function () {
                    bar.style.width = Math.min(100, target) + "%";
                });
            });
        });
    </script>
</body>
</html>
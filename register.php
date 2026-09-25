<?php
// register.php
require "config.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm  = $_POST["confirm_password"];

    if ($username === "" || $email === "" || $password === "") {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "An account with that email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $insert->bind_param("sss", $username, $email, $hashedPassword);

            if ($insert->execute()) {
                $success = "Account created! You can now log in.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $insert->close();
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
    <title>Create Account · Pesa</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* register.php - inline copy of auth-page styling.
           Kept in style.css too, since login.php likely shares this
           .form-box / password-toggle pattern. Duplicated here so this
           page still renders correctly on its own. */

        .form-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: var(--space-6);
            width: 100%;
            max-width: 380px;
        }

        .form-box h2 {
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            color: var(--text-dark);
            margin-bottom: var(--space-5);
        }

        .form-box label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
            margin-top: var(--space-3);
        }

        .form-box button[type="submit"] {
            width: 100%;
            margin-top: var(--space-4);
            padding: 12px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }

        .form-box button[type="submit"]:hover {
            background: var(--primary-dark);
        }

        .field-hint {
            font-size: 12px;
            color: var(--text-faint);
            margin-top: 4px;
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap input {
            padding-right: 64px;
        }

        .password-toggle {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            padding: 4px 6px;
            border-radius: var(--radius-sm);
        }

        .password-toggle:hover {
            color: var(--primary);
            background: var(--primary-soft);
        }
    </style>
</head>
<body class="centered">
    <div class="form-box">
        <h2>Create your account</h2>

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

        <form method="post" action="register.php">
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="password-wrap">
                    <input type="password" id="password" name="password" required minlength="6">
                    <button type="button" class="password-toggle" data-target="password">Show</button>
                </div>
                <div class="field-hint">At least 6 characters.</div>
            </div>

            <div class="field">
                <label for="confirm_password">Confirm password</label>
                <div class="password-wrap">
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    <button type="button" class="password-toggle" data-target="confirm_password">Show</button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>

        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>

    <script>
        document.querySelectorAll(".password-toggle").forEach(function (btn) {
            btn.addEventListener("click", function () {
                var input = document.getElementById(btn.getAttribute("data-target"));
                if (!input) return;
                var showing = input.type === "text";
                input.type = showing ? "password" : "text";
                btn.textContent = showing ? "Show" : "Hide";
            });
        });
    </script>
</body>
</html>
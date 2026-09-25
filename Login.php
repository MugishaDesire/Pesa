<?php
// login.php
require "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($email === "" || $password === "") {
        $error = "Please fill in both fields.";
    } else {
        // 1. Find the user by email
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // 2. Verify the typed password against the stored hash
            if (password_verify($password, $user["password"])) {
                // 3. Correct! Store info in the session so other pages know we're logged in
                $_SESSION["user_id"]  = $user["id"];
                $_SESSION["username"] = $user["username"];

                header("Location: dashboard.php"); // redirect to the protected page
                exit(); // always exit() right after a redirect
            } else {
                $error = "Incorrect email or password.";
            }
        } else {
            $error = "Incorrect email or password.";
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
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* login.php - inline copy of auth-page styling.
           Kept in style.css too, since register.php shares this
           .form-box pattern. Duplicated here so this page still
           renders correctly on its own. */

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
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Log In</h2>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Log In</button>
        </form>

        <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
</body>
</html>
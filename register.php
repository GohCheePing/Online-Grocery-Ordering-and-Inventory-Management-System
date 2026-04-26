<?php
session_start();
require 'db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT * FROM customer WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO customer (full_name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $fullname, $email, $hashed_password);

            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Register</title>
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:-apple-system, BlinkMacSystemFont, "SF Pro Display", "Segoe UI", sans-serif;
        }

        body{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            background:
                radial-gradient(circle at top left, rgba(255,255,255,0.35), transparent 30%),
                radial-gradient(circle at bottom right, rgba(173,216,255,0.28), transparent 30%),
                linear-gradient(135deg, #dfe9f3, #ffffff, #d6e4f0);
            overflow:hidden;
            position:relative;
            padding:20px;
        }

        body::before,
        body::after{
            content:"";
            position:absolute;
            border-radius:50%;
            filter:blur(60px);
            z-index:0;
        }

        body::before{
            width:250px;
            height:250px;
            background:rgba(255,255,255,0.45);
            top:8%;
            left:10%;
        }

        body::after{
            width:300px;
            height:300px;
            background:rgba(120,180,255,0.20);
            bottom:8%;
            right:10%;
        }

        .register-card{
            position:relative;
            z-index:1;
            width:100%;
            max-width:440px;
            padding:40px 32px;
            border-radius:32px;
            background:rgba(255,255,255,0.22);
            backdrop-filter:blur(25px) saturate(180%);
            -webkit-backdrop-filter:blur(25px) saturate(180%);
            border:1px solid rgba(255,255,255,0.35);
            box-shadow:
                0 8px 32px rgba(31, 38, 135, 0.15),
                inset 0 1px 0 rgba(255,255,255,0.45),
                inset 0 -1px 0 rgba(255,255,255,0.12);
        }

        .register-card h2{
            font-size:30px;
            font-weight:700;
            color:#1d1d1f;
            margin-bottom:8px;
        }

        .subtitle{
            color:rgba(29,29,31,0.65);
            font-size:15px;
            margin-bottom:28px;
        }

        .error{
            background:rgba(255, 59, 48, 0.10);
            border:1px solid rgba(255, 59, 48, 0.18);
            color:#b42318;
            padding:12px 14px;
            border-radius:14px;
            margin-bottom:18px;
            font-size:14px;
        }

        .success{
            background:rgba(52, 199, 89, 0.10);
            border:1px solid rgba(52, 199, 89, 0.18);
            color:#157f3b;
            padding:12px 14px;
            border-radius:14px;
            margin-bottom:18px;
            font-size:14px;
        }

        .input-group{
            margin-bottom:18px;
        }

        .input-group label{
            display:block;
            margin-bottom:8px;
            color:#1d1d1f;
            font-size:14px;
            font-weight:600;
        }

        .input-group input{
            width:100%;
            padding:14px 16px;
            border:none;
            outline:none;
            border-radius:16px;
            background:rgba(255,255,255,0.35);
            color:#1d1d1f;
            font-size:15px;
            backdrop-filter:blur(10px);
            -webkit-backdrop-filter:blur(10px);
            box-shadow:
                inset 0 1px 1px rgba(255,255,255,0.45),
                inset 0 -1px 1px rgba(0,0,0,0.03);
            transition:all 0.25s ease;
        }

        .input-group input::placeholder{
            color:rgba(29,29,31,0.45);
        }

        .input-group input:focus{
            background:rgba(255,255,255,0.48);
            box-shadow:
                0 0 0 3px rgba(0,122,255,0.12),
                inset 0 1px 1px rgba(255,255,255,0.5);
        }

        .password-wrap{
            position:relative;
        }

        .password-wrap input{
            padding-right:52px;
        }

        .toggle-password{
            position:absolute;
            right:14px;
            top:50%;
            transform:translateY(-50%);
            border:none;
            background:transparent;
            cursor:pointer;
            font-size:18px;
            color:rgba(29,29,31,0.55);
        }

        .register-btn{
            width:100%;
            padding:14px;
            border:none;
            border-radius:18px;
            background:rgba(255,255,255,0.42);
            color:#1d1d1f;
            font-size:16px;
            font-weight:700;
            cursor:pointer;
            margin-top:10px;
            backdrop-filter:blur(12px);
            -webkit-backdrop-filter:blur(12px);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.65),
                0 6px 18px rgba(0,0,0,0.08);
            transition:all 0.25s ease;
        }

        .register-btn:hover{
            transform:translateY(-1px);
            background:rgba(255,255,255,0.55);
        }

        .login-text{
            text-align:center;
            margin-top:20px;
            font-size:14px;
            color:rgba(29,29,31,0.7);
        }

        .login-text a{
            color:#0071e3;
            text-decoration:none;
            font-weight:600;
        }

        .login-text a:hover{
            text-decoration:underline;
        }

        @media (max-width: 480px){
            .register-card{
                padding:30px 22px;
                border-radius:26px;
            }

            .register-card h2{
                font-size:26px;
            }
        }
    </style>
</head>
<body>

    <div class="register-card">
        <h2>Create account</h2>
        <p class="subtitle">Join Freshmart and start shopping</p>

        <?php if (!empty($error)) : ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="fullname" placeholder="Enter your full name" required>
            </div>

            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="password-wrap">
                    <input type="password" name="password" id="password" placeholder="Create a password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">👁</button>
                </div>
            </div>

            <div class="input-group">
                <label>Confirm Password</label>
                <div class="password-wrap">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">👁</button>
                </div>
            </div>

            <button type="submit" class="register-btn">Register</button>
        </form>

        <p class="login-text">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === "password" ? "text" : "password";
        }
    </script>

</body>
</html>
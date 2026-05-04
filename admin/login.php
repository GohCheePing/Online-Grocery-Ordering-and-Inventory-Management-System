<?php
session_start();
require '../db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT * FROM admin
        WHERE email = ?
        LIMIT 1
    ");

    if(!$stmt){
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    $user = $result->fetch_assoc();

    $stmt->close();

    if ($user && password_verify($pass, $user['admin_password'])) {

        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_full_name'] = $user['full_name'];

        header("Location: dashboard.php");
        exit();

    } else {

        $error = "Invalid email or password";

    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Login</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
}

body{

    min-height:100vh;

    display:flex;

    justify-content:center;

    align-items:center;

    overflow:hidden;

    background:
        radial-gradient(circle at top left,#8ec5fc,transparent 30%),
        radial-gradient(circle at bottom right,#e0c3fc,transparent 30%),
        linear-gradient(135deg,#dfe9f3,#ffffff);
}

/* background blobs */
body::before,
body::after{

    content:"";

    position:absolute;

    width:420px;
    height:420px;

    border-radius:50%;

    filter:blur(90px);

    opacity:0.5;

    z-index:-1;
}

body::before{

    background:#8ec5fc;

    top:-120px;
    left:-120px;
}

body::after{

    background:#e0c3fc;

    bottom:-120px;
    right:-120px;
}

.glass-card{

    position:relative;

    width:450px;

    padding:42px;

    border-radius:36px;

    background:
        linear-gradient(
            135deg,
            rgba(255,255,255,0.28),
            rgba(255,255,255,0.08)
        );

    backdrop-filter:blur(28px) saturate(180%);

    -webkit-backdrop-filter:blur(28px) saturate(180%);

    border:1px solid rgba(255,255,255,0.38);

    box-shadow:
        inset 0 1px 1px rgba(255,255,255,0.45),
        inset 0 -1px 1px rgba(255,255,255,0.08),
        0 20px 50px rgba(0,0,0,0.18);

    overflow:hidden;
}

/* top shine */
.glass-card::before{

    content:"";

    position:absolute;

    top:0;
    left:0;

    width:100%;
    height:50%;

    background:
        linear-gradient(
            to bottom,
            rgba(255,255,255,0.35),
            transparent
        );

    pointer-events:none;
}

h1{

    text-align:center;

    margin-bottom:32px;

    font-size:34px;

    color:rgba(0,0,0,0.82);

    letter-spacing:-0.5px;
}

.input-group{

    margin-bottom:22px;
}

label{

    display:block;

    margin-bottom:8px;

    font-size:14px;

    font-weight:600;

    color:rgba(0,0,0,0.72);
}

input{

    width:100%;

    padding:16px 18px;

    border:none;

    outline:none;

    border-radius:20px;

    background:
        linear-gradient(
            135deg,
            rgba(255,255,255,0.32),
            rgba(255,255,255,0.08)
        );

    backdrop-filter:blur(20px);

    -webkit-backdrop-filter:blur(20px);

    border:1px solid rgba(255,255,255,0.35);

    color:#111;

    font-size:15px;

    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease,
        background 0.25s ease;
}

input:focus{

    transform:translateY(-2px);

    background:
        linear-gradient(
            135deg,
            rgba(255,255,255,0.42),
            rgba(255,255,255,0.14)
        );

    box-shadow:
        0 0 0 4px rgba(0,122,255,0.18),
        0 10px 30px rgba(0,122,255,0.12);
}

/* glass liquid button */
button{

    width:100%;

    position:relative;

    overflow:hidden;

    padding:18px;

    border:none;

    border-radius:24px;

    cursor:pointer;

    font-size:17px;

    font-weight:700;

    letter-spacing:0.4px;

    color:white;

    background:
        linear-gradient(
            135deg,
            rgba(0,122,255,0.42),
            rgba(88,86,214,0.28)
        );

    backdrop-filter:blur(30px) saturate(220%);

    -webkit-backdrop-filter:blur(30px) saturate(220%);

    border:
        1px solid rgba(255,255,255,0.32);

    box-shadow:
        inset 0 1px 1px rgba(255,255,255,0.28),
        inset 0 -1px 1px rgba(255,255,255,0.06),
        0 12px 35px rgba(0,122,255,0.18);

    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease,
        background 0.25s ease;
}

/* moving spotlight */
button::before{

    content:"";

    position:absolute;

    inset:0;

    background:
        radial-gradient(
            circle at var(--x, 50%) var(--y, 50%),
            rgba(255,255,255,0.95),
            transparent 36%
        );

    opacity:0;

    transition:opacity 0.2s ease;
}

button:hover::before{

    opacity:1;
}

button:hover{

    transform:
        translateY(-5px)
        scale(1.03);

    background:
        linear-gradient(
            135deg,
            rgba(0,122,255,0.55),
            rgba(140,82,255,0.32)
        );

    box-shadow:
        inset 0 1px 1px rgba(255,255,255,0.4),
        0 22px 50px rgba(0,122,255,0.28);
}

button span{

    position:relative;

    z-index:2;
}

.error{

    background:rgba(255,0,0,0.12);

    color:#b10020;

    padding:14px;

    border-radius:18px;

    margin-bottom:20px;

    text-align:center;

    backdrop-filter:blur(15px);
}

.register-link{

    text-align:center;

    margin-top:24px;

    font-size:14px;

    color:rgba(0,0,0,0.65);
}

.register-link a{

    color:#007aff;

    text-decoration:none;

    font-weight:700;
}

/* PASSWORD BOX */

.password-box{

    position:relative;
}

.password-box input{

    padding-right:60px;
}

.toggle-password{

    position:absolute;

    right:18px;

    top:50%;

    transform:translateY(-50%);

    cursor:pointer;

    font-size:18px;

    user-select:none;

    color:rgba(0,0,0,0.55);

    transition:0.2s;
}

.toggle-password:hover{

    color:#007aff;

    transform:
        translateY(-50%)
        scale(1.1);
}

</style>

</head>

<body>

<div class="glass-card">

    <h1>Admin Login</h1>

    <?php if($error): ?>
        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="input-group">

            <label>Email</label>

            <input
                type="email"
                name="email"
                required
            >

        </div>

        <div class="input-group">

    <label>Password</label>

    <div class="password-box">

        <input
            type="password"
            name="password"
            id="password"
            required
        >

        <span
            class="toggle-password"
            onclick="togglePassword()"
        >
            👁
        </span>

    </div>

</div>

        <button type="submit">
            <span>Login</span>
        </button>

    </form>

    <div class="register-link">

        No admin account?

        <a href="admin_register.php">
            Register
        </a>

    </div>

</div>

<script>

const btn = document.querySelector("button");

btn.addEventListener("mousemove", function(e){

    const rect = btn.getBoundingClientRect();

    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    btn.style.setProperty("--x", x + "px");
    btn.style.setProperty("--y", y + "px");
});



</script>

<script>

function togglePassword(){

    const password = document.getElementById("password");

    if(password.type === "password"){

        password.type = "text";

    } else {

        password.type = "password";
    }
}

</script>

</body>
</html>
<?php
session_start();
require 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

$error = "";

/* ================= RESEND OTP ================= */
if(isset($_POST['resend'])){

    if(!isset($_SESSION['register'])){
        $error = "Session expired.";
    } else {

        $otp = rand(100000,999999);

        $_SESSION['register']['otp'] = $otp;
        $_SESSION['register']['expire'] = time() + 300;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;

            $mail->Username = "mmufreshmart@gmail.com";
            $mail->Password = "uffyolfuleobwwsj";

            $mail->SMTPSecure = "tls";
            $mail->Port = 587;

            $mail->setFrom("mmufreshmart@gmail.com","FreshMart");
            $mail->addAddress($_SESSION['register']['email']);

            $mail->Subject = "Resend OTP";
            $mail->Body = "Your new OTP is: $otp";

            $mail->send();

        } catch(Exception $e){
            $error = "Email failed: " . $mail->ErrorInfo;
        }
    }
}

/* ================= VERIFY OTP ================= */
if(isset($_POST['verify'])){

    $otp = $_POST['otp'] ?? "";

    if(!isset($_SESSION['register'])){
        $error = "Session expired.";
    } else {

        if(time() > $_SESSION['register']['expire']){
            $error = "OTP expired.";
        }
        else if($otp == $_SESSION['register']['otp']){

            $u = $_SESSION['register'];

            $stmt = $conn->prepare("INSERT INTO customer (name,email,password,is_verified) VALUES (?,?,?,1)");
            $stmt->bind_param("sss",$u['name'],$u['email'],$u['password']);
            $stmt->execute();

            unset($_SESSION['register']);

            echo "<script>
                alert('Register successful!');
                window.location.href='login.php';
            </script>";
            exit();
        }
        else {
            $error = "Wrong OTP";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Verify OTP</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#f5f7fb;
}

.card{
    max-width:420px;
    margin:80px auto;
    padding:30px;
    border-radius:15px;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
    text-align:center;
}

.otp-box{
    width:50px;
    height:50px;
    font-size:22px;
    text-align:center;
    margin:5px;
    border:1px solid #ccc;
    border-radius:8px;
}

.timer{
    margin-top:10px;
    font-weight:bold;
    color:#e74c3c;
}
</style>

</head>

<body>

<div class="card">

<h3>Verify OTP</h3>

<?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

<form method="POST">

<div class="d-flex justify-content-center">
    <input class="otp-box" maxlength="1">
    <input class="otp-box" maxlength="1">
    <input class="otp-box" maxlength="1">
    <input class="otp-box" maxlength="1">
    <input class="otp-box" maxlength="1">
    <input class="otp-box" maxlength="1">
</div>

<input type="hidden" name="otp" id="otp">

<button class="btn btn-success mt-3 w-100" name="verify">Verify OTP</button>

</form>

<form method="POST">
    <button id="resendBtn" class="btn btn-link mt-2" name="resend" style="display:none;">
        Resend OTP
    </button>
</form>

<div class="timer">
    Resend in: <span id="count">60</span>s
</div>

</div>

<script>

/* ================= OTP INPUT ================= */
document.querySelectorAll(".otp-box").forEach((input, index) => {

    input.addEventListener("input", function(){
        let inputs = document.querySelectorAll(".otp-box");
        let otp = "";

        inputs.forEach(i => otp += i.value);
        document.getElementById("otp").value = otp;

        if(this.value && index < 5){
            inputs[index + 1].focus();
        }
    });

    /* ================= COPY PASTE SUPPORT ================= */
    input.addEventListener("paste", function(e){
        e.preventDefault();

        let paste = (e.clipboardData || window.clipboardData).getData("text");
        paste = paste.replace(/\D/g,'');

        let inputs = document.querySelectorAll(".otp-box");

        for(let i=0;i<inputs.length;i++){
            inputs[i].value = paste[i] || "";
        }

        let otp = "";
        inputs.forEach(i => otp += i.value);
        document.getElementById("otp").value = otp;
    });
});

/* ================= TIMER ================= */
let time = 60;
let btn = document.getElementById("resendBtn");
let timerBox = document.querySelector(".timer");
let count = document.getElementById("count");

function startTimer(){

    time = 60;
    btn.style.display = "none";
    timerBox.style.display = "block";
    count.innerText = time;

    let t = setInterval(() => {

        time--;
        count.innerText = time;

        if(time <= 0){
            clearInterval(t);
            timerBox.style.display = "none";
            btn.style.display = "inline-block";
        }

    },1000);
}

startTimer();

</script>

</body>
</html>
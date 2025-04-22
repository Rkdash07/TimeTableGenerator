<?php
    session_start();
    include("connection.php");
    $nameError = "";
    $passwordError = "";
    $Error = "";
    if(isset($_POST['submit']))
    {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
    if(empty($username))
    {
        $nameError = "Username Required";
    }
    else{
        $username = trim($username);
        $username = htmlspecialchars($username);
        if(!preg_match("/^[a-zA-Z ]+$/", $username))
        {
            $nameError = "<br/>* Username only accept char";
        }
    }

    if(empty($password))
    {
        $passwordError = "<br />Password Required";
    }
    else{
        if(strlen($password)<=8)
        {
            $passwordError = "<br/>* At least 8 char password";
        } else if(!preg_match("#[0-9]+#",$password)){
            $passwordError = "<br />* At least one digit ";
        }
        else if(!preg_match("#[A-Z]+#",$password)){
            $passwordError = "<br />* At least one Uppercase ";
        }
    }
    $sql = "select * from login where username = '$username' and password = '$password'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $count = mysqli_num_rows($result);

    if($count==1)
    {
        $_SESSION['username'] = $username;
        $_SESSION['logged_in'] = true;
        header("Location: Dashboard/pages/dashboard.php");
        exit;
    }
    else
    {
        $Error = "<br />Invalid Login!!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TIME TABLE</title>
    <link rel="stylesheet" href="LoginPage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="form-box">
        <h1 id="title">ADMIN LOGIN</h1>
        <div class="login-options">
                <a href="Dashboard/pages/dashboard.php" class="guest-link">
                    <strong> Guest </strong> <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            </div>
                <form action="LoginPage.php" method="post">
                    <div class="input-field">
                    <i class="fa-solid fa-user-tie"></i>
                        <input type="username" name="username" placeholder="Username">
                    </div>  
                     <div class="input-field">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" placeholder="Password">
                    </div>
                    <div class="valid-field" style="color:red; font-size:20px; margin-bottom:10px;"> 
                    <?php if($nameError) echo "<span>$nameError</span>"; ?>
                    <?php if($passwordError) echo "<span>$passwordError</span>"; ?>
                    <?php if($Error) echo "<span>$Error</span>"; ?>
                </div>
                    <div class="btn-field">
                        <button type="submit" name="submit" id="signinBtn">Login</button>
                    </div>   
                </form>
        </div> 
    </div>
</body>
</html>
<?php
// index.php is the html login form
include('./login.php'); // Includes Login Script
http_response_code(401); // set HTTP response code if session expired

if(isset($_SESSION['username'])){
header("location: ./sensorz/sqlchartz.php");
}
?>
<!DOCTYPE html>
<html>
<head>
<title>HomeSensor System Login</title>
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="main" style="float:center; width:40%;">
<div id="login">
<h2>HomeSensor Login</h2><br>
<form name="UserLoginForm" action="" method="post">
<label>UserName :</label>
<input id="name" name="username" placeholder="username" type="text">
<label>Password :</label>
<input id="password" name="password" placeholder="**********" type="password">
<input name="submit" type="submit" value=" Login ">
<span><?php echo $error; ?></span>
</form>
</div>
</div>
</body>
</html>

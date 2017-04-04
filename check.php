<?php
session_start();

//session set to expire after 30 mins - note: 30 * 60 = 1800
$expiry = 3600 ;
    if (isset($_SESSION['LAST']) && (time() - $_SESSION['LAST'] > $expiry)) {
    	http_response_code(401); // set HTTP response code if session expired
        session_unset();
        session_destroy();
	header("Location: ../index.php");
    }
$_SESSION['LAST'] = time();

include('./connection.php');

$username=$_SESSION['username'];
$access=$_SESSION['access'];

$ses_sql = mysqli_query($db,"SELECT username, access FROM login WHERE username='$username'");

$row=mysqli_fetch_array($ses_sql,MYSQLI_ASSOC);

$login_user=$row['username'];
$login_access=$row['access'];

if(!isset($username)){
mysqli_close($db); // Closing  datbase connection
header("Location: ../index.php");
}
?>

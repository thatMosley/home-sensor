<?php
define('DB_SERVER', 'localhost');  //enter your mysql database details here
define('DB_USERNAME', 'sensor_out');
define('DB_PASSWORD', 'passwordout');
define('DB_DATABASE', 'homesensor');
$db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
?>

<?php
	session_start();
	include("./connection.php"); //Establishing connection with our database

	$error = ""; //Variable for storing our errors.
	if(isset($_POST["submit"]))
	{
		if(empty($_POST["username"]) || empty($_POST["password"]))
		{
			$error = "Both fields are required.";
		}else
		{
			// Define $username and $password
			$username=$_POST['username'];
			$password=$_POST['password'];

			// To protect from MySQL injection
			$username = stripslashes($username);
			$password = stripslashes($password);
			$username = mysqli_real_escape_string($db, $username);
			$password = mysqli_real_escape_string($db, $password);
//			$password = md5($password);

			//Check username and password from database
			$sql="SELECT * FROM login WHERE username='$username' and password='$password'";
			$result=mysqli_query($db,$sql);
			$row=mysqli_fetch_array($result,MYSQLI_ASSOC);
			$access=$row['access'];
			$id=$row['id'];

			//If username and password exist in our database then create a session.
			//Otherwise echo error.

			if(mysqli_num_rows($result) == 1)
			{
				$con = mysqli_connect("localhost","sensor_in","passwordin", "homesensor");
				$sqlq = "INSERT INTO logindata (username) VALUES ('$username')";
				$retval = mysqli_query($con, $sqlq);

				$_SESSION['username'] = $username; // Initializing Session
				$_SESSION['access'] = $access;
				$_SESSION['id'] = $id;
				header("location: ./sensorz/sqlchartz.php"); // Redirecting To Other Page
			}else
			{
				$error = "Incorrect username or password.";
			}
			mysqli_close($db); // Closing MySQL connections
			mysqli_close($con);
		}
	}

?>

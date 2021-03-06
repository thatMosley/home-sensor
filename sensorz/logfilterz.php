<?php
//logfilterz.php

//start PHP session
include('../checkaccess.php');
$username=$_SESSION['username'];
$access=$_SESSION['access'];

 if(isset($_POST["from_date"], $_POST["to_date"])){
      $connect = mysqli_connect("localhost", "sensor_out", "passwordout", "homesensor") or die("Error " . mysqli_error($connect));
      $output = '';
      $query = "
           SELECT * FROM login, logindata WHERE login.username=logindata.username AND DATE(datetime) BETWEEN '".$_POST["from_date"]."' AND '".$_POST["to_date"]. "' ORDER BY datetime DESC
      ";
      function JSdate($in,$type){
    	if($type=='date'){
        	//Dates are patterned 'yyyy-MM-dd'
        	preg_match('/(\d{4})-(\d{2})-(\d{2})/', $in, $match);
    	} elseif($type=='datetime'){
        	//Datetimes are patterned 'yyyy-MM-dd hh:mm:ss'
        	preg_match('/(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2}):(\d{2})/', $in, $match);
    	}

    	$year = (int) $match[1];
    	$month = (int) $match[2] - 1; // Month conversion between indexes
    	$day = (int) $match[3];

    	if ($type=='date'){
        	return "Date($year, $month, $day)";
    	} elseif ($type=='datetime'){
        	$hours = sprintf('%02d', $match[4]);
        	$minutes = sprintf('%02d', $match[5]);
        	$seconds = sprintf('%02d', $match[6]);
        	return "$hours:$minutes:$seconds";
    	}
	  }
      $result = mysqli_query($connect, $query);
      $output .= '
           <table class="table table-bordered">
                <tr>
                               <th width="30%">Username</th>
                               <th width="30%">Access Status</th>
                               <th width="40%">Login Time</th>
                </tr>
      ';
      if(mysqli_num_rows($result) > 0)
      {
           while($row = mysqli_fetch_array($result))
           {
                $output .= '
                     <tr>
                          <td>'. $row["username"] .'</td>
                          <td>'. $row["access"] .'</td>
                          <td>'. JSdate($row["datetime"], 'datetime') .'</td>
                     </tr>
                ';
           }
      }
      else
      {
           $output .= '
                <tr>
                     <td colspan="5">No Data Found</td>
                </tr>
           ';
      }
      $output .= '</table>';
      echo $output;
      mysqli_close($connect);
 }
 ?>

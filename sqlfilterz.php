<?php
//sqlfilterz.php

//start PHP session
include('../check.php');
$username=$_SESSION['username'];
$access=$_SESSION['access'];

 if(isset($_POST["from_date"], $_POST["to_date"])){
      $connect = mysqli_connect("localhost", "sensor_out", "passwordout", "homesensor") or die("Error " . mysqli_error($connect));
      $output = '';
      $query = "
           SELECT * FROM sensordata, sensorname WHERE sensordata.sensorcode=sensorname.sensorcode AND DATE(timestamp) BETWEEN '".$_POST["from_date"]."' AND '".$_POST["to_date"]. "' ORDER BY timestamp 
      ";
      $qresult = mysqli_query($connect,$query);

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
        	$hours = (int) $match[4];
        	$minutes = (int) $match[5];
        	$seconds = (int) $match[6];
        	return "Date($year, $month, $day, $hours, $minutes, $seconds)";
    	}
	  }

	  $rows = array();
	  $table = array();

	  $table['cols'] = array (
  		array('id' => 'Sensor', 'type' => 'string'),
  		array('id' => 'Label', 'type' => 'string'),
  		array('role' => 'tooltip', 'type' => 'string'),
  		array('role' => 'style', 'type' => 'string'),
  		array('id' => 'On time', 'type' => 'date'),
  		array('id' => 'Off time', 'type' => 'date')
  	  );

	  while($res = mysqli_fetch_assoc($qresult)){
  		$result[] = $res;
	  }

	  foreach ($result as $r) {
  		$time1 = JSdate($r["timestamp"], 'datetime');
  		$time2 = JSdate($r["timestamp"], 'datetime');
		$time3 = date('H:i:s', strtotime($r['timestamp']));
  		$label = '';
  		$temp = array();
  		$temp[] = array('v' => $r['name']);
  		$temp[] = array('v' => $label);
  		$temp[] = array('v' => $time3);
  		$temp[] = array('v' => $r['colour']);
  		$temp[] = array('v' => $time1);
  		$temp[] = array('v' => $time2);
  		$rows[] = array('c' => $temp);

	  }
	  $table['rows'] = $rows;
	  echo json_encode($table, JSON_NUMERIC_CHECK);
	  $responseText = json_encode($table);
	  mysqli_close($connect);
}
else
{
alert("Please Select Date");
}
?>

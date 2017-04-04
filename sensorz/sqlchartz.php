<?php
//start PHP session
include('../check.php');
$username=$_SESSION['username'];
$access=$_SESSION['access'];
$userpermission = '';

	// hide login data link from non 'admin' access
	if($_SESSION['access'] == 'admin'){
	$userpermission = '<a href="logindataz.php"><div class="sensor-button" style="float:right; width:15%">Timeline Login Data</div></a><br />';
	}

// connect to MySQL database
$connect=mysqli_connect("localhost","sensor_out","passwordout","homesensor");
if (mysqli_connect_errno()){echo "Failed to connect to MySQL: " . mysqli_connect_error();}

// MySQL query for initial Google Timeline Chart - set for today
$query = "SELECT * FROM sensordata, sensorname WHERE sensordata.sensorcode=sensorname.sensorcode AND timestamp >= CONCAT(CURDATE(), ' 00:00:00') && timestamp < CONCAT(CURDATE(), ' 23:59:59') ORDER BY timestamp";
$qresult = mysqli_query($connect,$query);

// function for MySQL to Javascript Date
// echo JSdate('2014-11-06','date') - Returns: Date(2014, 10, 06)
// echo JSdate('2014-11-06 16:56:20','datetime') - Returns: Date(2014, 10, 06, 16, 56, 20)
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

// set initial values of MySQL array for JSON file output
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
$jsonTable = json_encode($table);
mysqli_close($connect);
?>
<!doctype html>
<html lang = "en">
  <head>
    <meta charset = "utf-8">
    <title>HomeSensor Data System</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
	// date function for displaying the current date & time at the top of the html page
	function startTime() {
    		var today = new Date();
    		var cd = today.getDate();
    		var dn = getDayName(today.getDay());
    		var mn = getMonthName(today.getMonth());
    		var y = today.getFullYear();
    		var h = today.getHours();
    		var m = today.getMinutes();
    		var s = today.getSeconds();
    		cd = checkTime(cd);
		h = checkTime(h);
    		m = checkTime(m);
    		s = checkTime(s);
    		document.getElementById('currenttime').innerHTML =
    		dn + ", " + cd + "-" + mn + "-" + y + "   " + h + ":" + m + ":" + s;
    		var t = setTimeout(startTime, 500);
	}
	function checkTime(i) {
    		if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
    		return i;
	}
	function getMonthName(month) {
    		var ar = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
    		return ar[month];
	}
	function getDayName(day) {
    		var ar1 = new Array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
    		return ar1[day];
	}
	</script>
	<script type="text/javascript">

	// load google timeline charts API
	google.charts.load('current', {'packages': ['timeline']});
	google.charts.setOnLoadCallback(drawChart);

	var selectDate = new Date();

	// function to draw google chart
	function drawChart() {
  		var dataTable = new google.visualization.DataTable(<?php echo $jsonTable; ?>);
  		dataTable.insertRows(0, [
  			['PIR 1', '', '', 'color: lightgrey', selectDate, selectDate],
			['PIR 2', '', '', 'color: lightgrey', selectDate, selectDate],
  			['Front Door', '', '', 'color: lightgrey', selectDate, selectDate],
  			['PIR B', '', '', 'color: lightgrey', selectDate, selectDate],
  			['PIR L', '', '', 'color: lightgrey', selectDate, selectDate],
            ['D Open', '', '', 'color: lightgrey', selectDate, selectDate],
            ['D Close', '', '', 'color: lightgrey', selectDate, selectDate]
		]);
  		var container = document.getElementById('example');
  		var chart = new google.visualization.Timeline(container);
  		var options = {
  			tooltip: {isHtml: false},
  			legend: 'none',
			showCustomTime: true,
			crosshair: { trigger: 'both', orientation: 'vertical' },
  			height: 500,
    		hAxis: {
      		minValue: selectDate.setHours(00, 00, 00),
      		maxValue: selectDate.setHours(23, 59, 59)
//		ticks: {[[12,00,00],[18,00,00]]}
    		}
  		};
  		chart.draw(dataTable, options);
	}
	</script>
  </head>
	<body onload="startTime()">
	       <br /><br />
           <div class="container" style="width:900px;">
		<a href="sensor_datac.php"><div class="sensor-button" style="float:left; width:25%">Timeline Sensor Data</div></a>
		<?php if($userpermission){echo $userpermission;} ?>
           	<br /><div style="font-weight: bold" id="currenttime" ></div>
                <h2 align="center">HomeSensor Timeline Chart</h2><br />
                <div class="col-md-3" style="float:left; width:17%;">
                     <input type="text" name="from_date" id="from_date" class="form-control" placeholder="" />
                </div>
                <div class="col-md-5" style="float:left; width:15%;">
                    <input type="button" name="filter" id="filter" value="Refresh" class="btn btn-info" />
		</div>
                <div class="col-md-5" style="float:right; width:12%;">
                    <input type="button" name="logout" id="logout" value="Logout" class="btn btn-info" onclick="window.location='../logout.php';" />
                </div>
                <div style="clear:both"></div>
                <br />
	  <div id="example" ></div>
	  </div>
   	</body>
</html>
 <script>

 	// function initiates after html is loaded and ready
    $(document).ready(function(){

	// set up datepicker format and datepicker
        $.datepicker.setDefaults({
            dateFormat: 'yy-mm-dd'
        });

        $(function(){
            $("#from_date").datepicker();
            $("#from_date").datepicker("setDate", new Date());
        });

	// function initiates when filter button is clicked
        $('#filter').click(function(){

	$.ajaxSetup({ 
	  statusCode : {
	    401 : function () {
	      alert ('Sorry, previous Login session expired! - please renew Login.');
	      window.location = "../index.php";
	    }
	  }
	});

            var from_date = $('#from_date').val();
            var to_date = $('#from_date').val();
            var fromDate = new Date(from_date); // MySQL date to Javascript date format

            if(from_date != '' && to_date != ''){
            	//AJAX redraws Google chart with new date from datepicker
                $.ajax({
                    url: 'sqlfilterz.php',
                    method: 'POST',
                    data: {from_date:from_date, to_date:to_date},
                    dataType: 'json',
                    success: function(json){

					var dataTable = new google.visualization.DataTable(json);
  					dataTable.insertRows(0, [
  						['PIR 1', '', '', 'color: lightgrey', fromDate, fromDate],
						['PIR 2', '', '', 'color: lightgrey', fromDate, fromDate],
  						['Front Door', '', '', 'color: lightgrey', fromDate, fromDate],
  						['PIR B', '', '', 'color: lightgrey', fromDate, fromDate],
  						['PIR L', '', '', 'color: lightgrey', fromDate, fromDate],
                        ['D Open', '', '', 'color: lightgrey', fromDate, fromDate],
                        ['D Close', '', '', 'color: lightgrey', fromDate, fromDate]
 					]);

					var options = {
						tooltip: {isHtml: false},
						legend: 'none',
						showCustomTime: true,
						crosshair: { trigger: 'both', orientation: 'vertical' },
  						height: 500,
    					hAxis: {
      					minValue: fromDate.setHours(00, 00, 00),
      					maxValue: fromDate.setHours(23, 59, 59)
    					}
  					};

  					var chart = new google.visualization.Timeline(document.getElementById('example'));
					chart.draw(dataTable, options);
					}
				});
			}

    		else

            {
            alert("Please Select Date"); // if date is missing alert box
            }

		});
	});
 </script>


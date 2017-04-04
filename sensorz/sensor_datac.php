<?php
//start PHP session
include('../check.php');
$username=$_SESSION['username'];
$access=$_SESSION['access'];
$userpermission = '';

        // hide link from non 'admin' access
        if($_SESSION['access'] == 'admin'){
        $userpermission = '<a href="logindataz.php"><div class="sensor-button" style="float:right; width:25%">Timeline Login Data</div></a><br />';
        }

 $connect = mysqli_connect("localhost", "sensor_out", "passwordout", "homesensor") or die("Error " . mysqli_error($conn));
 $query = "SELECT * FROM sensordata, sensorname WHERE sensordata.sensorcode=sensorname.sensorcode AND timestamp >= CONCAT(CURDATE(), ' 00:00:00') && timestamp < CONCAT(CURDATE(), ' 23:59:59') ORDER BY timestamp DESC";
 $result = mysqli_query($connect, $query);

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
        $hours = sprintf('%02d', (int) $match[4]);
        $minutes = sprintf('%02d', (int) $match[5]);
        $seconds = sprintf('%02d', (int) $match[6]);
        return "$hours:$minutes:$seconds";
    }
}
mysqli_close($connect);
 ?>
 <!DOCTYPE html>
 <html>
      <head>
           <title>HomeSensor Data System</title>
           <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
           <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
           <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
           <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
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
      </head>
      <body onload="startTime()">
           <br /><br />
           <div class="container" style="width:600px;">
           		<a href="sqlchartz.php"><div class="sensor-button" style="float:left; width:25%;">Timeline Chart</div></a>
           		<?php if($userpermission){echo $userpermission;} ?>
           		<br /><div style="font-weight: bold" id="currenttime" ></div>
                <h2 align="center">HomeSensor Timeline Sensor Data</h2><br />
                <div class="col-md-3" style="float:left; width:30%;">
                     <input type="text" name="from_date" id="from_date" class="form-control" placeholder="" />
                </div>
                <div class="col-md-5" style="float:left; width:20%;">
                     <input type="button" name="filter" id="filter" value="Refresh" class="btn btn-info" />
                </div>
                <div class="col-md-5" style="float:right; width:18%;">
                    <input type="button" name="logout" id="logout" value="Logout" class="btn btn-info" onclick="window.location='../logout.php';" />
                </div>
                <div style="clear:both"></div>
                <br />
                <div id="order_table">
                     <table class="table table-bordered">
                          <tr>
                               <th width="30%">Sensor Code</th>
                               <th width="30%">Sensor Name</th>
                               <th width="40%">Trigger Time</th>
                          </tr>
                     <?php
                     while($row = mysqli_fetch_array($result))
                     {
                     ?>
                          <tr>
                               <td><?php echo $row["sensorcode"]; ?></td>
                               <td><?php echo $row["name"]; ?></td>
                               <td><?php echo JSdate($row["timestamp"], 'datetime'); ?></td>
                          </tr>
                     <?php
                     }
                     ?>
                     </table>
                </div>
           </div>
      </body>
 </html>
 <script>
      $(document).ready(function(){
           $.datepicker.setDefaults({
                dateFormat: 'yy-mm-dd'
           });

           $(function(){
                $("#from_date").datepicker();
                $("#from_date").datepicker("setDate", new Date());
           });

           $('#filter').click(function(){

                $.ajaxSetup({
                  statusCode : {
                    401 : function () {
                      alert ('Sorry, previous Login session has expired! - please renew Login.');
                      window.location = "../index.php";
                    }
                  }
                });

                var from_date = $('#from_date').val();
                var to_date = $('#from_date').val();
                if(from_date != '' && to_date != ''){
                     $.ajax({
                          url:"filterc.php",
                          method:"POST",
                          data:{from_date:from_date, to_date:to_date},
                          success:function(data)
                          {
                               $('#order_table').html(data);
                          }
                     });
                }
                else
                {
                     alert("Please Select Date");
                }
           });
      });
 </script>


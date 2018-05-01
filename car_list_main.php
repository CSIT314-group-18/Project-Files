<?php
// Include config file
require_once 'config.php';

// Initialize the session
session_start();

//get all the cars
$textArea = $car_id = $model = $manufacturer = $transmission = $odometer = "";
$getCarSql = "SELECT car_id, model, manufacturer, transmission, odometer FROM car";
if($getCarSqlStmt = mysqli_prepare($link, $getCarSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getCarSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getCarSqlStmt);
		mysqli_stmt_bind_result($getCarSqlStmt, $car_id, $model, $manufacturer, $transmission, $odometer);
		
		//populate the html text field variable
		while(mysqli_stmt_fetch($getCarSqlStmt)){
			$textArea .= "<ul style='list-style-type:none'><li>" . $model . "</li><li>" . $manufacturer . "</li><li>" . $transmission . "</li>";
			$textArea .= "<li>" . $odometer . '<br>
			<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="hidden" name="this_car_id" value="' . $car_id . '">
			<input type="submit" name="seeCar" class="btn btn-primary" value="View More Info"></form></div></li></ul>';
			$textArea .= '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="hidden" name="this_car_id" value="' . $car_id . '">
			<input type="submit" name="requestBooking" class="btn" value="Request This Car">
			</form><br><br>';
		}
		$textArea .= "<br><br>";
	}
}
// Close statement
mysqli_stmt_close($getCarSqlStmt);

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST["seeCar"])){
		$this_car_id = trim($_POST["this_car_id"]);
		$_SESSION['this_car_id'] = $this_car_id;   
		header("location: /view_car.php");
	}
	
	if(isset($_POST["requestBooking"])){
		$this_car_id = trim($_POST["this_car_id"]);
		$_SESSION['this_car_id'] = $this_car_id;   
		header("location: /request_conf.php");
	}
}	



// Close connection
mysqli_close($link);
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; text-align: center; }
    </style>
</head>
<body>
    <div class="page-header">
        <h1>Our current available cars:</h1>
    </div>
	<div style="position: absolute; left: 10px; top: 10px; border: 3px;">
	<p><a href="welcome.php" class="btn">See your Account</a></p>
	</div>
	
	<p><?php echo $textArea; ?></p>
	
	<div style="position: absolute; left: 10px; bottom: 10px; border: 3px;">
	<p><a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a></p>
	</div>
</body>
</html>
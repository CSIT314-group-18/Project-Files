<?php
// Include config file
require_once 'config.php';

// Initialize the session
session_start();

// If session variable is not set it will redirect to login page
if(!isset($_SESSION['this_car_id']) || empty($_SESSION['this_car_id'])){
  header("location: car_list_main.php");
  exit;
}else{
	$car_id = $_SESSION['this_car_id'];
}

//get the car that they clicked
$textArea = $model = $manufacturer = $transmission = $odometer = "";
$getCarSql = "SELECT * FROM car WHERE car_id = " . $car_id;
if($getCarSqlStmt = mysqli_prepare($link, $getCarSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getCarSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getCarSqlStmt);
		mysqli_stmt_bind_result($getCarSqlStmt, $car_id, $image, $model, $manufacturer, $transmission, $odometer, $car_owner_users_id);
		
		//populate the html text field variable
		while(mysqli_stmt_fetch($getCarSqlStmt)){
			$textArea .= "<div class='page-header'>
			<h1>" . $model . "</h1></div>
			<ul style='list-style-type:none'><li>" . $manufacturer . "</li><li>" . $transmission . "</li>
			<li>" . $odometer . '</li></ul>';
		}
	}
}
// Close statement
mysqli_stmt_close($getCarSqlStmt);

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
	
	//to put through an actual request
	if(isset($_POST["reqConf"])){
		
		// Set parameters
		$status = "requested";
		$param_startdate = trim($_POST['startdate']);
		$param_enddate = trim($_POST['enddate']);
		
		if(empty($param_startdate || $param_startdate)){
			echo "Please choose dates for your booking.";
			
		}else{

			// Prepare an insert statement
			$sql = "INSERT INTO reservation (status, startdate, enddate, renter, rentee, car_id) VALUES (?, ?, ?, ?, ?, ?)";
			
			if($stmt = mysqli_prepare($link, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "sssiii", $status, $param_startdate, $param_enddate, $car_owner_users_id, $_SESSION['users_id'], $car_id);
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt)){
					/* store result */
					mysqli_stmt_store_result($stmt);
					header("location: /welcome.php");
				} else{
					echo "Oops! Something went wrong. Please try again later.";
				}
			}
			// Close statement
			mysqli_stmt_close($stmt);
		}
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
	<div style="position: absolute; left: 10px; top: 10px; border: 3px;">
	<p><a href="welcome.php" class="btn">See your Account</a></p>
	</div>
	
	<p><?php echo $textArea; ?></p>
	
	<form action= "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
		Start date: <input type="date" min="<?php echo date("Y-m-d"); ?>" name="startdate"> <br>
		End date: <input type="date" min="<?php echo date("Y-m-d"); ?>" name="enddate"> <br>
		<input type="submit" name="reqConf" class="btn btn-primary" value="Confirm Request">
	</form>
	
	<div style="position: absolute; left: 10px; bottom: 10px; border: 3px;">
	<p><a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a></p>
	</div>
</body>
</html>
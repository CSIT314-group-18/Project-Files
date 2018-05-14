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
$getCarSql = "SELECT car_id, image, model, manufacturer, transmission, odometer, users_id FROM car WHERE car_id = " . $car_id;
if($getCarSqlStmt = mysqli_prepare($link, $getCarSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getCarSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getCarSqlStmt);
		mysqli_stmt_bind_result($getCarSqlStmt, $car_id, $image, $model, $manufacturer, $transmission, $odometer, $car_owner_users_id);
		
		//populate the html text field variable
		while(mysqli_stmt_fetch($getCarSqlStmt)){
			
			//get a cars picture
			$target_file = "car_image/" . $car_id . ".*";
			$target_file = glob($target_file);
			
			// Check if file already exists
			if (!empty($target_file)) {
				$prelimPhotoArea = "<img src='" . current($target_file) . "' alt='" . $car_id . "' style='width:200px;'>";
			}else{
				$prelimPhotoArea = "";
			}
			
			$textArea .= "<div class='page-header'>
			<h1>" . $model . "</h1></div>
			<ul style='list-style-type:none'><li>" . $prelimPhotoArea . "</li><li>" . $manufacturer . "</li><li>" . $transmission . "</li>
			<li>" . $odometer . '</li></ul>';
		}
	}
}
// Close statement
mysqli_stmt_close($getCarSqlStmt);

//Show when this car is booked:

$showBookedArea = $showAmountArea = "";
$sql = "SELECT startdate, enddate FROM reservation WHERE car_id = " . $car_id;
if($stmt = mysqli_prepare($link, $sql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($stmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($stmt);
		
		if(mysqli_stmt_num_rows($stmt) != 0){
			$showBookedArea = "<label>This car is booked from:</label><ul style='list-style-type:none'>";
        }
		
		mysqli_stmt_bind_result($stmt, $startdate, $enddate);
		
		//populate the html text field variable
		while(mysqli_stmt_fetch($stmt)){
			$startdate = substr($startdate, 0, -8);
			$enddate = substr($enddate, 0, -8);
			
			$startdate = strtotime($startdate);
			$enddate = strtotime($enddate);
			
			
			$startdate = date('D d/m/Y', $startdate);
			$enddate = date('D d/m/Y', $enddate);
			
			$showBookedArea .= '<li>' . $startdate .' <b>until</b> ' . $enddate .'</li>';
		}
		$showBookedArea .= '</ul>';
	}
}
// Close statement
mysqli_stmt_close($stmt);

//initialise variables
$param_startdate = $param_enddate = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
	
	
	//show how much it would cost
	if(isset($_POST["showAmount"])){
		$amount = 0;
		$param_startdate = trim($_POST['startdate']);
		$param_enddate = trim($_POST['enddate']);
		$days = 0;
		
		//add the money based on how many days they booked
		$temp_param_startdate = strtotime($param_startdate);
		$temp_param_enddate = strtotime($param_enddate);
		for($j=$temp_param_startdate; $j<=$temp_param_enddate; $j+=86400){
			$amount += 50.0;
			$days++;
		}
		
		$showAmountArea = "To book this car for " . $days . " days, it would cost $" . $amount . ". ";
	}
	
	
	//to put through an actual request
	if(isset($_POST["reqConf"])){
		
		// Set parameters
		$status = "requested";
		$amount = 50.0;
		$param_startdate = trim($_POST['startdate']);
		$param_enddate = trim($_POST['enddate']);
		
		//add the money based on how many days they booked
		$temp_param_startdate = strtotime($param_startdate);
		$temp_param_enddate = strtotime($param_enddate);
		for($j=$temp_param_startdate; $j<=$temp_param_enddate; $j+=86400){
			$amount += 50.0;
		}
		
		
		if(empty($param_startdate || $param_enddate)){
			echo "Please choose dates for your booking.";
		} else if(strtotime($param_startdate) > strtotime($param_enddate)){
			echo "Your start date must be before your end date.";
		}else{
			
			
			//check if this car belongs to them
			$car_check_id = 0;
			$carCheckSql = "SELECT car_id FROM car WHERE users_id = '" . $_SESSION['users_id'] . "'";
			if($carCheckSqlStmt = mysqli_prepare($link, $carCheckSql)){

				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($carCheckSqlStmt)){

					// Store result, print it to the variable
					mysqli_stmt_store_result($carCheckSqlStmt);
					mysqli_stmt_bind_result($carCheckSqlStmt, $car_check_id);

					//check if the owners cars match the id of this one
					while(mysqli_stmt_fetch($carCheckSqlStmt)){
						if($car_id == $car_check_id){
							echo '<div style="position: absolute; left: 10px; top: 10px; border: 3px;">
							<p><a href="/car_list_main.php" class="btn">See All Cars</a></p>
							</div>';
							exit("You already own this car, so you don't need to rent it out.");
						}
						
					}
				}
			}
			// Close statement
			mysqli_stmt_close($carCheckSqlStmt);
			
			$alreadyBooked = false;
			
			$sql = "SELECT startdate, enddate FROM reservation WHERE car_id = " . $car_id;
			if($stmt = mysqli_prepare($link, $sql)){

				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt)){

					// Store result, print it to the variable
					mysqli_stmt_store_result($stmt);

					mysqli_stmt_bind_result($stmt, $startdate, $enddate);

					//populate the html text field variable
					while(mysqli_stmt_fetch($stmt)){
						$startdate = strtotime($startdate);
						$enddate = strtotime($enddate);
						$temp_param_startdate = strtotime($param_startdate);
						$temp_param_enddate = strtotime($param_enddate);
						
						for($i=$startdate; ($i<=$enddate) &&$alreadyBooked == false; $i+=86400){
							for($j=$temp_param_startdate; ($j<=$temp_param_enddate)&& $alreadyBooked == false; $j+=86400){
								if($i == $j){
									echo "You can't book those dates, because this car is already booked at " . date('d/m/Y', $j);
									$alreadyBooked = true;
								}
							}
						}
					}
				}
			}
			// Close statement
			mysqli_stmt_close($stmt);
			
			
			if($alreadyBooked == false){
				// Prepare an insert statement
				$sql = "INSERT INTO reservation (status, startdate, enddate, owner, renter, car_id) VALUES (?, ?, ?, ?, ?, ?)";

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
				
				
				// Prepare an insert statement to payment
				$sql = "INSERT INTO payment (payment_status, total_fee, owner, renter) VALUES (?, ?, ?, ?)";

				if($stmt = mysqli_prepare($link, $sql)){
					// Bind variables to the prepared statement as parameters
					mysqli_stmt_bind_param($stmt, "sdii", $status, $amount, $car_owner_users_id, $_SESSION['users_id']);
					
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
		Start date: <input type="date" min="<?php echo date("Y-m-d"); ?>" name="startdate" value="<?php echo $param_startdate; ?>"> <br>
		End date: <input type="date" min="<?php echo date("Y-m-d"); ?>" name="enddate" value="<?php echo $param_enddate; ?>"> <br>
		<input type="submit" name="showAmount" class="btn" value="Estimate Cost">
		<input type="submit" name="reqConf" class="btn btn-primary" value="Confirm Request">
	</form>
	<p><?php echo $showAmountArea; ?></p>
	<div style="position: absolute; left: 10px;">
		<?php echo $showBookedArea; ?>
	</div>
	
	<div style="position: absolute; left: 10px; bottom: 10px; border: 3px;">
	<p><a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a></p>
	</div>
</body>
</html>
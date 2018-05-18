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
$addressArea = $street = $suburb = $postcode = $city = $country = $textArea = $model = 
$manufacturer = $transmission = $rego = $odometer = $colour = $engine_type = $drive_layout = 
$body_type = $seats = $doors = $year = $temp_days_na = "";
$getCarSql = "SELECT car_id, registration, model, manufacturer, transmission, colour, engine_type, drive_layout, body_type, seats, doors, year, odometer, days_na, users_id FROM car WHERE car_id = " . $car_id;
if($getCarSqlStmt = mysqli_prepare($link, $getCarSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getCarSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getCarSqlStmt);
		mysqli_stmt_bind_result($getCarSqlStmt, $car_id, $rego, $model, $manufacturer, $transmission, $colour, $engine_type, $drive_layout, $body_type, $seats, $doors, $year, $odometer, $temp_days_na, $car_owner_users_id);
		
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
			
			
			// Prepare an select statement to get the location id of the user who owns this car
			$sql = "SELECT location_id FROM users WHERE users_id = ?";
			
			if($stmt = mysqli_prepare($link, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "i", $car_owner_users_id);
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt)){
					/* store result */
					mysqli_stmt_store_result($stmt);
					mysqli_stmt_bind_result($stmt, $temp_location_id);
					mysqli_stmt_fetch($stmt);
				} else{
					echo "Oops! Something went wrong. Please try again later.";
				}
			}
			// Close statement
			mysqli_stmt_close($stmt);
			
			$getLocSql = "SELECT street, suburb, postcode, city, country FROM location WHERE location_id = " . $temp_location_id;
			if($getLocSqlStmt = mysqli_prepare($link, $getLocSql)){
	
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($getLocSqlStmt)){
			
					// Store result, print it to the variable
					mysqli_stmt_store_result($getLocSqlStmt);
					mysqli_stmt_bind_result($getLocSqlStmt, $street, $suburb, $postcode, $city, $country);
					mysqli_stmt_fetch($getLocSqlStmt);
					$addressArea = $street . ", " . $suburb . ", " . $postcode . ", " . $city . ", " . $country;
				}
			}
			// Close statement
			mysqli_stmt_close($getLocSqlStmt);
			
			//parse the days variable into something checkable
			$days_array = explode(",", $temp_days_na);
			$showDayArea = $showDayParam = "";
			foreach ($days_array as $day){
				$info_array = explode("-", $day);
				if($info_array[1] == "unchecked") $showDayParam .= $info_array[0] . "<br>";
			}
			
			if(!empty($showDayParam)){
				$showDayArea = "<br><br><h4>The Owner of this car has specified that it can't be booked<br>on the following days:<h4><h5>" . $showDayParam . "</h5>";
			}
			
			$textArea .= "<div class='page-header'>
			<h1>" . $year . " " . $model . "</h1><h4>This car will be picked up and dropped off from " . $addressArea . "</h4>
			" . $showDayArea . "</div>
			<ul style='list-style-type:none'><li>" . $prelimPhotoArea . "</li><li>" . $manufacturer . "</li><li>" . $transmission . "</li><li>" . $colour . "</li>
			<li>" . $engine_type . "</li><li>" . $drive_layout . "</li><li>" . $body_type . "</li><li>" . $seats . " seats</li>
			<li>" . $doors . " doors</li><li>Odometer: " . $odometer . 'km</li></ul>';
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
			
			if($enddate > strtotime(date("Y-m-d"))){
			
			$startdate = date('D d/m/Y', $startdate);
			$enddate = date('D d/m/Y', $enddate);
			
			
			$showBookedArea .= '<li>' . $startdate .' <b>until</b> ' . $enddate .'</li>';
			}
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
			
			
			//parse the days variable into something checkable
			$days_array = explode(",", $temp_days_na);
			$day_checker = array();
			foreach ($days_array as $day){
				$info_array = explode("-", $day);
				if($info_array[1] == "unchecked")array_push($day_checker, $info_array[0]);
			}
			
			
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
							exit("<h3>You already own this car, so you don't need to rent it out.</h3>");
						}
						
					}
				}
			}
			// Close statement
			mysqli_stmt_close($carCheckSqlStmt);
			
			$alreadyBooked = false;
			
			
			
			//check against any dates it's already booked at
			$sql = "SELECT startdate, enddate FROM reservation WHERE reservation_status NOT IN ('declined') AND car_id = " . $car_id;
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
								
								//check against the days allowed 
								foreach($day_checker as $day){
									if(date('l', $j) == $day){
										echo "Your attempted booking lies on " . $day . ", a day that the owner has specified can't be used.";
										$alreadyBooked = true;
									}
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
				$sql = "INSERT INTO reservation (reservation_status, startdate, enddate, owner, renter, car_id) VALUES (?, ?, ?, ?, ?, ?)";

				if($stmt = mysqli_prepare($link, $sql)){
					// Bind variables to the prepared statement as parameters
					mysqli_stmt_bind_param($stmt, "sssiii", $status, $param_startdate, $param_enddate, $car_owner_users_id, $_SESSION['users_id'], $car_id);
					
					// Attempt to execute the prepared statement
					if(mysqli_stmt_execute($stmt)){
						/* store result */
						mysqli_stmt_store_result($stmt);
					} else{
						echo "Oops! Something went wrong. Please try again later.";
					}
				}
				// Close statement
				mysqli_stmt_close($stmt);
				
				
				
				//get the reservation id we just made
				$temp_res_id = 0;
				$sql = "SELECT reservation_id FROM reservation WHERE startdate = ? AND enddate = ? AND owner = ? AND renter = ? AND car_id = ?";
				if($stmt = mysqli_prepare($link, $sql)){
					
					mysqli_stmt_bind_param($stmt, "ssiii", $param_startdate, $param_enddate, $car_owner_users_id, $_SESSION['users_id'], $car_id);
					
					// Attempt to execute the prepared statement
					if(mysqli_stmt_execute($stmt)){
						
						// Store result, print it to the variable
						mysqli_stmt_store_result($stmt);
						
						mysqli_stmt_bind_result($stmt, $temp_res_id);
						echo $temp_res_id;
						
						//populate the html text field variable
						mysqli_stmt_fetch($stmt);
					}
				}
				// Close statement
				mysqli_stmt_close($stmt);
				
				$status = "not paid";
				
				// Prepare an insert statement to payment
				$sql = "INSERT INTO payment (payment_status, total_fee, owner, renter, reservation_id) VALUES (?, ?, ?, ?, ?)";

				if($stmt = mysqli_prepare($link, $sql)){
					// Bind variables to the prepared statement as parameters
					mysqli_stmt_bind_param($stmt, "sdiii", $status, $amount, $car_owner_users_id, $_SESSION['users_id'], $temp_res_id);
					
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
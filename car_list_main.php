<?php
// Include config file
require_once 'config.php';

// Initialize the session
session_start();


$locationSearchString = $carTypeSearchString = $userSearchString = "";
$emptyLocationArray = $emptyCarTypeArray = true;
$users_id_array = array();
$tempUser = $tempCar = 0;

$Psql = "SELECT users_id FROM users";

if($Pstmt = mysqli_prepare($link, $Psql)){
	// Bind variables to the prepared statement as parameters
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($Pstmt)){
		/* store result */
		mysqli_stmt_store_result($Pstmt);
		mysqli_stmt_bind_result($Pstmt, $tempUser);
		
		$userSearchString = " users_id IN (";
		while(mysqli_stmt_fetch($Pstmt)){
			$userSearchString .= $tempUser . ", ";
		}
		
		$userSearchString = substr($userSearchString, 0, -2);
		$userSearchString .= ")";
		
	} else{
		echo "Oops! Something went wrong. Please try again later.";
	}
}
// Close statement
mysqli_stmt_close($Pstmt);

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
	
	if(isset($_POST["search"])){
		$street = trim($_POST["street"]);
		$suburb = trim($_POST["suburb"]);
		$postcode = trim($_POST["postcode"]);
		$city = trim($_POST["city"]);
		$country = trim($_POST["country"]);
		
		$locationArray = array(
			"street" => $street,
			"suburb" => $suburb,
			"postcode" => $postcode,
			"city" => $city,
			"country" => $country,
		);
		
		$emptyLocationArray = true;
		$locationSearchString = " WHERE";
		
		foreach ($locationArray as $key => $value) {
			if(!empty($value)){
				$locationSearchString .= " " . $key . " = '" . $value . "' AND";
				$emptyLocationArray = false;
			}
		}
		
		$locationSearchString = substr($locationSearchString, 0, -4);
		
		if($emptyLocationArray == true){
			$locationSearchString = "";
		}
		
		
		$transmission = $manufacturer = $odometerRange = "";
		
		//initialising everything to do with the car type search
		$emptyCarTypeArray = true;
		$carTypeSearchString = " WHERE";
		$odo1 = $odo2 = 0;
		if(isset($_POST["manufacturer"]))$manufacturer = trim($_POST["manufacturer"]);
		if(isset($_POST["transmission"]))$transmission = trim($_POST["transmission"]);
		if(isset($_POST["odometer"]))$odometerRange = trim($_POST["odometer"]);;
		
		$model = trim($_POST["model"]);
		
		
		if($odometerRange == "1"){
			$odo1 = 0;
			$odo2 = 999;
		}else if($odometerRange == "2"){
			$odo1 = 1000;
			$odo2 = 4999;
		}else if($odometerRange == "3"){
			$odo1 = 5000;
			$odo2 = 19999;
		}else if($odometerRange == "4"){
			$odo1 = 20000;
			$odo2 = 1000000;
		}
		
		
		
		$carTypeArray = array(
			"manufacturer" => $manufacturer,
			"model" => $model,
			"transmission" => $transmission,
		);
		
		$emptyCarTypeArray = true;
		$carTypeSearchString = " WHERE";
		
		if(!empty($odometerRange)){
			$carTypeSearchString .= " (odometer BETWEEN " . $odo1 . " AND " . $odo2 . ") AND";
			$emptyCarTypeArray = false;
		}
		
		foreach ($carTypeArray as $key => $value) {
			if(!empty($value)){
				$carTypeSearchString .= " " . $key . " = '" . $value . "' AND";
				$emptyCarTypeArray = false;
			}
		}
		$carTypeSearchString = substr($carTypeSearchString, 0, -4);
		
		
		
		if($emptyCarTypeArray == true){
			$carTypeSearchString = "";
		}
		
		//initialise the arrays that will hold all the cars that are needed
		$lbCarIdArray = $ctbCarIdArray = array();
		
		//get all the cars from the location part 
		$Psql = "SELECT users_id FROM location" . $locationSearchString;

			if($Pstmt = mysqli_prepare($link, $Psql)){
				// Bind variables to the prepared statement as parameters
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($Pstmt)){
					/* store result */
					mysqli_stmt_store_result($Pstmt);
					mysqli_stmt_bind_result($Pstmt, $tempUser);
					
					//while there are still location based users to iterate through, find their car id's
					while(mysqli_stmt_fetch($Pstmt)){
						$sql = "SELECT car_id FROM car WHERE users_id = ?";
			
						if($stmt = mysqli_prepare($link, $sql)){
							// Bind variables to the prepared statement as parameters
							mysqli_stmt_bind_param($stmt, "i", $tempUser);
							
							// Attempt to execute the prepared statement
							if(mysqli_stmt_execute($stmt)){
								/* store result */
								mysqli_stmt_store_result($stmt);
								mysqli_stmt_bind_result($stmt, $tempCar);
								while(mysqli_stmt_fetch($stmt)){
									array_push($lbCarIdArray, $tempCar);
								}
							} else{
								echo "Oops! Something went wrong. Please try again later.";
							}
						}
						// Close statement
						mysqli_stmt_close($stmt);
						
					}
					
				} else{
					echo "Oops! Something went wrong. Please try again later.";
				}
			}
		// Close statement
		mysqli_stmt_close($Pstmt);
		
		//get all the cars from the preference part
		$sql = "SELECT car_id FROM car" . $carTypeSearchString;
			
		if($stmt = mysqli_prepare($link, $sql)){
			
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($stmt)){
				/* store result */
				mysqli_stmt_store_result($stmt);
				mysqli_stmt_bind_result($stmt, $tempCar);
				while(mysqli_stmt_fetch($stmt)){
					array_push($ctbCarIdArray, $tempCar);
				}
			} else{
				echo "Oops! Something went wrong. Please try again later.";
			}
		}
		// Close statement
		mysqli_stmt_close($stmt);
		
		
		
		$foundCar = false;
		$userSearchString = " car_id IN (";
		
		//see if there are cars in both arrays
		
		foreach ($lbCarIdArray as $lbValue) {
			foreach ($ctbCarIdArray as $cbtValue) {
				if($lbValue == $cbtValue){
					$userSearchString .= $lbValue . ", ";
					$foundCar = true;
				}
			}
		}	
		
		
		if($foundCar == false){
			$userSearchString = " car_id IN (0)";
		}else{
			$userSearchString = substr($userSearchString, 0, -2);
			$userSearchString .= ")";
		}
		
	}
	
	
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

//get all the cars
$textArea = $model = $manufacturer = $transmission = $street = $suburb = $postcode = $city = $country = "";
$car_id = $temp_users_id = $temp_location_id = $odometer = 0;
$getCarSql = "SELECT car_id, model, manufacturer, transmission, odometer, users_id FROM car WHERE status = 'listed' AND" . $userSearchString;
if($getCarSqlStmt = mysqli_prepare($link, $getCarSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getCarSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getCarSqlStmt);
		mysqli_stmt_bind_result($getCarSqlStmt, $car_id, $model, $manufacturer, $transmission, $odometer, $temp_users_id);
		
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
				mysqli_stmt_bind_param($stmt, "i", $temp_users_id);
				
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
				}
			}
			// Close statement
			mysqli_stmt_close($getLocSqlStmt);
			
			$textArea .= "<div class='carArea'><ul style='list-style-type:none'><li>" . $prelimPhotoArea . "</li><li>" . $street . ", " . $suburb . 
			", " . $postcode . ", " . $city . ", " . $country . "<br><br></li><li>" . $model . "</li><li>" . $manufacturer . "</li><li>" . $transmission . "</li>";
			$textArea .= "<li>" . $odometer . '<br>
			<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="hidden" name="this_car_id" value="' . $car_id . '">
			<input type="submit" name="seeCar" class="btn btn-primary" value="View More Info"></form></div></li></ul>';
			$textArea .= '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="hidden" name="this_car_id" value="' . $car_id . '">
			<input type="submit" name="requestBooking" class="btn" value="Request This Car">
			</form></div><br><br><hr>';
		}
		$textArea .= "<br><br>";
	}
}
// Close statement
mysqli_stmt_close($getCarSqlStmt);

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
		.carArea{
			border: 2px solid lightgrey;
			margin-left:40%;
			margin-right:40%;
			padding: 10px;
		}
    </style>
</head>
<body>
    <div class="page-header">
        <h1>Our current available cars:</h1>
    </div>
	<div style="position: absolute; left: 10px; top: 10px; border: 3px;">
	<p><a href="welcome.php" class="btn">See your Account</a></p>
	</div>
	
		<div class="form-group" style = "position: absolute; left: 10px;">
			<h2>Search</h1>
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			Street<input type="text" name="street" class="form-control">
			Suburb<input type="text" name="suburb" class="form-control">
			Postcode<input type="text" name="postcode" class="form-control">
			City<input type="text" name="city" class="form-control">
			Country<input type="text" name="country" class="form-control">
			<br><br>
			Make<li style='list-style-type:none'><select class="form-control" name="manufacturer">
			  <option value="" disabled selected></option>
			  <option value="volvo">Volvo</option>
			  <option value="fiat">Fiat</option>
			  <option value="audi">Audi</option>
			  <option value="honda">Honda</option>
			  <option value="toyota">Toyota</option>
			  <option value="ford">Ford</option>
			  <option value="volkswagen">Volkswagen</option>
			  <option value="bmw">BMW</option>
			</select></li>
			
			Model<input type="text" name="model" class="form-control">
			
			Odometer (in range)<li style='list-style-type:none'><select class="form-control" name="odometer">
			<option value="" disabled selected></option>
			  <option value="1">0-999km</option>
			  <option value="2">1000-4999km</option>
			  <option value="3">5000-19999km</option>
			  <option value="4">20000km and above</option>
			</select></li>
			
			Transmission type<li style='list-style-type:none'><select class="form-control" name="transmission">
			<option value="" disabled selected></option>
			  <option value="automatic">Automatic</option>
			  <option value="manual">Manual</option>
			</select></li>
			<br>
			<input type="submit" name="search" class="btn btn-primary" value="Search">
			</form>
		</div>
	
	
	<p><?php echo $textArea; ?></p>
	
	<div style="position: absolute; left: 10px; bottom: 10px; border: 3px;">
	<p><a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a></p>
	</div>
</body>
</html>
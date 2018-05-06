<?php
// Include config file
require_once 'config.php';

// Initialize the session
session_start();


$locationSearchString = $userSearchString = "";
$emptyArray = true;
$users_id_array = array();
$tempUser = 0;

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
	
	if(isset($_POST["locationSearch"])){
		$street = trim($_POST["street"]);
		$suburb = trim($_POST["suburb"]);
		$postcode = trim($_POST["postcode"]);
		$city = trim($_POST["city"]);
		$country = trim($_POST["country"]);
		
		$array = array(
			"street" => $street,
			"suburb" => $suburb,
			"postcode" => $postcode,
			"city" => $city,
			"country" => $country,
		);
		
		$emptyArray = true;
		$locationSearchString = " WHERE";
		
		foreach ($array as $key => $value) {
			if(!empty($value)){
				$locationSearchString .= " " . $key . " = '" . $value . "' AND";
				$emptyArray = false;
			}
		}
		
		$locationSearchString = substr($locationSearchString, 0, -4);
		
		if($emptyArray == true){
			$locationSearchString = "";
		}
		
		$Psql = "SELECT users_id FROM location" . $locationSearchString;
		

			if($Pstmt = mysqli_prepare($link, $Psql)){
				// Bind variables to the prepared statement as parameters
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($Pstmt)){
					/* store result */
					mysqli_stmt_store_result($Pstmt);
					mysqli_stmt_bind_result($Pstmt, $tempUser);
					
					$userSearchString = " users_id IN (";
					$foundId = false;
					
					while(mysqli_stmt_fetch($Pstmt)){
						$userSearchString .= $tempUser . ", ";
						$foundId = true;
					}
					if($foundId == true){
						$userSearchString = substr($userSearchString, 0, -2);
						$userSearchString .= ")";
					}else{
						$userSearchString = "0)";
					}
					
				} else{
					echo "Oops! Something went wrong. Please try again later.";
				}
			}
		// Close statement
		mysqli_stmt_close($Pstmt);
		
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
$textArea = $car_id = $model = $manufacturer = $transmission = $odometer = "";
$getCarSql = "SELECT car_id, model, manufacturer, transmission, odometer FROM car WHERE status = 'listed' AND" . $userSearchString;
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
	
		<div class="form-group" style = "position: absolute; left: 10px;">
			<h2>Search by location:</h1>
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			Street<input type="text" name="street" class="form-control">
			Suburb<input type="text" name="suburb" class="form-control">
			Postcode<input type="text" name="postcode" class="form-control">
			City<input type="text" name="city" class="form-control">
			Country<input type="text" name="country" class="form-control">
			<input type="submit" name="locationSearch" class="btn btn-primary" value="Search">
			</form>
		</div>
	
	
	<p><?php echo $textArea; ?></p>
	
	<div style="position: absolute; left: 10px; bottom: 10px; border: 3px;">
	<p><a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a></p>
	</div>
</body>
</html>
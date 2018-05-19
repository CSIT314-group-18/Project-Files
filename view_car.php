<?php
// Include config file
require_once 'config.php';

// Initialize the session
session_start();

// If session variable is not set it will redirect to login page
if(!isset($_SESSION['this_car_id']) || empty($_SESSION['this_car_id'])){
  header("location: /car_list_main.php");
  exit;
}else{
	$car_id = $_SESSION['this_car_id'];
}

$this_users_id = trim($_SESSION['users_id']);

//get the car that they clicked
$addressArea = $street = $suburb = $postcode = $city = $country = $textArea = $model = 
$manufacturer = $transmission = $rego = $odometer = $colour = $engine_type = $drive_layout = 
$body_type = $seats = $doors = $year = $temp_days_na = "";
$car_owner_users_id = 0;
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


//show the user that owns this car, and his overall rating from the average of all his cars' ratings
$owner_username = $owner_fname = $owner_lname = "";
$getRenteeSql = "SELECT username, fname, lname FROM users WHERE users_id = " . $car_owner_users_id;
if($getRenteeSqlStmt = mysqli_prepare($link, $getRenteeSql)){

	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getRenteeSqlStmt)){

		// Store result, print it to the variable
		mysqli_stmt_store_result($getRenteeSqlStmt);
		mysqli_stmt_bind_result($getRenteeSqlStmt, $owner_username, $owner_fname, $owner_lname);
		mysqli_stmt_fetch($getRenteeSqlStmt);
		
		$msgOwnerArea = '<p>This Car is Owned by:<br><b>' . $owner_username . '</b><br>' . $owner_fname . " " . $owner_lname
		. '</p>';
		
	}
}
// Close statement
mysqli_stmt_close($getRenteeSqlStmt);

if($car_owner_users_id != $this_users_id){
$msgOwnerArea .= '<button class="btn btn-primary" onclick="showChanger(' . "'" . "msgArea" . "'" . ')">Message The Owner of This Car</button>
<div id="msgArea" style="display:none">
<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
<input type="text" name="msg" class="form-control">
<input type="submit" name="SendMsg" class="btn" value="Send"></form></div>';
}

$ratingArea = $temp_review = "";
$temp_rating = $rating_avg = $count = 0;;

//fill out the reviews
$sql = "SELECT review, rating FROM car_rating WHERE car_id = " . $car_id;
if($sqlStmt = mysqli_prepare($link, $sql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($sqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($sqlStmt);
		mysqli_stmt_bind_result($sqlStmt, $temp_review, $temp_rating);
		
		
		//populate the html text field variable
		$ratingArea = "<div style='overflow-y:scroll;'>";
		while(mysqli_stmt_fetch($sqlStmt)){
			$ratingArea .= '<p style="font-style:italic;">"' . $temp_review . '"</p>&nbsp;&nbsp;- ' . $temp_rating . '/5<br><br>';
			$rating_avg += $temp_rating;
			$count++;
		}
		$ratingArea .= "</div>";
		
		if($count > 0){
			$rating_avg = ($rating_avg/$count);
			$ratingArea = "<br><br><br><h3>This car as an average rating of " . $rating_avg . "/5 stars</h3><br><p>Some reviews are: </p><br>" . $ratingArea;
		}
	}
}
// Close statement
mysqli_stmt_close($sqlStmt);


// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
	
	//if the user sends a message to the owner of that car
	if(isset($_POST["SendMsg"])){
		$msg = trim($_POST["msg"]);
		if(empty($msg)){
			echo "Please enter a message.";
		}else if(strlen($msg) > 255){	
			echo "That message is too long!";
		}else{
			
			$sql = "INSERT INTO message (content, sentby, sentto) VALUES (?, ?, ?)";
			
			if($stmt = mysqli_prepare($link, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "sii", $msg, $this_users_id, $owner_users_id);
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt)){
					/* store result */
					mysqli_stmt_store_result($stmt);
					echo "<p style='color:green;'>Message sent.</p>";
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
	<script>
		function showChanger(type) {
			var x = document.getElementById(type);
			if (x.style.display === "none") {
				x.style.display = "block";
			} else {
				x.style.display = "none";
			}
		}
	</script>
</head>
<body>
	<div style="position: absolute; left: 10px; top: 10px; border: 3px;">
	<p><a href="welcome.php" class="btn">See your Account</a></p>
	</div>
	
	
	<div style = "position: absolute; left: 10px;top:12%;"  align = "right">
		<p><?php echo $msgOwnerArea; ?></p>
	</div>
	<p><?php echo $textArea; ?></p>
	<p><a href="/request_conf.php" class="btn btn-primary">Request A Booking</a></p>
	
	
	
	
	<div style="position: absolute; right: 10px; top: 10px; border: 3px;">
		<div style="height:50%;">
			<p><?php echo $ratingArea; ?></p>
		</div>
	</div>
	
	
	<div style="position: absolute; left: 10px; bottom: 10px; border: 3px;">
	<p><a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a></p>
	</div>
</body>
</html>
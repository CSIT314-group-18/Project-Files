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

//get the car that they clicked
$textArea = $model = $manufacturer = $transmission = $odometer = "";
$getCarSql = "SELECT car_id, image, model, manufacturer, transmission, odometer, users_id FROM car WHERE car_id = " . $car_id;
if($getCarSqlStmt = mysqli_prepare($link, $getCarSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getCarSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getCarSqlStmt);
		mysqli_stmt_bind_result($getCarSqlStmt, $car_id, $image, $model, $manufacturer, $transmission, $odometer, $owner_users_id);
		
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


$msgOwnerArea = '<button class="btn btn-primary" onclick="showChanger(' . "'" . "msgArea" . "'" . ')">Message The Owner of This Car</button>
<div id="msgArea" style="display:none">
<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
<input type="text" name="msg" class="form-control">
<input type="submit" name="SendMsg" class="btn" value="Send"></form></div>';


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
			
			$this_users_id = trim($_SESSION['users_id']);
			
			
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
	
	<p><?php echo $textArea; ?></p>
	<p><a href="/request_conf.php" class="btn btn-primary">Request A Booking</a></p>
	
	<div style="position: absolute; right: 10px; top: 10px; border: 3px;">
	<p><?php echo $msgOwnerArea; ?></p>
	</div>
	
	
	<div style="position: absolute; left: 10px; bottom: 10px; border: 3px;">
	<p><a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a></p>
	</div>
</body>
</html>
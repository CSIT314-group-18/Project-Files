<?php
// Include config file
require_once 'config.php';

// Initialize the session
session_start();

// If session variable is not set it will redirect to login page
if(!isset($_SESSION['username']) || empty($_SESSION['username'])){
  header("location: login.php");
  exit;
}

//get the verified variable from this user, to see if they need to be
$verified = 0;
$divArea = "";
$getVerSql = "SELECT verifed FROM users WHERE username = " . "'" . htmlspecialchars($_SESSION['username']) . "'";
if($getVerSqlStmt = mysqli_prepare($link, $getVerSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getVerSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getVerSqlStmt);
		mysqli_stmt_bind_result($getVerSqlStmt, $verified);
		mysqli_stmt_fetch($getVerSqlStmt);
	}
}
// Close statement
mysqli_stmt_close($getVerSqlStmt);

//make the verify form hidden if the user is already verified
if($verified == 1){
	$divArea = "hidden";
}

//initialize variable
$users_id = 0;
//get the id of this user, so that we can use it when finding all the cars they have
$getUserSql = "SELECT users_id FROM users WHERE username = " . "'" . htmlspecialchars($_SESSION['username']) . "'";
if($getUserSqlStmt = mysqli_prepare($link, $getUserSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getUserSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getUserSqlStmt);
		mysqli_stmt_bind_result($getUserSqlStmt, $users_id);
		mysqli_stmt_fetch($getUserSqlStmt);
	}else{
		echo "Something seems to be wrong with your id, please try again later.";
	}
}
// Close statement
mysqli_stmt_close($getUserSqlStmt);

//get all the cars a user has ready
$textArea = $car_id = $model = $manufacturer = $transmission = $odometer = "";
$getCarSql = "SELECT car_id, model, manufacturer, transmission, odometer FROM car WHERE users_id = " . $users_id;
if($getCarSqlStmt = mysqli_prepare($link, $getCarSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getCarSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getCarSqlStmt);
		mysqli_stmt_bind_result($getCarSqlStmt, $car_id, $model, $manufacturer, $transmission, $odometer);
		if(mysqli_stmt_num_rows($getCarSqlStmt) != 0){
                    $textArea = "<label>Your current cars:</label>";
		}
		//populate the html text field variable
		while(mysqli_stmt_fetch($getCarSqlStmt)){
			$textArea .= "<ul style='list-style-type:none'><li>" . $model . "</li><li>" . $manufacturer . "</li><li>" . $transmission . "</li>";
			$textArea .= "<li>" . $odometer . '<br><button class="btn btn-primary" onclick="showChanger(' . "'odoChange'," . $car_id . ')">Update Odometer</button>
			<div id="odoChange' . $car_id . '" style="display:none"><form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="text" name="odo" class="form-control" value= "' . $odometer . '">
			<input type="hidden" name="this_car_id" value="' . $car_id . '">
			<input type="submit" name="odoChange" class="btn btn-primary" value="Submit"></form></div></li></ul>';
			$textArea .= '<button class="btn btn-danger" onclick="showChanger(' . "'deleter'," . $car_id . ')">Remove Car From Our Site</button>
			<div id="deleter' . $car_id . '" style="display:none">
			<p>Are you really sure you want to delete this car from the site?</p>
			<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="hidden" name="this_car_id" value="' . $car_id . '">
			<input type="submit" name="deleteCar" class="btn btn-danger" value="Delete">
			</form>
			</div><br><br>';
		}
		$textArea .= "<br><br>";
	}
}
// Close statement
mysqli_stmt_close($getCarSqlStmt);

//it's easier to define here the button that let's your delete your account
$deleteAccountArea = '<button class="btn btn-danger" onclick="showChanger(' . "'deleter'," . "'" . htmlspecialchars($_SESSION['username']) . "'" . ')">Remove Your Account From Our Site</button>
			<div id="deleter' . htmlspecialchars($_SESSION['username']) . '" style="display:none">
			<p>Are you really sure you want to delete your account?</p>
			<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="submit" name="delAcc" class="btn btn-danger" value="Delete">
			</form>
			</div>';

// Define variables and initialize with empty values
$email = $email_err = "";
$success = "Thank you, you've been verified.";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
	
	//for when they click the verify email button
	if(isset($_POST["emailVerify"])){
		$email = trim($_POST["email"]);
		// Validate email
		if(empty($email)){
			$email_err = "Please enter an email.";
		}else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			$email_err = "This email is not valid.";
		} else{
			// Prepare an update statement
			$sql = "UPDATE users SET verifed = 1 WHERE username = ?";
			
			if($stmt = mysqli_prepare($link, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "s", $param_username);
				
				// Set parameters
				$param_username = $_SESSION['username'];
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt)){
					/* store result */
					mysqli_stmt_store_result($stmt);
					$email_err = $success;
					header("location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
				} else{
					echo "Oops! Something went wrong. Please try again later.";
				}
			}
			// Close statement
			mysqli_stmt_close($stmt);
		}
	}
	
	//for when they click the change odometer button
	if(isset($_POST["odoChange"])){
		//set parameters
		$odo = trim($_POST["odo"]);
		$this_car_id = trim($_POST["this_car_id"]);
		// Validate inputed odometer
		if(empty($odo) || !is_numeric($odo)){
			$odo_err = "Please enter a valid odometer reading.";
			echo $odo_err;
		} else{
			// Prepare an update statement
			$newOdoSql = "UPDATE car SET odometer = ? WHERE car_id = ?";
			
			if($newOdoSqlStmt = mysqli_prepare($link, $newOdoSql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($newOdoSqlStmt, "ii", $odo, $this_car_id);
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($newOdoSqlStmt)){
					/* store result */
					mysqli_stmt_store_result($newOdoSqlStmt);
					header("location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
				} else{
					echo "Oops! Something went wrong. Please try again later.";
				}
			}
			// Close statement
			mysqli_stmt_close($stmt);
		}
	}
	
	//for when they want to legit delete their car
	if(isset($_POST["deleteCar"])){
		//set parameter
		$this_car_id = trim($_POST["this_car_id"]);
		
		// Prepare a delete statement
		$newDeleteCarSql = "DELETE FROM car WHERE car_id = ?";
		
		if($newDeleteCarSqlStmt = mysqli_prepare($link, $newDeleteCarSql)){
			// Bind variables to the prepared statement as parameters
			mysqli_stmt_bind_param($newDeleteCarSqlStmt, "i", $this_car_id);
			
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($newDeleteCarSqlStmt)){
				/* store result */
				mysqli_stmt_store_result($newDeleteCarSqlStmt);
				header("location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
			} else{
				echo "Oops! Something went wrong. Please try again later.";
			}
		}
	}
	
	//for when they want to legit delete their account
	if(isset($_POST["delAcc"])){
		
		// Prepare a delete statement
		$DASql = "DELETE FROM users WHERE users_id = ?";
		
		if($DASqlStmt = mysqli_prepare($link, $DASql)){
			
			// Bind variables to the prepared statement as parameters
			mysqli_stmt_bind_param($DASqlStmt, "i", $users_id);
			
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($DASqlStmt)){
				/* store result */
				mysqli_stmt_store_result($DASqlStmt);
				header("location: register.php");
			} else{
				echo "Oops! Something went wrong with deleting your account.";
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
	<script>
		function showChanger(type, car_id) {
			var x = document.getElementById(type + car_id);
			if (x.style.display === "none") {
				x.style.display = "block";
			} else {
				x.style.display = "none";
			}
		}
	</script>
</head>
<body>
    <div class="page-header">
        <h1>Hi, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b>. Welcome to our site.</h1>
    </div>
    <p><?php echo $textArea; ?></p>
	<div <?php echo $divArea; ?>>
		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			<div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
				<label>Verify your account:</label>
				<input type="text" name="email"class="form-control" value="<?php echo $email; ?>">
				<span class="help-block"><?php echo $email_err; ?></span>
			</div> 
			<div class="form-group">
					<input type="submit" name="emailVerify" class="btn btn-primary" value="Submit">
			</div>
		</form>
	</div>
	<p><a href="add_car.php" class="btn btn-primary">Add a car available for rent</a></p>
	
	<div style="position: absolute; left: 10px; bottom: 10px; border: 3px;">
	<p><a href="/logout.php" class="btn btn-danger">Sign Out of Your Account</a></p>
	</div>
	
	<div style="position: absolute; left: 10px; top: 10px; border: 3px;">
		<p><a href="/car_list_main.php" class="btn">See All Cars</a></p>
	</div>
	
	<div style="position: absolute; right: 10px; bottom: 10px; border: 3px;">
		<?php echo $deleteAccountArea; ?>
	</div>
	
</body>
</html>
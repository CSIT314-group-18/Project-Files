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
	$divArea = "hidden>";
}

//initialize variable
$users_id = "";
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
$textArea = $model = $manufacturer = $transmission = $odometer = "";
$getCarSql = "SELECT model, manufacturer, transmission, odometer FROM car WHERE users_id = " . $users_id;
if($getCarSqlStmt = mysqli_prepare($link, $getCarSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getCarSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getCarSqlStmt);
		mysqli_stmt_bind_result($getCarSqlStmt, $model, $manufacturer, $transmission, $odometer);
		if(mysqli_stmt_num_rows($getCarSqlStmt) != 0){
                    $textArea = "<label>Your current cars:</label>";
		}
		while(mysqli_stmt_fetch($getCarSqlStmt)){
			$textArea .= "<ul><li>" . $model . "</li><li>" . $manufacturer . "</li><li>" . $transmission . "</li><li>" . $odometer . "</li></ul>";
		}
	}
}
// Close statement
mysqli_stmt_close($getCarSqlStmt);


// Define variables and initialize with empty values
$email = $email_err = "";
$success = "Thank you, you've been verified.";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
	if($_POST["emailVerify"]){
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
					header("location: welcome.php");
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
    <div class="page-header">
        <h1>Hi, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b>. Welcome to our site.</h1>
    </div>
    <p><label><?php echo $textArea; ?></p>
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
	<p><a href="add_car.php" class="btn btn-danger">Add a car available for rent</a></p>
	
	<p><a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a></p>
	
</body>
</html>
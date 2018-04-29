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

// Define variables and initialize with empty values
$car_err = "";
$users_id = "";

//get the id of this user, so that we can use it when inputting the car later
$getUserSql = "SELECT users_id FROM users WHERE username = " . "'" . htmlspecialchars($_SESSION['username']) . "'";
if($getUserSqlStmt = mysqli_prepare($link, $getUserSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getUserSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getUserSqlStmt);
		mysqli_stmt_bind_result($getUserSqlStmt, $users_id);
		mysqli_stmt_fetch($getUserSqlStmt);
	}else{
		header("location: welcome.php");
	}
}
// Close statement
mysqli_stmt_close($getUserSqlStmt);

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
	if($_POST["carSubmit"]){
		
		// Set parameters
				$param_model = trim($_POST['model']);
				$param_manufacturer = trim($_POST['manufacturer']);
				$param_transmission = trim($_POST['transmission']);
				$param_odometer = trim($_POST['odometer']);
		
		// Validate car
		if(empty($_POST["carSubmit"]) || !is_numeric($param_odometer)){
			$car_err = "Please enter a car.";
		} else{
			// Prepare an insert statement
			$sql = "INSERT INTO car (model, manufacturer, transmission, odometer, users_id) VALUES (?, ?, ?, ?, ?)";
			
			if($stmt = mysqli_prepare($link, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "sssii", $param_model, $param_manufacturer, $param_transmission, $param_odometer, $users_id);
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt)){
					/* store result */
					mysqli_stmt_store_result($stmt);
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
    <title>Add a car</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; text-align: center; }
    </style>
</head>
<body>
    <div class="page-header">
        <h1>Hi, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b>. Please enter your car information below:</h1>
    </div>
	<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
		<div class="form-group <?php echo (!empty($car_err)) ? 'has-error' : ''; ?>">
			<ul>
			<li><input type="text" name="model"class="form-control" placeholder="Model" required></li>
			<li><select class="form-control" name="manufacturer" required>
			  <option value="" disabled selected>Manufacturer</option>
			  <option value="volvo">Volvo</option>
			  <option value="fiat">Fiat</option>
			  <option value="audi">Audi</option>
			  <option value="honda">Honda</option>
			  <option value="toyota">Toyota</option>
			  <option value="ford">Ford</option>
			  <option value="volkswagen">Volkswagen</option>
			  <option value="bmw">BMW</option>
			</select></li>
			<li><select class="form-control" name="transmission" required>
			  <option value="" disabled selected>Transmission</option>
			  <option value="automatic">Automatic</option>
			  <option value="manual">Manual</option>
			</select></li></li>
			<li><input type="text" name="odometer"class="form-control" placeholder="Current odometer reading" required></li>
			</ul>
			<span class="help-block"><?php echo $car_err; ?></span>
		</div> 
		<div class="form-group">
                <input type="submit" name="carSubmit" class="btn btn-primary" value="Submit">
        </div>
	</form>
	
	<p><a href="welcome.php" class="btn btn-danger">Back to Home</a></p>
	
	<p><a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a></p>
</body>
</html>
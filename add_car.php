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
$car_err = $newFileName = "";
$users_id = "";
$this_car_id = 0;

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
	if(isset($_POST["carSubmit"])){
		
		// Set parameters
		$param_model = trim($_POST['model']);
		$param_manufacturer = trim($_POST['manufacturer']);
		$param_transmission = trim($_POST['transmission']);
		$param_odometer = trim($_POST['odometer']);
		$status = "listed";
		
		// Validate car
		if(empty($_POST["carSubmit"]) || !is_numeric($param_odometer)){
			$car_err = "Please enter a car.";
		} else{
			// Prepare an insert statement
			$sql = "INSERT INTO car (model, manufacturer, transmission, odometer, status, users_id) VALUES (?, ?, ?, ?, ?, ?)";
			
			if($stmt = mysqli_prepare($link, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "sssisi", $param_model, $param_manufacturer, $param_transmission, $param_odometer, $status, $users_id);
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt)){
					/* store result */
					
					// get the car id of the car we just put in
					$Gsql = "SELECT MAX(car_id) FROM car";
						
					if($Gstmt = mysqli_prepare($link, $Gsql)){
						// Attempt to execute the prepared statement
						if(mysqli_stmt_execute($Gstmt)){
							/* store result */
							mysqli_stmt_store_result($Gstmt);
							mysqli_stmt_bind_result($Gstmt, $this_car_id);
							mysqli_stmt_fetch($Gstmt);
						} else{
							echo "Oops! Something went wrong. Please try again later.";
						}
					}
					// Close statement
					mysqli_stmt_close($Gstmt);
					
					
					mysqli_stmt_store_result($stmt);
					
				} else{
					echo "Oops! Something went wrong. Please try again later.";
				}
			}
			// Close statement
			mysqli_stmt_close($stmt);
		}
		
		if(isset($_FILES["fileToUpload"])){
			//initialise photo uploading code
			$target_dir = "car_image/";
			
			//convert photo to its new name from the car_id
			$temp = explode(".", $_FILES["fileToUpload"]["name"]);
			$newFileName = $this_car_id . '.' . end($temp);
			$target_file = $target_dir . $newFileName;
			$this_car_id = 0;
			$uploadOk = 1;
			$imageFileType = strtolower(end($temp));
			echo $newFileName;

			//everything to do with uploading a file
			$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
			if($check !== false) {
				echo "File is an image - " . $check["mime"] . ".";
				$uploadOk = 1;
			} else {
				echo "File is not an image.";
				$uploadOk = 0;
			}
			
			// Check if file already exists
			if (file_exists($target_file)) {
				echo "Sorry, file already exists.";
				$uploadOk = 0;
			}
			// Check file size
			if ($_FILES["fileToUpload"]["size"] > 500000) {
				echo "Sorry, your file is too large.";
				$uploadOk = 0;
			}
			// Allow certain file formats
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
			&& $imageFileType != "gif" ) {
				echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
				$uploadOk = 0;
			}
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == 0) {
				echo "Sorry, your file was not uploaded.";
			// if everything is ok, try to upload file
			} else {
				if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
					echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
					header("location: welcome.php");
				} else {
					echo "Sorry, there was an error uploading your file.";
				}
			}
		}else{
			echo "Please choose a photo for your car.";
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
	<form enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
		<div class="form-group <?php echo (!empty($car_err)) ? 'has-error' : ''; ?>">
			Select image to upload:
			
			<ul style='list-style-type:none; padding-left:35%; padding-right:35%;'>
			<li><input type="file" class="form-control" name="fileToUpload" id="fileToUpload"></li>
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
			</select></li>
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
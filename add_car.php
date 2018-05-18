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
		$param_rego = trim($_POST['registration']);
		$param_model = trim($_POST['model']);
		$param_manufacturer = trim($_POST['manufacturer']);
		$param_transmission = trim($_POST['transmission']);
		$param_odometer = trim($_POST['odometer']);
		$param_colour = trim($_POST['colour']);
		$param_engine_type = trim($_POST['engine_type']);
		$param_drive_layout = trim($_POST['drive_layout']);
		$param_body_type = trim($_POST['body_type']);
		$param_seats = trim($_POST['seats']);
		$param_doors = trim($_POST['doors']);
		$param_year = trim($_POST['year']);
		
		$status = "listed";
		$days_na = 'Monday-checked,Tuesday-checked,Wednesday-checked,Thursday-checked,Friday-checked,Saturday-checked,Sunday-checked';
		
		
		
		
		$photo_err = "";
		
		if(isset($_FILES["fileToUpload"]) || !empty($_FILES["fileToUpload"])){
			//initialise photo uploading code
			$target_dir = "car_image/";
			
			//convert photo to its new name from the car_id
			$temp = explode(".", $_FILES["fileToUpload"]["name"]);
			$newFileName = $this_car_id . '.' . end($temp);
			$target_file = $target_dir . $newFileName;
			$this_car_id = 0;
			$uploadOk = 1;
			$imageFileType = strtolower(end($temp));

			//everything to do with uploading a file
			$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
			if($check !== false) {
				$uploadOk = 1;
			} else {
				$photo_err = "File is not an image.";
				$uploadOk = 0;
			}
			
			// Check if file already exists
			if (file_exists($target_file)) {
				$photo_err = "Sorry, file already exists.";
				$uploadOk = 0;
			}
			// Check file size
			if ($_FILES["fileToUpload"]["size"] > 600000) {
				$photo_err = "Sorry, your file is too large.";
				$uploadOk = 0;
			}
			// Allow certain file formats
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
			&& $imageFileType != "gif" ) {
				$photo_err = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
				$uploadOk = 0;
			}
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == 0) {
				$photo_err = "Sorry, your file was not uploaded.";
			// if everything is ok, try to upload file
			} else {
				if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
					echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
					header("location: welcome.php");
				} else {
					$photo_err = "Sorry, there was an error uploading your file.";
				}
			}
		}else{
			$photo_err = "Please choose a photo for your car.";
		}
		
		if($uploadOk == 0){
			// gif the photo wasn't uploaded, delete the car that was just made
			$Gsql = "DELETE FROM car WHERE car_id = " . $this_car_id;
				
			if($Gstmt = mysqli_prepare($link, $Gsql)){
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($Gstmt)){
					/* store result */
					mysqli_stmt_store_result($Gstmt);
				} else{
					echo "Oops! Something went wrong. Please try again later.";
				}
			}
			// Close statement
			mysqli_stmt_close($Gstmt);
		}
		echo $photo_err;
		
		
		if($photo_err == ""){
			// Validate car
			if(empty($_POST["carSubmit"]) || !is_numeric($param_odometer)){
				$car_err = "Please enter a car.";
			} else if(!preg_match("/[A-Z0-9]{6}$/", $param_rego)){
				echo "That registration doesn't match any that are linked to your license.";
			}else{	
				// Prepare an insert statement
				$sql = "INSERT INTO car (registration, model, manufacturer, transmission, colour, engine_type, drive_layout, body_type, seats, doors, year, odometer, status, days_na, users_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				if($stmt = mysqli_prepare($link, $sql)){
					// Bind variables to the prepared statement as parameters
					mysqli_stmt_bind_param($stmt, "ssssssssiiiissi", $param_rego, $param_model, $param_manufacturer, $param_transmission, $param_colour, $param_engine_type, $param_drive_layout, $param_body_type, $param_seats, $param_doors, $param_year, $param_odometer, $status, $days_na, $users_id);
					
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
			<li><input type="text" name="registration"class="form-control" placeholder="registration" required></li>
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
			<li><select class="form-control" name="colour" required>
			  <option value="" disabled selected>Colour</option>
			  <option value="Black">Black</option>
			  <option value="White">White</option>
			  <option value="Blue">Blue</option>
			  <option value="Green">Green</option>
			  <option value="Red">Red</option>
			  <option value="Grey/Silver">Grey/Silver</option>
			  <option value="Brown">Brown</option>
			  <option value="Yellow">Yellow</option>
			  <option value="Orange">Orange</option>
			  <option value="Purple">Purple</option>
			</select></li>
			<li><select class="form-control" name="engine_type" required>
			  <option value="" disabled selected>Engine Type</option>
			  <option value="2 Cylinder">2 Cylinder</option>
			  <option value="4 Cylinder">4 Cylinder</option>
			  <option value="6 Cylinder">6 Cylinder</option>
			  <option value="8 Cylinder">8 Cylinder</option>
			  <option value="12 Cylinder">12 Cylinder</option>
			  <option value="Electric">Electric</option>
			</select></li>
			<li><select class="form-control" name="drive_layout" required>
			  <option value="" disabled selected>Drive Layout</option>
			  <option value="2WD">2WD</option>
			  <option value="4WD">4WD</option>
			</select></li>
			<li><select class="form-control" name="body_type" required>
			  <option value="" disabled selected>Body Type</option>
			  <option value="Sedan">Sedan</option>
			  <option value="SUV">SUV</option>
			  <option value="Hatchback">Hatchback</option>
			  <option value="Ute">Ute</option>
			  <option value="Van">Van</option>
			  <option value="Coupe">Coupe</option>
			  <option value="Wagon">Wagon</option>
			</select></li>
			<li><select class="form-control" name="seats" required>
			  <option value="" disabled selected>Seats</option>
			  <option value="2">2</option>
			  <option value="3">3</option>
			  <option value="4">4</option>
			  <option value="5">5</option>
			  <option value="6">6</option>
			  <option value="7">7</option>
			  <option value="8">8</option>
			  <option value="9">9</option>
			</select></li>
			<li><select class="form-control" name="doors" required>
			  <option value="" disabled selected>Doors</option>
			  <option value="2">2</option>
			  <option value="3">3</option>
			  <option value="4">4</option>
			  <option value="5">5</option>
			</select></li>
			<li><select class="form-control" name="year" required>
				<option value="" disabled selected>Year</option>
				<option value="2018">2018</option>
				<option value="2017">2017</option>
				<option value="2016">2016</option>
				<option value="2015">2015</option>
				<option value="2014">2014</option>
				<option value="2013">2013</option>
				<option value="2012">2012</option>
				<option value="2011">2011</option>
				<option value="2010">2010</option>
				<option value="2009">2009</option>
				<option value="2008">2008</option>
				<option value="2007">2007</option>
				<option value="2006">2006</option>
				<option value="2005">2005</option>
				<option value="2004">2004</option>
				<option value="2003">2003</option>
				<option value="2002">2002</option>
				<option value="2001">2001</option>
				<option value="2000">2000</option>
				<option value="1999">1999</option>
				<option value="1998">1998</option>
				<option value="1997">1997</option>
				<option value="1996">1996</option>
				<option value="1995">1995</option>
				<option value="1994">1994</option>
				<option value="1993">1993</option>
				<option value="1992">1992</option>
				<option value="1991">1991</option>
				<option value="1990">1990</option>
				<option value="1989">1989</option>
				<option value="1988">1988</option>
				<option value="1987">1987</option>
				<option value="1986">1986</option>
				<option value="1985">1985</option>
				<option value="1984">1984</option>
				<option value="1983">1983</option>
				<option value="1982">1982</option>
				<option value="1981">1981</option>
				<option value="1980">1980</option>
				<option value="1979">1979</option>
				<option value="1978">1978</option>
				<option value="1977">1977</option>
				<option value="1976">1976</option>
				<option value="1975">1975</option>
				<option value="1974">1974</option>
				<option value="1973">1973</option>
				<option value="1972">1972</option>
				<option value="1971">1971</option>
				<option value="1970">1970</option>
				<option value="1969">1969</option>
				<option value="1968">1968</option>
				<option value="1967">1967</option>
				<option value="1966">1966</option>
				<option value="1965">1965</option>
				<option value="1964">1964</option>
				<option value="1963">1963</option>
				<option value="1962">1962</option>
				<option value="1961">1961</option>
				<option value="1960">1960</option>
				<option value="1959">1959</option>
				<option value="1958">1958</option>
				<option value="1957">1957</option>
				<option value="1956">1956</option>
				<option value="1955">1955</option>
				<option value="1954">1954</option>
				<option value="1953">1953</option>
				<option value="1952">1952</option>
				<option value="1951">1951</option>
				<option value="1950">1950</option>
				<option value="1949">1949</option>
				<option value="1948">1948</option>
				<option value="1947">1947</option>
				<option value="1946">1946</option>
				<option value="1945">1945</option>
				<option value="1944">1944</option>
				<option value="1943">1943</option>
				<option value="1942">1942</option>
				<option value="1941">1941</option>
				<option value="1940">1940</option>
				<option value="1939">1939</option>
				<option value="1938">1938</option>
				<option value="1937">1937</option>
				<option value="1936">1936</option>
				<option value="1935">1935</option>
				<option value="1934">1934</option>
				<option value="1933">1933</option>
				<option value="1932">1932</option>
				<option value="1931">1931</option>
				<option value="1930">1930</option>
				<option value="1929">1929</option>
				<option value="1928">1928</option>
				<option value="1927">1927</option>
				<option value="1926">1926</option>
				<option value="1925">1925</option>
				<option value="1924">1924</option>
				<option value="1923">1923</option>
				<option value="1922">1922</option>
				<option value="1921">1921</option>
				<option value="1920">1920</option>
				<option value="1919">1919</option>
				<option value="1918">1918</option>
				<option value="1917">1917</option>
				<option value="1916">1916</option>
				<option value="1915">1915</option>
				<option value="1914">1914</option>
				<option value="1913">1913</option>
				<option value="1912">1912</option>
				<option value="1911">1911</option>
				<option value="1910">1910</option>
				<option value="1909">1909</option>
				<option value="1908">1908</option>
				<option value="1907">1907</option>
				<option value="1906">1906</option>
				<option value="1905">1905</option>
				<option value="1904">1904</option>
				<option value="1903">1903</option>
				<option value="1902">1902</option>
				<option value="1901">1901</option>
				<option value="1900">1900</option>
			   </select></li
			<li><input type="text" name="odometer"class="form-control" placeholder="Current odometer reading (km)" required></li>
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
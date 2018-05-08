<?php
// Include config file
require_once 'config.php';
 
// Define variables and initialize with empty values
$username = $password = $confirm_password = $fname = $lname = $licnese_number = "";
$username_err = $password_err = $confirm_password_err = $address_err = "";
$location_id = 0;
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
	
	//user must have the location set
	$street = trim($_POST["street"]);
	$suburb = trim($_POST["suburb"]);
	$postcode = trim($_POST["postcode"]);
	$city = trim($_POST["city"]);
	$country = trim($_POST["country"]);
	
	if(empty($street) || empty($suburb) || empty($postcode) || empty($city) || empty($country)){
		$address_err = "Please fill out all parts of the address form.";
		echo "Please fill out all parts of the address form.";
	}else{
		if(!is_int((int)$postcode)){
			$address_err = "The postcode must be a number.";
			echo "The postcode must be a number.";
		}else{
				$sql = "INSERT INTO location (street, suburb, postcode, city, country) VALUES  (?, ?, ?, ?, ?)";

				if($stmt = mysqli_prepare($link, $sql)){
					// Bind variables to the prepared statement as parameters
					mysqli_stmt_bind_param($stmt, "sssss", $street, $suburb, $postcode, $city, $country);

					// Attempt to execute the prepared statement
					if(mysqli_stmt_execute($stmt)){
						/* store result */
						mysqli_stmt_store_result($stmt);
						mysqli_stmt_bind_result($stmt, $location_id);
						mysqli_stmt_fetch($stmt);
						
					} else{
						echo "Oops! Something went wrong. Please try again later.";
					}
				}
				// Close statement
				mysqli_stmt_close($stmt);
				
				//get that location id we just put in
				$sql = "SELECT MAX(location_id) FROM location";
				if($stmt = mysqli_prepare($link, $sql)){

					// Attempt to execute the prepared statement
					if(mysqli_stmt_execute($stmt)){
						/* store result */
						mysqli_stmt_store_result($stmt, $location_id);
						mysqli_stmt_bind_result($stmt, $location_id);
						mysqli_stmt_fetch($stmt);
						
					} else{
						echo "Oops! Something went wrong. Please try again later.";
					}
				}
				// Close statement
				mysqli_stmt_close($stmt);
		}
	}
	
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else{
        // Prepare a select statement
        $sql = "SELECT users_id FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Validate password
    if(empty(trim($_POST['password']))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST['password'])) < 6){
        $password_err = "Password must have at least 6 characters.";
    } else{
        $password = trim($_POST['password']);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = 'Please confirm password.';     
    } else{
        $confirm_password = trim($_POST['confirm_password']);
        if($password != $confirm_password){
            $confirm_password_err = 'Password did not match.';
        }
    }
    
	// Validate first name
    if(empty(trim($_POST['fname']))){
        $fname_err = "Please enter a first name.";
		echo $fname_err;
    } else{
        $fname = trim($_POST['fname']);
    }
	
	// Validate last name
    if(empty(trim($_POST['lname']))){
        $lname_err = "Please enter a last name.";
		echo $lname_err;
    } else{
        $lname = trim($_POST['lname']);
    }
	
	// Validate license number
    if(empty(trim($_POST['license_number']))){
        $license_number_err = "Please enter a license.";     
    } else if(!is_int((int)(trim($_POST['license_number'])))){
        $license_number_err = "License Number must only be numbers.";
		echo $license_number_err;
    } else{
        $license_number = trim($_POST['license_number']);
    }
	
	
    // Check input errors before inserting in database
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($address_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password, fname, lname, license_number, location_id) VALUES (?, ?, ?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssssi", $param_username, $param_password, $fname, $lname, $license_number, $location_id);
            
            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            
            // Attempt to execute the prepared statement
            if(!mysqli_stmt_execute($stmt)){	
                echo "Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        mysqli_stmt_close($stmt);
		
		
		$sql = "SELECT users_id FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                
				/* store result */
				mysqli_stmt_store_result($stmt);
				mysqli_stmt_bind_result($stmt, $users_id);
				mysqli_stmt_fetch($stmt);
				
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        mysqli_stmt_close($stmt);
		
		$sql = "UPDATE location SET users_id = ? WHERE location_id = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ii", $users_id, $location_id);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to login page
                header("location: login.php");
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Close connection
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Sign Up</h2>
        <p>Please fill this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username"class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
                <span class="help-block"><?php echo $confirm_password_err; ?></span>
            </div>
			
			<br>
			<div class="form-group">
			First Name<input type="text" name="fname" class="form-control">
			Last Name<input type="text" name="lname" class="form-control">
			License Number<input type="text" name="license_number" class="form-control">
			</div>
			
			<div class="form-group">
			Street<input type="text" name="street" class="form-control">
			Suburb<input type="text" name="suburb" class="form-control">
			Postcode<input type="text" name="postcode" class="form-control">
			City<input type="text" name="city" class="form-control">
			Country<input type="text" name="country" class="form-control">
			</div>
			
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-default" value="Reset">
            </div>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
		
    </div>    
</body>
</html>
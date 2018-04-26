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
$email = $email_err = "";
$success = "Thank you, you've been verified.";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
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
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        // Close statement
        mysqli_stmt_close($stmt);
    }
}	
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
    <p><a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a></p>
	
	<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
		<div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
			<label>Verify your account:</label>
			<input type="text" name="email"class="form-control" value="<?php echo $email; ?>">
			<span class="help-block"><?php echo $email_err; ?></span>
		</div> 
		<div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
        </div>
	</form>
</body>
</html>
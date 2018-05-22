<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'demo');
 
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

//define how much the system takes per transaction
$system_commission = 0;
$sql = "SELECT commission FROM system";
if($stmt = mysqli_prepare($link, $sql)){
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($stmt)){
		/* store result */
		mysqli_stmt_store_result($stmt);
		mysqli_stmt_bind_result($stmt, $system_commission);
		mysqli_stmt_fetch($stmt);
		
	} else{
		echo "Oops! Something went wrong. Please try again later.";
	}
}
// Close statement
mysqli_stmt_close($stmt);

?>
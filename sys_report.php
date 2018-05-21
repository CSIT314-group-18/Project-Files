<?php
// Include config file
require_once 'config.php';

// Initialize the session
session_start();


if(!isset($_SESSION['username']) || empty($_SESSION['username']) || ($_SESSION['isAdmin'] != true)){
  header("location: login.php");
  exit;
}


/*
$locationSearchString = $userSearchString = "";
$emptyArray = true;
$users_id_array = array();
$tempUser = 0;

$Psql = "SELECT users_id FROM users";

if($Pstmt = mysqli_prepare($link, $Psql)){
	// Bind variables to the prepared statement as parameters
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($Pstmt)){
		mysqli_stmt_store_result($Pstmt);
		mysqli_stmt_bind_result($Pstmt, $tempUser);
		
		$userSearchString = " users_id IN (";
		while(mysqli_stmt_fetch($Pstmt)){
			$userSearchString .= $tempUser . ", ";
		}
		
		$userSearchString = substr($userSearchString, 0, -2);
		$userSearchString .= ")";
		
	} else{
		echo "Oops! Something went wrong. Please try again later.";
	}
}
// Close statement
mysqli_stmt_close($Pstmt);

*/
//get the transactions
$temp_owner = $temp_renter = $temp_fee = $temp_reservation_id = $temp_id = $system_balance = 0;
$temp_ownername = $temp_rentername = $temp_startdate = $temp_enddate = $userAccountArea = "";
$made_trans = false;
$sql = "SELECT payment_id, owner, renter, total_fee, reservation_id FROM payment WHERE payment_status = 'paid'";

if($sqlStmt = mysqli_prepare($link, $sql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($sqlStmt)){
		/* store result */
		mysqli_stmt_store_result($sqlStmt);
		mysqli_stmt_bind_result($sqlStmt, $temp_id, $temp_owner, $temp_renter, $temp_fee, $temp_reservation_id);
		$userAccountArea .= '<ul style="list-style-type:none">';
		while(mysqli_stmt_fetch($sqlStmt)){
			$made_trans = true;
			
			//get the dates that each transaction happened
			$getRenteeSql = "SELECT startdate, enddate FROM reservation WHERE reservation_id = " . $temp_reservation_id;
			if($getRenteeSqlStmt = mysqli_prepare($link, $getRenteeSql)){
			
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($getRenteeSqlStmt)){

					// Store result, print it to the variable
					mysqli_stmt_store_result($getRenteeSqlStmt);
					mysqli_stmt_bind_result($getRenteeSqlStmt, $temp_startdate, $temp_enddate);
					mysqli_stmt_fetch($getRenteeSqlStmt);
				}
			}
			// Close statement
			mysqli_stmt_close($getRenteeSqlStmt);
			
			//convert the dates into something readable
			$temp_startdate = substr($temp_startdate, 0, -8);
			$temp_enddate = substr($temp_enddate, 0, -8);
			$temp_startdate = strtotime($temp_startdate);
			$temp_enddate = strtotime($temp_enddate);
			
				
				//get the person who's not you
				$getRenteeSql = "SELECT username FROM users WHERE users_id = " . $temp_renter;
				if($getRenteeSqlStmt = mysqli_prepare($link, $getRenteeSql)){
				
					// Attempt to execute the prepared statement
					if(mysqli_stmt_execute($getRenteeSqlStmt)){

						// Store result, print it to the variable
						mysqli_stmt_store_result($getRenteeSqlStmt);
						mysqli_stmt_bind_result($getRenteeSqlStmt, $temp_rentername);
						mysqli_stmt_fetch($getRenteeSqlStmt);
					}
				}
				// Close statement
				mysqli_stmt_close($getRenteeSqlStmt);
				
				//get the person who's not you
				$getRenteeSql = "SELECT username FROM users WHERE users_id = " . $temp_owner;
				if($getRenteeSqlStmt = mysqli_prepare($link, $getRenteeSql)){
				
					// Attempt to execute the prepared statement
					if(mysqli_stmt_execute($getRenteeSqlStmt)){

						// Store result, print it to the variable
						mysqli_stmt_store_result($getRenteeSqlStmt);
						mysqli_stmt_bind_result($getRenteeSqlStmt, $temp_ownername);
						mysqli_stmt_fetch($getRenteeSqlStmt);
					}
				}
				// Close statement
				mysqli_stmt_close($getRenteeSqlStmt);
				
				$userAccountArea .= '<li><fieldset><legend>Transaction ID: ' . $temp_id . '</legend>' . $temp_rentername . ' paid ' . $temp_ownername
				. ' $' . $temp_fee . ' for the rental dates of '
				. date('D d/m/Y', $temp_startdate) . ' to ' . date('D d/m/Y', $temp_enddate) .  '</fieldset></li><br>';
			
		}
		
		//get the already existing balance from the system, to update it
		$getSysBal = "SELECT balance FROM users WHERE users_id = 1";
		
		if($getSysBalStmt = mysqli_prepare($link, $getSysBal)){
			
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($getSysBalStmt)){
				/* store result */
				mysqli_stmt_store_result($getSysBalStmt);
				mysqli_stmt_bind_result($getSysBalStmt, $system_balance);
				mysqli_stmt_fetch($getSysBalStmt);
			} else{
				echo "Oops! Something went wrong. Please try again later.";
			}
		}
		// Close statement
		mysqli_stmt_close($getSysBalStmt);
		
		
		if($made_trans == false){
			$userAccountArea .= "There haven't been any transactions yet.";
		}
		$userAccountArea .= '</ul><br><br><h4>The system made $' . $system_balance . ' in commission.<br>The current commission is '
		. $system_commission . '% of any transaction.</h4><br><br><br>';
		
	} else{
		echo "Oops! Something went wrong. Please try again later.";
	}
}
// Close statement
mysqli_stmt_close($sqlStmt);

$userAccountArea .= '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input style="width:50px;" type="number" name="newCommission" value="' . $system_commission . '" min="0" max="100">%
			<input type="submit" name="changeCommission" class="btn" value="Change The Commission"></form>';

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
	
	//changes the commission
	if(isset($_POST["changeCommission"])){
		$newCommission = trim($_POST["newCommission"]);
		
		// Prepare an update statement
		$acceptReqSql = "UPDATE system SET commission = ? WHERE commission = " . $system_commission;
		
		if($acceptReqSqlStmt = mysqli_prepare($link, $acceptReqSql)){
			
			mysqli_stmt_bind_param($acceptReqSqlStmt, "d", $newCommission);
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($acceptReqSqlStmt)){
				/* store result */
				mysqli_stmt_store_result($acceptReqSqlStmt);
				header("location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
			} else{
				echo "Oops! Something went wrong. Please try again later.";
			}
		}
		// Close statement
		mysqli_stmt_close($acceptReqSqlStmt);
		
	}
	
	
	if(isset($_POST["locationSearch"])){
		$street = trim($_POST["street"]);
		$suburb = trim($_POST["suburb"]);
		$postcode = trim($_POST["postcode"]);
		$city = trim($_POST["city"]);
		$country = trim($_POST["country"]);
		
		$array = array(
			"street" => $street,
			"suburb" => $suburb,
			"postcode" => $postcode,
			"city" => $city,
			"country" => $country,
		);
		
		$emptyArray = true;
		$locationSearchString = " WHERE";
		
		foreach ($array as $key => $value) {
			if(!empty($value)){
				$locationSearchString .= " " . $key . " = '" . $value . "' AND";
				$emptyArray = false;
			}
		}
		
		$locationSearchString = substr($locationSearchString, 0, -4);
		
		if($emptyArray == true){
			$locationSearchString = "";
		}
		
		$Psql = "SELECT users_id FROM location" . $locationSearchString;
		

			if($Pstmt = mysqli_prepare($link, $Psql)){
				// Bind variables to the prepared statement as parameters
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($Pstmt)){
					/* store result */
					mysqli_stmt_store_result($Pstmt);
					mysqli_stmt_bind_result($Pstmt, $tempUser);
					
					$userSearchString = " users_id IN (";
					$foundId = false;
					
					while(mysqli_stmt_fetch($Pstmt)){
						$userSearchString .= $tempUser . ", ";
						$foundId = true;
					}
					if($foundId == true){
						$userSearchString = substr($userSearchString, 0, -2);
						$userSearchString .= ")";
					}else{
						$userSearchString = "0)";
					}
					
				} else{
					echo "Oops! Something went wrong. Please try again later.";
				}
			}
		// Close statement
		mysqli_stmt_close($Pstmt);
		
	}
	
	
	if(isset($_POST["suspendUser"])){
		$this_users_id = trim($_POST["this_users_id"]);
		
		$isSuspended = 0;
		
		//get the suspension status of a user
		$getCarNameSql = "SELECT account_suspended FROM users WHERE users_id = " . $this_users_id;
		if($getCarNameSqlStmt = mysqli_prepare($link, $getCarNameSql)){
		
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($getCarNameSqlStmt)){

				// Store result, print it to the variable
				mysqli_stmt_store_result($getCarNameSqlStmt);
				mysqli_stmt_bind_result($getCarNameSqlStmt, $isSuspended);
				mysqli_stmt_fetch($getCarNameSqlStmt);
			}
		}
		// Close statement
		mysqli_stmt_close($getCarNameSqlStmt);
		
		if($isSuspended == 0)$isSuspended = 1;
		else if($isSuspended == 1)$isSuspended = 0;
		
		// Prepare an update statement
		$acceptReqSql = "UPDATE users SET account_suspended = ? WHERE users_id = " . $this_users_id;
		
		if($acceptReqSqlStmt = mysqli_prepare($link, $acceptReqSql)){
			
			mysqli_stmt_bind_param($acceptReqSqlStmt, "i", $isSuspended);
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($acceptReqSqlStmt)){
				/* store result */
				mysqli_stmt_store_result($acceptReqSqlStmt);
				//header("location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
			} else{
				echo "Oops! Something went wrong. Please try again later.";
			}
		}
		// Close statement
		mysqli_stmt_close($acceptReqSqlStmt);
		
	}
	
}	



/*

//get all the users
$textArea = $temp_username = $temp_fname = $temp_lname = $temp_verified = $temp_account_suspended = "";
$temp_users_id = $temp_license_number = 0;

$getCarSql = "SELECT users_id, username, fname, lname, license_number, verifed, account_suspended FROM users WHERE" . $userSearchString;
if($getCarSqlStmt = mysqli_prepare($link, $getCarSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getCarSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getCarSqlStmt);
		mysqli_stmt_bind_result($getCarSqlStmt, $temp_users_id, $temp_username, $temp_fname, $temp_lname, $temp_license_number, $temp_verified, $temp_account_suspended);
		
		//populate the html text field variable
		while(mysqli_stmt_fetch($getCarSqlStmt)){
			$textArea .= "<ul style='list-style-type:none'><li>" . $temp_username . "</li><li>" . $temp_fname . " " . $temp_lname . "</li><li>License Number: " . $temp_license_number . "</li>";
			$textArea .= "<li>Verified: " . $temp_verified . '<br>
			<li>Is Currently Suspended: ' . $temp_account_suspended . '<br>
			<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="hidden" name="this_users_id" value="' . $temp_users_id . '">
			<input type="submit" name="suspendUser" class="btn btn-primary" value="Suspend User"></form></div></li></ul>';
			$textArea .= '<br><br>';
		}
		$textArea .= "<br><br>";
	}
}
// Close statement
mysqli_stmt_close($getCarSqlStmt);

*/


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
        <h1><img src="1_Primary_logo_on_transparent_427x63.png" alt="" width="413" height="63" longdesc="1_Primary_logo_on_transparent_427x63.png">All Transactions</h1>
    </div>
	<div style="position: absolute; left: 8px; top: 91px; border: 3px;">
	<p><a href="welcome.php" class="btn">See your Account</a></p>
	</div>
	
		<div hidden class="form-group" style = "position: absolute; left: 10px;">
			<h2>Search by location:</h1>
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			Street<input type="text" name="street" class="form-control">
			Suburb<input type="text" name="suburb" class="form-control">
			Postcode<input type="text" name="postcode" class="form-control">
			City<input type="text" name="city" class="form-control">
			Country<input type="text" name="country" class="form-control">
			<input type="submit" name="locationSearch" class="btn btn-primary" value="Search">
			</form>
		</div>
	
	
	<p><?php echo $userAccountArea; ?></p>
	
	<div style="position: absolute; left: 1150px; bottom: 600px; border: 3px;">
	<p><a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a></p>
	</div>
</body>
</html>
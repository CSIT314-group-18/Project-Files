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

//get the verified variable and the user id from this user, to see if they need to be
$verified = $users_id = $location_id = $account_suspended = 0;
$divArea = "";
$getVerSql = "SELECT users_id, verifed, location_id, account_suspended FROM users WHERE username = " . "'" . htmlspecialchars($_SESSION['username']) . "'";
if($getVerSqlStmt = mysqli_prepare($link, $getVerSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getVerSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getVerSqlStmt);
		mysqli_stmt_bind_result($getVerSqlStmt, $users_id, $verified, $location_id, $account_suspended);
		mysqli_stmt_fetch($getVerSqlStmt);
	}
}
// Close statement
mysqli_stmt_close($getVerSqlStmt);

$_SESSION['location_id'] = $location_id;
$_SESSION['users_id'] = $users_id;


//link to admin page, if the user is an admin
$adminArea = "";
if($_SESSION['isAdmin'] == true){
	$adminArea .= "<p><a href='/user_list_main.php' class='btn'>See All Users</a></p>";
}


//make the verify form hidden if the user is already verified
if($verified == 1){
	$divArea = "hidden";
}

$suspendedArea = "";
if($account_suspended == 1)$suspendedArea = " <p>You are suspended. Appeal to an administrator <a>here</a> </p><div style='display:none'>";
else $suspendedArea = "<div>";

//get all the cars a user has ready
$textArea = $model = $manufacturer = $transmission = $status = "";
$car_id = $odometer = 0;
$getCarSql = "SELECT car_id, model, manufacturer, transmission, odometer, status FROM car WHERE users_id = " . $users_id;
if($getCarSqlStmt = mysqli_prepare($link, $getCarSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getCarSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getCarSqlStmt);
		mysqli_stmt_bind_result($getCarSqlStmt, $car_id, $model, $manufacturer, $transmission, $odometer, $status);
		if(mysqli_stmt_num_rows($getCarSqlStmt) != 0){
                    $textArea = "<label>Your current cars:</label>";
		}
		//populate the html text field variable
		while(mysqli_stmt_fetch($getCarSqlStmt)){
			$textArea .= "<ul style='list-style-type:none'><li>" . $model . "</li><li>" . $manufacturer . "</li><li>" . $transmission . "</li>";
			$textArea .= "<li>" . $odometer . '&nbsp;&nbsp;&nbsp;<button class="btn btn-primary" onclick="showChanger(' . "'odoChange'," . $car_id . ')">Update Odometer</button>
			<div id="odoChange' . $car_id . '" style="display:none"><form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="text" name="odo" class="form-control" value= "' . $odometer . '">
			<input type="hidden" name="this_car_id" value="' . $car_id . '">
			<input type="submit" name="odoChange" class="btn btn-primary" value="Submit"></form></div></li>
			<li>' . $status . '&nbsp;&nbsp;&nbsp;<button class="btn btn-primary" onclick="showChanger(' . "'statusChange'," . $car_id . ')">List/Unlist Car</button>
			<div id="statusChange' . $car_id . '" style="display:none">
			<p>Are you sure you want to<br> switch your cars listed status?<br> (you can change it again later)</p>
			<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="hidden" name="status" class="form-control" value= "' . $status . '">
			<input type="hidden" name="this_car_id" value="' . $car_id . '">
			<input type="submit" name="statusChange" class="btn btn-primary" value="Change status"></form></div>
			</il></ul>';
			$textArea .= '<button class="btn btn-danger" onclick="showChanger(' . "'deleter'," . $car_id . ')">Remove Car From Our Site</button>
			<div id="deleter' . $car_id . '" style="display:none">
			<p>Are you really sure you want <br>to delete this car from the site?</p>
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



//get the parts of the users account, ready to change
$userAccountArea = $username = $facebook = $street = $suburb = $postcode = $city = $country = "";
$location_id = 0;
$getuserInfoSql = "SELECT facebook, location_id FROM users WHERE users_id = " . $users_id;
if($getuserInfoSqlStmt = mysqli_prepare($link, $getuserInfoSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getuserInfoSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getuserInfoSqlStmt);
		mysqli_stmt_bind_result($getuserInfoSqlStmt, $facebook, $location_id);
		if(mysqli_stmt_num_rows($getuserInfoSqlStmt) != 1){
                    $userAccountArea = "Error, your info wasn't retrievable.";
		}
		//populate the html text field variable
		while(mysqli_stmt_fetch($getuserInfoSqlStmt)){
			
			if($location_id != 0){
				//Get the location variables based on the id
				$getLocSql = "SELECT street, suburb, postcode, city, country FROM location WHERE location_id = " . $location_id;
				if($getLocSqlStmt = mysqli_prepare($link, $getLocSql)){
		
					// Attempt to execute the prepared statement
					if(mysqli_stmt_execute($getLocSqlStmt)){
				
						// Store result, print it to the variable
						mysqli_stmt_store_result($getLocSqlStmt);
						mysqli_stmt_bind_result($getLocSqlStmt, $street, $suburb, $postcode, $city, $country);
						mysqli_stmt_fetch($getLocSqlStmt);
					}
				}
				// Close statement
				mysqli_stmt_close($getLocSqlStmt);
			}
			
			$userAccountArea .= "<ul style='list-style-type:none'><li>" . htmlspecialchars($_SESSION["username"]) . 
			'&nbsp;&nbsp;&nbsp;<button class="btn" onclick="showChanger(' . "'unameChange'," . $users_id . ')">Change Username</button>
			<div id="unameChange' . $users_id . '" style="display:none"><form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="text" name="newUname" class="form-control">
			<input type="submit" name="unameChange" class="btn btn-primary" value="Submit Username Change"></form><br><br></div></li>
			<li><button class="btn" onclick="showChanger(' . "'pwordChange'," . $users_id . ')">Change Password</button>
			<div id="pwordChange' . $users_id . '" style="display:none"><form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			Your password now
			<input type="password" name="oldPword" class="form-control"><br><br>
			New Password
			<input type="password" name="newPword1" class="form-control">
			Retype new password
			<input type="password" name="newPword2" class="form-control">
			<input type="submit" name="pwordChange" class="btn btn-primary" value="Submit Password Change"></form><br><br></div></li>
			<li>' . $facebook . '&nbsp;&nbsp;&nbsp;<button class="btn" onclick="showChanger(' . "'fbChange'," . $users_id . ')">Change Facebook Link</button>
			<div id="fbChange' . $users_id . '" style="display:none"><form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="text" name="newFb" class="form-control" value="' . $facebook . '">
			<input type="submit" name="fbChange" class="btn btn-primary" value="Change Facebook Link"></form><br><br></div></li>
			<li><h3>Your Location</h3><br>' . $street . '<br>' . $suburb . '<br>' . $postcode . '<br>' . $city . '<br>' . $country . '<br>' . '
			<button class="btn" onclick="showChanger(' . "'addressChange'," . $users_id . ')">Change Address</button>
			<div id="addressChange' . $users_id . '" style="display:none"><form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			Street<input type="text" name="street" class="form-control" value="' . $street . '">
			Suburb<input type="text" name="suburb" class="form-control" value="' . $suburb . '">
			Postcode<input type="text" name="postcode" class="form-control" value="' . $postcode . '">
			City<input type="text" name="city" class="form-control" value="' . $city . '">
			Country<input type="text" name="country" class="form-control" value="' . $country . '">
			<input type="submit" name="addressChange" class="btn btn-primary" value="Change Address"></form><br><br></div></li>
			</ul>';
			
			
		}
	}
}
// Close statement
mysqli_stmt_close($getuserInfoSqlStmt);



//it's easier to define here the button that let's your delete your account
$deleteAccountArea = '<button class="btn btn-danger" onclick="showChanger(' . "'deleter'," . "'" . htmlspecialchars($_SESSION['username']) . "'" . ')">Remove Your Account From Our Site</button>
<div id="deleter' . htmlspecialchars($_SESSION['username']) . '" style="display:none">
<p>Are you really sure you want to delete your account?</p>
<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
<input type="submit" name="delAcc" class="btn btn-danger" value="Delete"></form></div>';


//Get any incoming requests for the users cars
$status = $startdate = $enddate = $incomingReserv = "";
$reservation_id = $owner = $renter = $rented_car_id = 0;
$getReservSql = "SELECT reservation_id, status, startdate, enddate, owner, renter, car_id FROM reservation WHERE status = 'requested' AND owner = " . $users_id;
if($getReservSqlStmt = mysqli_prepare($link, $getReservSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getReservSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getReservSqlStmt);
		mysqli_stmt_bind_result($getReservSqlStmt, $reservation_id, $status, $startdate, $enddate, $owner, $renter, $rented_car_id);

		//populate the html text field variable
		while(mysqli_stmt_fetch($getReservSqlStmt)){
			
			//get the name of the renter
			$renter_name = "";
			$getRenteeSql = "SELECT username FROM users WHERE users_id = " . "'" . $renter . "'";
			if($getRenteeSqlStmt = mysqli_prepare($link, $getRenteeSql)){
			
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($getRenteeSqlStmt)){

					// Store result, print it to the variable
					mysqli_stmt_store_result($getRenteeSqlStmt);
					mysqli_stmt_bind_result($getRenteeSqlStmt, $renter_name);
					mysqli_stmt_fetch($getRenteeSqlStmt);
				}
			}
			// Close statement
			mysqli_stmt_close($getRenteeSqlStmt);
			
			//get the name of the renter
			$car_name = "";
			$getCarNameSql = "SELECT model FROM car WHERE users_id = " . $users_id;
			if($getCarNameSqlStmt = mysqli_prepare($link, $getCarNameSql)){
			
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($getCarNameSqlStmt)){

					// Store result, print it to the variable
					mysqli_stmt_store_result($getCarNameSqlStmt);
					mysqli_stmt_bind_result($getCarNameSqlStmt, $car_name);
					mysqli_stmt_fetch($getCarNameSqlStmt);
				}
			}
			// Close statement
			mysqli_stmt_close($getCarNameSqlStmt);
			
			
			$incomingReserv .= $renter_name . " wants to rent your " . $car_name . " from <br>" . $startdate . " until " . $enddate;
			$incomingReserv .= '<br><form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="hidden" name="reservation_id" value="' . $reservation_id . '">
			<input type="submit" name="acceptRes" class="btn" value="Accept"></form>
			<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="hidden" name="reservation_id" value="' . $reservation_id . '">
			<input type="submit" name="declineRes" class="btn" value="Decline"></form>';
		}
	}
}
// Close statement
mysqli_stmt_close($getReservSqlStmt);



//Get any payments this user is required to do
$status = $startdate = $enddate = $incomingPay = "";
$reservation_id = $owner = $renter = $rented_car_id = 0;
$getPaySql = "SELECT reservation_id, status, startdate, enddate, owner, renter, car_id FROM reservation WHERE status = 'accepted' AND renter = " . $users_id;
if($getPaySqlStmt = mysqli_prepare($link, $getPaySql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getPaySqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getPaySqlStmt);
		mysqli_stmt_bind_result($getPaySqlStmt, $reservation_id, $status, $startdate, $enddate, $owner, $renter, $rented_car_id);

		//populate the html text field variable
		while(mysqli_stmt_fetch($getPaySqlStmt)){
			
			//get the name of the renter
			$owner_name = "";
			$getRenterSql = "SELECT username FROM users WHERE users_id = " . "'" . $owner . "'";
			if($getRenterSqlStmt = mysqli_prepare($link, $getRenterSql)){
			
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($getRenterSqlStmt)){

					// Store result, print it to the variable
					mysqli_stmt_store_result($getRenterSqlStmt);
					mysqli_stmt_bind_result($getRenterSqlStmt, $owner_name);
					mysqli_stmt_fetch($getRenterSqlStmt);
				}
			}
			// Close statement
			mysqli_stmt_close($getRenterSqlStmt);
			
			//get the name of the renter
			$car_name = "";
			$getCarNameSql = "SELECT model FROM car WHERE users_id = " . "'" . $owner . "'";
			if($getCarNameSqlStmt = mysqli_prepare($link, $getCarNameSql)){
			
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($getCarNameSqlStmt)){

					// Store result, print it to the variable
					mysqli_stmt_store_result($getCarNameSqlStmt);
					mysqli_stmt_bind_result($getCarNameSqlStmt, $car_name);
					mysqli_stmt_fetch($getCarNameSqlStmt);
				}
			}
			// Close statement
			mysqli_stmt_close($getCarNameSqlStmt);
			
			
			$incomingPay .= $owner_name . " accepted your request to rent their " . $car_name . " from <br>" . $startdate . " until " . $enddate;
			$incomingPay .= '<br><form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="hidden" name="reservation_id" value="' . $reservation_id . '">
			<input type="submit" name="pay" class="btn" value="Pay Now"></form>
			<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="hidden" name="reservation_id" value="' . $reservation_id . '">
			<input type="submit" name="cancel" class="btn" value="Cancel Reservation"></form>';
		}
	}
}
// Close statement
mysqli_stmt_close($getPaySqlStmt);




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
			mysqli_stmt_close($newOdoSqlStmt);
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
				echo "Car deleted successfully.";
			} else{
				echo "Oops! Something went wrong. Please try again later.";
			}
		}
		// Close statement
		mysqli_stmt_close($newDeleteCarSqlStmt);
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
		// Close statement
		mysqli_stmt_close($DASqlStmt);
	}
	
	//for when user accepts a booking request
	if(isset($_POST["acceptRes"])){
		
		$reservation_id = trim($_POST["reservation_id"]);
		// Prepare an update statement
		$acceptReqSql = "UPDATE reservation SET status = 'accepted' WHERE reservation_id = " . $reservation_id;
		
		if($acceptReqSqlStmt = mysqli_prepare($link, $acceptReqSql)){
			
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
		mysqli_stmt_close($acceptReqSql);
		
	}
	
	//for when user declines a booking request
	if(isset($_POST["declineRes"])){
		$reservation_id = trim($_POST["reservation_id"]);
		// Prepare an update statement
		$declineReqSql = "UPDATE reservation SET status = 'declined' WHERE reservation_id = " . $reservation_id;
		
		if($declineReqSqlStmt = mysqli_prepare($link, $declineReqSql)){
			
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($declineReqSqlStmt)){
				/* store result */
				mysqli_stmt_store_result($declineReqSqlStmt);
				header("location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
			} else{
				echo "Oops! Something went wrong. Please try again later.";
			}
		}
		// Close statement
		mysqli_stmt_close($declineReqSql);
	}
	
	//for when user presses the pay button
	if(isset($_POST["pay"])){
		$reservation_id = trim($_POST["reservation_id"]);
		// Prepare an update statement
		$payReqSql = "UPDATE reservation SET status = 'paid' WHERE reservation_id = " . $reservation_id;
		
		if($payReqSqlStmt = mysqli_prepare($link, $payReqSql)){
			
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($payReqSqlStmt)){
				/* store result */
				mysqli_stmt_store_result($payReqSqlStmt);
				header("location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
			} else{
				echo "Oops! Something went wrong. Please try again later.";
			}
		}
		// Close statement
		mysqli_stmt_close($payReqSql);
		
		
		// Close statement
		mysqli_stmt_close($payReqSql);
	}
	
	//for when user presses the cancel payment button
	if(isset($_POST["cancel"])){
		$reservation_id = trim($_POST["reservation_id"]);
		// Prepare an update statement
		$cancelReqSql = "UPDATE reservation SET status = 'declined' WHERE reservation_id = " . $reservation_id;
		
		if($cancelReqSqlStmt = mysqli_prepare($link, $cancelReqSql)){
			
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($cancelReqSqlStmt)){
				/* store result */
				mysqli_stmt_store_result($cancelReqSqlStmt);
				header("location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
			} else{
				echo "Oops! Something went wrong. Please try again later.";
			}
		}
		// Close statement
		mysqli_stmt_close($cancelReqSql);
	}
	
	//if the user submitted a change of username
	if(isset($_POST["unameChange"])){
		
		// Validate username
		if(empty(trim($_POST["newUname"]))){
			echo "Please enter a username.";
		} else{
			$newUname = trim($_POST["newUname"]);
			
			// Prepare a select statement
			$sql = "SELECT users_id FROM users WHERE username = ?";
			
			if($stmt = mysqli_prepare($link, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "s", $newUname);
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt)){
					/* store result */
					mysqli_stmt_store_result($stmt);
					
					if(mysqli_stmt_num_rows($stmt) == 1){
						echo "This username is already taken.";
					} else{
						
						// Prepare an update statement
						$Usql = "UPDATE users SET username = ? WHERE username = '" . htmlspecialchars($_SESSION['username']) . "'";

						if($Ustmt = mysqli_prepare($link, $Usql)){
							// Bind variables to the prepared statement as parameters
							mysqli_stmt_bind_param($Ustmt, "s", $newUname);

							// Attempt to execute the prepared statement
							if(mysqli_stmt_execute($Ustmt)){
								/* store result */
								mysqli_stmt_store_result($Ustmt);
								$_SESSION['username'] = $newUname;
								header("location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
							}
						}
						mysqli_stmt_close($Ustmt);
					}
				} else{
					echo "Oops! Something went wrong. Please try again later.";
				}
			}
			 
			// Close statement
			mysqli_stmt_close($stmt);
		}
	}
	
	//if the user submitted a change of password 
	if(isset($_POST["pwordChange"])){
		
		
		// Validate password
		if(empty(trim($_POST["oldPword"])) || empty(trim($_POST["newPword1"])) || empty(trim($_POST["newPword1"]))){
			echo "Please enter all passwords.";
		} else{
			$newPword1 = trim($_POST["newPword1"]);
			$newPword2 = trim($_POST["newPword2"]);
			$oldPword = trim($_POST["oldPword"]);
			
			//check if they're the same
			if(($newPword1 == $newPword2) && ($oldPword != $newPword1)){
				
				$comparedPword = $newPword = "";
				$newPword = password_hash($newPword1, PASSWORD_DEFAULT);
				
				// Prepare a select statement
				$Psql = "SELECT password FROM users WHERE username = '" . htmlspecialchars($_SESSION['username']) . "'";
				
					if($Pstmt = mysqli_prepare($link, $Psql)){
						// Bind variables to the prepared statement as parameters
						
						// Attempt to execute the prepared statement
						if(mysqli_stmt_execute($Pstmt)){
							/* store result */
							mysqli_stmt_store_result($Pstmt);
							mysqli_stmt_bind_result($Pstmt, $comparedPword);
							mysqli_stmt_fetch($Pstmt);
						} else{
							echo "Oops! Something went wrong. Please try again later.";
						}
					}
				// Close statement
				mysqli_stmt_close($Pstmt);
				
				
				if(password_verify($oldPword, $comparedPword)) { 
					// Prepare an update statement
					$sql = "UPDATE users SET password = ? WHERE password = '" . $comparedPword . "'";

					if($stmt = mysqli_prepare($link, $sql)){
						// Bind variables to the prepared statement as parameters
						mysqli_stmt_bind_param($stmt, "s", $newPword);
						
						// Attempt to execute the prepared statement
						if(mysqli_stmt_execute($stmt)){
							/* store result */
							mysqli_stmt_store_result($stmt);
							echo "Password changed successfully.";
						} else{
							echo "Oops! Something went wrong. Please try again later.";
						}
					}
					
					// Close statement
					mysqli_stmt_close($stmt);
				} else { 
					echo "The current password you entered was wrong.";
				} 
			}else {
				echo "Please re-enter the same password.";
			}
		}
		
	}
	
	//if the user submitted a change of facebook link
	if(isset($_POST["fbChange"])){
		if(empty(trim($_POST["newFb"]))){
			echo "Please enter a URL.";
		} else {
			//check if it's a site
			$regex = '@^(http\:\/\/|https\:\/\/)?(facebook*\.)+(com*\/)+[a-z0-9][a-z0-9\-]*$@i';
			$newFb = trim($_POST["newFb"]);
			if(preg_match($regex, $newFb)){
				
				$sql = "UPDATE users SET facebook = ? WHERE username = '" . htmlspecialchars($_SESSION['username']) . "'";

				if($stmt = mysqli_prepare($link, $sql)){
					// Bind variables to the prepared statement as parameters
					mysqli_stmt_bind_param($stmt, "s", $newFb);
					
					// Attempt to execute the prepared statement
					if(mysqli_stmt_execute($stmt)){
						/* store result */
						mysqli_stmt_store_result($stmt);
						echo "Facebook link changed successfully.";
						header("location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
					} else{
						echo "Oops! Something went wrong. Please try again later.";
					}
				}
				// Close statement
				mysqli_stmt_close($stmt);
			}else {
				echo "Invalid link!";
			}
			
		}
	}
	
	//if the user submitted a change of address
	if(isset($_POST["addressChange"])){
		$street = trim($_POST["street"]);
		$suburb = trim($_POST["suburb"]);
		$postcode = trim($_POST["postcode"]);
		$city = trim($_POST["city"]);
		$country = trim($_POST["country"]);
		if(empty($street) || empty($suburb) || empty($postcode) || empty($city) || empty($country)){
			echo "Please fill out all parts of the form.";
		}else{
			if(!is_int((int)$postcode)){
				echo "The postcode must be a number.";
			}else{
				$location_id = 0;
				
				//get the location_id of this user, so that we can update the location table at the right place
				$getVerSql = "SELECT location_id FROM users WHERE username = " . "'" . htmlspecialchars($_SESSION['username']) . "'";
				if($getVerSqlStmt = mysqli_prepare($link, $getVerSql)){

					// Attempt to execute the prepared statement
					if(mysqli_stmt_execute($getVerSqlStmt)){

						// Store result, print it to the variable
						mysqli_stmt_store_result($getVerSqlStmt);
						mysqli_stmt_bind_result($getVerSqlStmt, $location_id);
						mysqli_stmt_fetch($getVerSqlStmt);
					}
				}
				// Close statement
				mysqli_stmt_close($getVerSqlStmt);
				
				if($location_id != 0){
					
					$sql = "UPDATE location SET street = ?, suburb = ?, postcode = ?, city = ?, country = ?, users_id = ? WHERE location_id = " . $location_id;
					
					if($stmt = mysqli_prepare($link, $sql)){
						// Bind variables to the prepared statement as parameters
						mysqli_stmt_bind_param($stmt, "sssssi", $street, $suburb, $postcode, $city, $country, $users_id);

						// Attempt to execute the prepared statement
						if(mysqli_stmt_execute($stmt)){
							/* store result */
							mysqli_stmt_store_result($stmt);
							echo "Address changed successfully.";
							header("location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
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
	
	if(isset($_POST["statusChange"])) {
		$status = trim($_POST["status"]);
		$this_car_id = trim($_POST["this_car_id"]);
		
		if($status == "listed"){
			$status = "unlisted";
		}else {
			$status = "listed";
		}
		
		$sql = "UPDATE car SET status = ? WHERE car_id = " . $this_car_id;
					
					if($stmt = mysqli_prepare($link, $sql)){
						// Bind variables to the prepared statement as parameters
						mysqli_stmt_bind_param($stmt, "s", $status);

						// Attempt to execute the prepared statement
						if(mysqli_stmt_execute($stmt)){
							/* store result */
							mysqli_stmt_store_result($stmt);
							echo "Status changed successfully.";
							header("location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
						} else{
							echo "Oops! Something went wrong. Please try again later.";
						}
					}
					// Close statement
					mysqli_stmt_close($stmt);
		
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
	
	<?php echo $suspendedArea; ?>
	
		<div style = "position: absolute; left: 10px;"  align = "right">
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
			
			<p><a href="add_car.php" class="btn">Add a car available for rent</a></p>
		</div>
		
		<div style = "position: absolute; right: 10px;">
		<p><?php echo $incomingReserv; ?></p>
		</div>
		
		<div align = "center">
		<p><?php echo $incomingPay; ?></p>
		</div>
		
		<div style="padding-left: 40%;padding-right: 40%;" align = "right">
		<p><?php echo $userAccountArea; ?></p>
		</div>
		
		<div style="position: absolute; left: 10px; top: 10px; border: 3px;">
			<p><a href="/car_list_main.php" class="btn">See All Cars</a></p>
			<?php echo $adminArea; ?>
		</div>
		
		<div style="position: absolute; right: 10px; bottom: 10px; border: 3px;">
			<?php echo $deleteAccountArea; ?>
		</div>
		
		<div style="position: absolute; left: 10px; bottom: 10px; border: 3px;">
			<p><a href="/logout.php" class="btn btn-danger">Sign Out of Your Account</a></p>
		</div>
	
	</div>
</body>
</html>
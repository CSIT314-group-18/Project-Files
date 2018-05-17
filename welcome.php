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
$divArea = $dummyPaymentArea = "";
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
			
			//get a cars picture
			$target_file = "car_image/" . $car_id . ".*";
			$target_file = glob($target_file);
			
			// Check if file already exists
			if (!empty($target_file)) {
				$prelimPhotoArea = "<img src='" . current($target_file) . "' alt='" . $car_id . "' style='width:200px;'>";
			}else{
				$prelimPhotoArea = "";
			}
			
			
			$textArea .= "<ul style='list-style-type:none'><li>" . $prelimPhotoArea . "</li>
			<li>" . $model . "</li><li>" . $manufacturer . "</li><li>" . $transmission . "</li>";
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
			<input type="submit" name="statusChange" class="btn btn-primary" value="Change status"></form></div></li>
			
			<button class="btn btn-primary" onclick="showChanger(' . "'photoChange'," . $car_id . ')">Change Photo</button>
			<div id="photoChange' . $car_id . '" style="display:none">
			<form enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="file" class="form-control" name="' . $car_id . '">
			<input type="hidden" name="this_car_id" value="' . $car_id . '">
			<input type="submit" name="photoChange" class="btn" value="Change Photo"></form></div>
			</ul>';
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
$userAccountArea = $username = $fname = $lname = $facebook = $street = $suburb = $postcode = $city = $country = "";
$location_id = $balance =  0;
$getuserInfoSql = "SELECT fname, lname, facebook, location_id, balance FROM users WHERE users_id = " . $users_id;
if($getuserInfoSqlStmt = mysqli_prepare($link, $getuserInfoSql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($getuserInfoSqlStmt)){
		
		// Store result, print it to the variable
		mysqli_stmt_store_result($getuserInfoSqlStmt);
		mysqli_stmt_bind_result($getuserInfoSqlStmt, $fname, $lname, $facebook, $location_id, $balance);
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
			
			$userAccountArea .= "<h3>" . $fname . " " . $lname . "</h3><ul style='list-style-type:none'><li style='border: 2px solid grey;'>Your Balance is $" . $balance . "<br></li>
			<li>" . htmlspecialchars($_SESSION["username"]) . 
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

$userAccountArea .= '<button class="btn btn-primary" onclick="showChanger(' . "'showTransLog'," . $users_id . ')">Show Your Transaction Log</button>';


//get the transactions that this account has done
$temp_owner = $temp_renter = $temp_fee = $temp_reservation_id = 0;
$temp_ownername = $temp_rentername = $temp_startdate = $temp_enddate = "";
$sql = "SELECT owner, renter, total_fee, reservation_id FROM payment WHERE owner = " . $users_id . " OR renter = " . $users_id . " ORDER BY payment_id DESC";

if($sqlStmt = mysqli_prepare($link, $sql)){
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($sqlStmt)){
		/* store result */
		mysqli_stmt_store_result($sqlStmt);
		mysqli_stmt_bind_result($sqlStmt, $temp_owner, $temp_renter, $temp_fee, $temp_reservation_id);
		$userAccountArea .= '<div id="showTransLog' . $users_id . '" style="display:none;overflow-y:scroll;bottom-margin:20;"><ul style="list-style-type:none">';
		while(mysqli_stmt_fetch($sqlStmt)){
			
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
			
			if($temp_owner == $users_id){
				
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
				
				$userAccountArea .= '<li>Got paid $' . $temp_fee . ' by ' . $temp_rentername . ' for the rental dates of '
				. date('D d/m/Y', $temp_startdate) . ' to ' . date('D d/m/Y', $temp_enddate) .  '</li>';
				
			} else if($temp_renter == $users_id){
				
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
				
				$userAccountArea .= '<li>You Paid ' . $temp_ownername . ' $' . $temp_fee . ' for the rental dates of '
				. date('D d/m/Y', $temp_startdate) . ' to ' . date('D d/m/Y', $temp_enddate) .  '</li>';
			}
			
		}
		$userAccountArea .= '</ul></div>';
	} else{
		echo "Oops! Something went wrong. Please try again later.";
	}
}
// Close statement
mysqli_stmt_close($sqlStmt);


//it's easier to define here the button that let's your delete your account
$deleteAccountArea = '<button class="btn btn-danger" onclick="showChanger(' . "'deleter'," . "'" . htmlspecialchars($_SESSION['username']) . "'" . ')">Remove Your Account From Our Site</button>
<div id="deleter' . htmlspecialchars($_SESSION['username']) . '" style="display:none">
<p>Are you really sure you want to delete your account?</p>
<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
<input type="submit" name="delAcc" class="btn btn-danger" value="Delete"></form></div>';


//Get any incoming requests for the users cars
$status = $startdate = $enddate = $incomingReserv = "";
$reservation_id = $owner = $renter = $rented_car_id = 0;
$getReservSql = "SELECT reservation_id, reservation_status, startdate, enddate, owner, renter, car_id FROM reservation WHERE reservation_status = 'requested' AND owner = " . $users_id;
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
			
			$startdate = substr($startdate, 0, -8);
			$enddate = substr($enddate, 0, -8);
			$startdate = strtotime($startdate);
			$enddate = strtotime($enddate);
			
			
			$incomingReserv .= $renter_name . " wants to rent your " . $car_name . " from <br>" . date('D d/m/Y', $startdate) . " until " . date('D d/m/Y', $enddate);
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
$reservation_id = $owner = $renter = $rented_car_id = $this_fee = 0;
$getPaySql = "SELECT reservation_id, reservation_status, startdate, enddate, owner, renter, car_id FROM reservation WHERE reservation_status = 'accepted' AND renter = " . $users_id;
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
			
			//get the amount they have to pay
			$getCarNameSql = "SELECT total_fee FROM payment WHERE reservation_id = " . $reservation_id;
			if($getCarNameSqlStmt = mysqli_prepare($link, $getCarNameSql)){
			
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($getCarNameSqlStmt)){

					// Store result, print it to the variable
					mysqli_stmt_store_result($getCarNameSqlStmt);
					mysqli_stmt_bind_result($getCarNameSqlStmt, $this_fee);
					mysqli_stmt_fetch($getCarNameSqlStmt);
				}
			}
			// Close statement
			mysqli_stmt_close($getCarNameSqlStmt);
			
			$startdate = substr($startdate, 0, -8);
			$enddate = substr($enddate, 0, -8);
			$startdate = strtotime($startdate);
			$enddate = strtotime($enddate);
			
			$incomingPay .= $owner_name . " accepted your request to rent their " . $car_name . " from <br>" . date('D d/m/Y', $startdate) . " until " . date('D d/m/Y', $enddate);
			$incomingPay .= '<br>You are required to pay $' . $this_fee . '<br>(you are liable to pay any damages to your rental if they occur)<br>
			<button onclick="modalChanger(' . $reservation_id . ')" name="pay" class="btn btn-primary">Open Payment Options</button>
		
			<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="hidden" name="reservation_id" value="' . $reservation_id . '">
			<input type="submit" name="cancel" class="btn" value="Cancel Reservation"></form>';
			
			
			
			$dummyPaymentArea .= '<div id="myModal' . $reservation_id . '" class="modal" style="display:none;"><div class="modal-content">';
		
			$dummyPaymentArea .= "<fieldset><legend>$" . $this_fee . "</legend><legend>Card Details</legend><ul style='list-style-type:none;'><li>
			<li>Paypal: <button>LOGIN</button><br><br><br>Or</li>
			<li><fieldset>
			<legend>Card Type</legend><ul style='list-style-type:none;'><li><input id=visa name=cardtype type=radio /><label for=visa>VISA</label></li><li>
			<input id=amex name=cardtype type=radio /><label for=amex>AmEx</label></li><li>
			<input id=mastercard name=cardtype type=radio /><label for=mastercard>Mastercard</label></li></ol></fieldset>
			</li><li><label for=cardnumber>Card Number</label><input id=cardnumber name=cardnumber type=number required /></li>
			<li><label for=secure>Security Code</label><input id=secure name=secure type=number required /></li><li>
			<label for=namecard>Name on Card</label><input id=namecard name=namecard type=text placeholder='Exact name as on the card' required />
			</li></ol></fieldset>";

			$dummyPaymentArea .= '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
					<input type="hidden" name="reservation_id" value="' . $reservation_id . '">
					<input type="submit" name="payButtonPressed" class="btn" value="Pay Now"></form><br><br>
					<button onclick="modalChanger(' . $reservation_id . ')" name="cancelPayment" class="btn btn-danger">Cancel Payment Process</button></div></div>';

		}
	}
}
// Close statement
mysqli_stmt_close($getPaySqlStmt);


//Get any finished car's option to be rated
$status = $startdate = $enddate = $ratingArea = "";
$reservation_id = $owner = $renter = $rented_car_id = 0;
$getPaySql = "SELECT reservation_id, reservation_status, startdate, enddate, owner, renter, car_id FROM reservation WHERE reservation_status = 'paid' AND renter = " . $users_id;
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
			
			$startdate = substr($startdate, 0, -8);
			$enddate = substr($enddate, 0, -8);
			$startdate = strtotime($startdate);
			$enddate = strtotime($enddate);
			$today = strtotime(date("Y-m-d"));
			
			if($today > $enddate){
				$ratingArea .= "You rented a " . $car_name . " from " . $owner_name . " recently, <br>from " . date('D d/m/Y', $startdate) . " until " . date('D d/m/Y', $enddate) .
				"<br>Would you like to rate this car?";
				$ratingArea .= '<br><form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
				<input type="hidden" name="reservation_id" value="' . $reservation_id . '">
				<input type="hidden" name="this_car_id" value="' . $rented_car_id . '">
				<p> 
				<span class="starRating">
				  <input id="rating5" type="radio" name="rating" value="5">
				  <label for="rating5">5</label>
				  <input id="rating4" type="radio" name="rating" value="4">
				  <label for="rating4">4</label>
				  <input id="rating3" type="radio" name="rating" value="3">
				  <label for="rating3">3</label>
				  <input id="rating2" type="radio" name="rating" value="2">
				  <label for="rating2">2</label>
				  <input id="rating1" type="radio" name="rating" value="1">
				  <label for="rating1">1</label>
				</span>
				</p>
				<p>Optional: </p>
				<input type="text" name="review" class="form-control" placeholder="Review">
				<input type="submit" name="rate" class="btn btn-primary" value="Rate"></form>
				<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
				<input type="hidden" name="reservation_id" value="' . $reservation_id . '">
				<input type="submit" name="dismiss" class="btn" value="Dismiss"></form><br><br>';
			}
			
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
	
	//if user wants to change their photo
	if(isset($_POST["photoChange"])){
		
		if(isset($_FILES[$_POST["this_car_id"]])){
			//initialise photo uploading code
			$target_dir = "car_image/";
			
			$this_car_id = trim($_POST["this_car_id"]);
			$car_as_name = $_POST["this_car_id"];
			echo $car_as_name;
			
			
			//delte any existing photo for this car
			//$old_file = $target_dir . $this_car_id . ".*";
			//$old_file = glob($old_file);
			//unlink(current($old_file));
			
			
			//convert photo to its new name from the car_id
			$temp = explode(".", $_FILES[$car_as_name]["name"]);
			$newFileName = $this_car_id . '.' . end($temp);
			$target_file = $target_dir . $newFileName;
			$uploadOk = 1;
			$imageFileType = strtolower(end($temp));
			echo $newFileName;

			//everything to do with uploading a file
			$check = getimagesize($_FILES[$car_as_name]["tmp_name"]);
			if($check !== false) {
				echo "File is an image - " . $check["mime"] . ".";
				$uploadOk = 1;
			} else {
				echo "File is not an image.";
				$uploadOk = 0;
			}
			
			// Check file size
			if ($_FILES[$this_car_id]["size"] > 500000) {
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
				if (move_uploaded_file($_FILES[$car_as_name]["tmp_name"], $target_file)) {
					echo "The file ". basename( $_FILES[$car_as_name]["name"]). " has been uploaded.";
					header("location: welcome.php");
				} else {
					echo "Sorry, there was an error uploading your file.";
				}
			}
		}else{
			echo "Please choose a photo for your car.";
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
		$acceptReqSql = "UPDATE reservation SET reservation_status = 'accepted' WHERE reservation_id = " . $reservation_id;
		
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
		$declineReqSql = "UPDATE reservation SET reservation_status = 'declined' WHERE reservation_id = " . $reservation_id;
		
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
		mysqli_stmt_close($declineReqSqlStmt);
	}
	
	
	//for when user presses the pay button
	if(isset($_POST["payButtonPressed"])){
		
		
		//update the reservation and payment tables showing the payment has been made
		$reservation_id = trim($_POST["reservation_id"]);
		
		//get the ids and amount to put them in next
		$temp_owner = $temp_renter = $temp_fee = $temp_owner_balance = $temp_renter_balance = 0;
		$sql = "SELECT owner, renter, total_fee FROM payment WHERE reservation_id = " . $reservation_id;
		
		if($sqlStmt = mysqli_prepare($link, $sql)){
			
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($sqlStmt)){
				/* store result */
				mysqli_stmt_store_result($sqlStmt);
				mysqli_stmt_bind_result($sqlStmt, $temp_owner, $temp_renter, $temp_fee);
				mysqli_stmt_fetch($sqlStmt);
			} else{
				echo "Oops! Something went wrong. Please try again later.";
			}
		}
		// Close statement
		mysqli_stmt_close($sqlStmt);
		
		//get the already existing balance from the owner, to update it
		$sql = "SELECT balance FROM users WHERE users_id = " . $temp_owner;
		
		if($sqlStmt = mysqli_prepare($link, $sql)){
			
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($sqlStmt)){
				/* store result */
				mysqli_stmt_store_result($sqlStmt);
				mysqli_stmt_bind_result($sqlStmt, $temp_owner_balance);
				mysqli_stmt_fetch($sqlStmt);
			} else{
				echo "Oops! Something went wrong. Please try again later.";
			}
		}
		// Close statement
		mysqli_stmt_close($sqlStmt);
		
		$temp_owner_balance = $temp_owner_balance + $temp_fee;
		
		//set the balance of the owner that much more
		$sql = "UPDATE users SET balance = ? WHERE users_id = " . $temp_owner;
		if($sqlStmt = mysqli_prepare($link, $sql)){
			
			mysqli_stmt_bind_param($sqlStmt, "d", $temp_owner_balance);
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($sqlStmt)){
				/* store result */
				mysqli_stmt_store_result($sqlStmt);
			} else{
				echo "Oops! Something went wrong. Please try again later.";
				
			}
		}
		// Close statement
		mysqli_stmt_close($sqlStmt);
		
		//get the already existing balance from the RENTER, to update it
		$sql = "SELECT balance FROM users WHERE users_id = " . $temp_renter;
		
		if($sqlStmt = mysqli_prepare($link, $sql)){
			
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($sqlStmt)){
				/* store result */
				mysqli_stmt_store_result($sqlStmt);
				mysqli_stmt_bind_result($sqlStmt, $temp_renter_balance);
				mysqli_stmt_fetch($sqlStmt);
			} else{
				echo "Oops! Something went wrong. Please try again later.";
			}
		}
		// Close statement
		mysqli_stmt_close($sqlStmt);
		
		$temp_renter_balance = $temp_renter_balance - $temp_fee;
		
		//set the balance of the RENTER that much more
		$sql = "UPDATE users SET balance = ? WHERE users_id = " . $temp_renter;
		
		if($sqlStmt = mysqli_prepare($link, $sql)){
			
			mysqli_stmt_bind_param($sqlStmt, "d", $temp_renter_balance);
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($sqlStmt)){
				/* store result */
				mysqli_stmt_store_result($sqlStmt);
				
			} else{
				echo "Oops! Something went wrong. Please try again later.";
				
			}
		}
		// Close statement
		mysqli_stmt_close($sqlStmt);
		
		
		// Prepare an update statement
		$payReqSql = "UPDATE reservation SET reservation_status = 'paid' WHERE reservation_id = " . $reservation_id;
		
		if($payReqSqlStmt = mysqli_prepare($link, $payReqSql)){
			
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($payReqSqlStmt)){
				/* store result */
				mysqli_stmt_store_result($payReqSqlStmt);
			} else{
				echo "Oops! Something went wrong. Please try again later.";
			}
		}
		// Close statement
		mysqli_stmt_close($payReqSqlStmt);
		
		$sql = "UPDATE payment SET payment_status = 'paid' WHERE reservation_id = " . $reservation_id;
		
		if($sqlStmt = mysqli_prepare($link, $sql)){
			
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($sqlStmt)){
				/* store result */
				mysqli_stmt_store_result($sqlStmt);
				header("location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
			} else{
				echo "Oops! Something went wrong. Please try again later.";
			}
		}
		// Close statement
		mysqli_stmt_close($sqlStmt);
		
		
	}
	
	//for when user presses the cancel payment button
	if(isset($_POST["cancel"])){
		$reservation_id = trim($_POST["reservation_id"]);
		// Prepare an update statement
		$cancelReqSql = "UPDATE reservation SET reservation_status = 'declined' WHERE reservation_id = " . $reservation_id;
		
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
		mysqli_stmt_close($cancelReqSqlStmt);
	}
	
	//for when user presses the rate button
	if(isset($_POST["rate"])){
		
		//init. all variables
		$reservation_id = trim($_POST["reservation_id"]);
		$rating = trim($_POST["rating"]);
		$review = trim($_POST["review"]);
		$this_car_id = trim($_POST["this_car_id"]);
		
		if(strlen($review) > 255){
			echo "That review is too long!";
		}else if(empty($rating)){
			echo "You need to put in a rating.";
		}else{
			
			// Prepare an update statement
			$payReqSql = "UPDATE reservation SET reservation_status = 'done' WHERE reservation_id = " . $reservation_id;
			
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
			mysqli_stmt_close($payReqSqlStmt);
			
			
			//put the rating & review into the table
			$sql = "INSERT INTO car_rating (review, rating, car_id) VALUES (?, ?, ?)";
				
			if($stmt = mysqli_prepare($link, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "sii", $review, $rating, $this_car_id);
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt)){
					/* store result */
					
					mysqli_stmt_store_result($stmt);
					
				} else{
					echo "Oops! Something went wrong. Please try again later.";
				}
			}
			// Close statement
			mysqli_stmt_close($stmt);
		}
		
	}
	
	
	//for when user presses the dismiss rating button
	if(isset($_POST["dismiss"])){
		$reservation_id = trim($_POST["reservation_id"]);
		// Prepare an update statement
		$payReqSql = "UPDATE reservation SET reservation_status = 'done' WHERE reservation_id = " . $reservation_id;
		
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
		mysqli_stmt_close($payReqSqlStmt);
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
		
		/* Everything concerned with the star rating*/
		
		.starRating:not(old){
		  display        : inline-block;
		  width          : 7.5em;
		  height         : 1.5em;
		  overflow       : hidden;
		  vertical-align : bottom;
		}

		.starRating:not(old) > input{
		  margin-right : -100%;
		  opacity      : 0;
		}

		.starRating:not(old) > label{
		  display         : block;
		  float           : right;
		  position        : relative;
		  background      : url('star-off.svg');
		  background-size : contain;
		}

		.starRating:not(old) > label:before{
		  content         : '';
		  display         : block;
		  width           : 1.5em;
		  height          : 1.5em;
		  background      : url('star-on.svg');
		  background-size : contain;
		  opacity         : 0;
		  transition      : opacity 0.2s linear;
		}

		.starRating:not(old) > label:hover:before,
		.starRating:not(old) > label:hover ~ label:before,
		.starRating:not(:hover) > :checked ~ label:before{
		  opacity : 1;
		}
		
		
		/*For the modal payment screen*/
		
		.modal {
			display: none; /* Hidden by default */
			position: fixed; /* Stay in place */
			z-index: 1; /* Sit on top */
			padding-top: 100px; /* Location of the box */
			left: 0;
			top: 0;
			width: 100%; /* Full width */
			height: 100%; /* Full height */
			overflow: auto; /* Enable scroll if needed */
			background-color: rgb(0,0,0); /* Fallback color */
			background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
		}
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
		function modalChanger(id) {
			var x = document.getElementById("myModal" + id);
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
	<?php echo $dummyPaymentArea; ?>
	
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
		
		<div style="padding-left: 40%;padding-right: 40%;" align = "right">
		<p><?php echo $incomingPay; ?></p>
		<p><?php echo $ratingArea; ?></p>
		<p><?php echo $userAccountArea; ?></p>
		</div>
		
		<div style="position: absolute; left: 10px; top: 10px; border: 3px;">
			<p><a href="/car_list_main.php" class="btn">See All Cars</a>
			<?php echo $adminArea; ?>
			<p><a href="/messages.php" class="btn">See Your Messages</a>
			</p>
		</div>
		<br><br><br>
		<a href="/logout.php" class="btn">Sign Out of Your Account</a>
		<div style="position: absolute; padding: 10px; right: 10px; bottom: 10px; border: 3px;">
			<?php echo $deleteAccountArea; ?>
		</div>
		
	
	</div>
</body>
</html>
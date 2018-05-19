<?php
// Include config file
require_once 'config.php';

// Initialize the session
session_start();


if(!isset($_SESSION['username']) || empty($_SESSION['username'])){
  header("location: login.php");
  exit;
}


$users_id = trim($_SESSION['users_id']);

//initialise variables
$already_shown_users = array();
$textArea = $msgArea = $msg = "";
$temp_sender = $temp_reciever = 0;

$Psql = "SELECT DISTINCT sentby, sentto FROM message WHERE sentby = " . $users_id . " OR sentto = " . $users_id;

if($Pstmt = mysqli_prepare($link, $Psql)){
	// Bind variables to the prepared statement as parameters
	
	// Attempt to execute the prepared statement
	if(mysqli_stmt_execute($Pstmt)){
		/* store result */
		mysqli_stmt_store_result($Pstmt);
		mysqli_stmt_bind_result($Pstmt, $temp_sender, $temp_reciever);
		
		//the variable for the user who isn't our current user
		$temp_otherusername = "";
		$get_username = 0;
		
		while(mysqli_stmt_fetch($Pstmt)){
			
			//make sure it's not our current user whose name is put on
			if($temp_sender == $users_id){
				$get_username = $temp_reciever;
			}else if($temp_reciever == $users_id){
				$get_username = $temp_sender;
			}
			
			
			$repeat = false;
			
			foreach($already_shown_users as $value){
				if($get_username == $value){
					$repeat = true;
				}
			}
			if($repeat == false){
			//get the location_id of this user, so that we can update the location table at the right place
			$getUsername = "SELECT username FROM users WHERE users_id = " . $get_username;
			if($getUsernameStmt = mysqli_prepare($link, $getUsername)){
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($getUsernameStmt)){

					// Store result, print it to the variable
					mysqli_stmt_store_result($getUsernameStmt);
					mysqli_stmt_bind_result($getUsernameStmt, $temp_otherusername);
					mysqli_stmt_fetch($getUsernameStmt);
				}
			}
			// Close statement
			mysqli_stmt_close($getUsernameStmt);
			
			$textArea .= '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
			<input type="hidden" name="otherUser" value="' . $get_username . '">
			<input type="submit" id="showMessages' . $get_username . '" name="showMessages" class="btn" value="' . $temp_otherusername . '"></form>';
			
			array_push($already_shown_users, $get_username);
			}
		}
		
		
	} else{
		echo "Oops! Something went wrong. Please try again later.";
	}
}
// Close statement
mysqli_stmt_close($Pstmt);

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
	
	if(isset($_POST["showMessages"])){
		$msgArea = $msg = "";
		$unsent_msg = "";
		$other_user = trim($_POST['otherUser']);
		$temp_sentto = $temp_sentby = 0;
		
		
		//get the messages between current user and the specified one
		$getUsername = "SELECT content, sentto, sentby FROM message WHERE sentto IN (" . $other_user . ", " . $users_id . ") OR sentby IN (" . $other_user . ", " . $users_id . ") ";
		if($getUsernameStmt = mysqli_prepare($link, $getUsername)){
			
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($getUsernameStmt)){

				// Store result, print it to the variable
				mysqli_stmt_store_result($getUsernameStmt);
				mysqli_stmt_bind_result($getUsernameStmt, $msg, $temp_sentto, $temp_sentby);
				while(mysqli_stmt_fetch($getUsernameStmt)){
					
					
					if($temp_sentto == $users_id){
						$msgArea .= '<div class="theirMsg"><p>' . $msg . '</p></div><br><br><br><br>';
						
					}else if($temp_sentby == $users_id){
						$msgArea .= '<div class="yourMsg"><p>' . $msg . '</p></div><br><br><br><br>';
						
					}
					
				}
			}
		}
		// Close statement
		mysqli_stmt_close($getUsernameStmt);
		
		$msgArea .= '</div><div style="bottom:20%; width:60%;position: absolute;left: 20%;"><form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
		<input type="hidden" name="otherUser" value="' . $other_user . '">
		<input type="text" name="msg" class="form-control" value"' . $unsent_msg . '">
		<input type="submit" name="SendMsg" class="btn" value="Send"></form>';
		
		echo '<script>setInterval(function(){
				var thisConversation = document.getElementById("showMessages' . $other_user . '"); 
				thisConversation.click();
				}, 15000);
				window.onload = function(){
					var myDiv = document.getElementById("messageArea");
					myDiv.scrollTop = myDiv.scrollHeight;
				};
				
			</script>';
	}
	
	//if the user sends a message to the owner of that car
	if(isset($_POST["SendMsg"])){
		$msg = trim($_POST["msg"]);
		$other_user = trim($_POST['otherUser']);
		if(empty($msg)){
			echo "Please enter a message.";
		}else if(strlen($msg) > 255){	
			echo "That message is too long!";
		}else{
			
			$this_users_id = trim($_SESSION['users_id']);
			
			
			$sql = "INSERT INTO message (content, sentby, sentto) VALUES (?, ?, ?)";
			
			if($stmt = mysqli_prepare($link, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "sii", $msg, $this_users_id, $other_user);
				
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
			echo '<script>window.onload = function(){
				var thisConversation = document.getElementById("showMessages' . $other_user . '"); 
				thisConversation.click();
				};
				</script>';
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
		
		.messageArea{
			position: absolute;
			width: 60%;
			left: 20%;
			overflow-y: scroll;
			height:50%;
		}
		
		.usersSection{
			position: absolute;
			left: 10px;
			border: 3px;
			
		}
		
		.theirMsg{
			text-align: center;
			padding: 10px;
			float:left;
			background-color:powderblue;
			
		}
		
		.yourMsg{
			text-align: center;
			padding: 10px;
			float:right; 
			background-color:lightgrey;
			
		}
		
    </style>
</head>
<body>
    <div class="page-header">
        <h1>Your Messages</h1>
    </div>
	<div style="position: absolute; left: 10px; top: 10px; border: 3px;">
	<p><a href="/car_list_main.php" class="btn">See All Cars</a>
	<p><a href="welcome.php" class="btn">See your Account</a></p>
	</div>
	
	<div class="usersSection">
	<p><?php echo $textArea; ?></p>
	</div>
	
	<div id="messageArea" class="messageArea">
	<?php echo $msgArea; ?>
	</div>
	
	<div style="position: absolute; left: 10px; bottom: 10px; border: 3px;">
	<p><a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a></p>
	</div>
</body>
</html>
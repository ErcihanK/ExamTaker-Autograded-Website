<?php
	session_start([
        'use_only_cookies' => 1,
        'cookie_lifetime' => 0,
        'cookie_secure' => 1,
        'cookie_httponly' => 1
    ]);
	
	$ucidErr = $passErr = "";
	$ucid = $password = "";
	
	$ucid = $_POST["ucid"];
	$pass = $_POST["pass"];
	$url = "https://web.njit.edu/~jrd62/CS490/rv/login.php";
	
	
	$ch = curl_init($url);
	$data = array(
				"requestType" => "login",
				"ucid" => $ucid,
				"pass" => $pass);
				
	$payload = json_encode($data);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	
	$json = json_decode($result);
	echo json_encode($json);
	
	$role = $json['role'];
	
	$_SESSION['role'] = $role;
	$_SESSION['ucid'] = $ucid;
	
?>
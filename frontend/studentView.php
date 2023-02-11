<?php 
	// If session doesn't exists, redirect to login page
	session_start([
        'use_only_cookies' => 1,
        'cookie_lifetime' => 0,
        'cookie_secure' => 1,
        'cookie_httponly' => 1
    ]);
	
	if (isset($_GET['ucid'])){
		$_SESSION['ucid'] = $_GET['ucid'];
	} 
	if (isset($_GET['role'])){
		$_SESSION['role'] = $_GET['role'];
	}
	if (empty($_SESSION['ucid']) || empty($_SESSION['role'])){
		header('Location: ./index.php');
	} 
	if ($_SESSION['role'] != '1') {
		header('Location: ./index.php');
	}

	ob_start();
	
?>
<html>
	<head>
		<meta charset="utf-8"/>
		<link rel="stylesheet" href="styles.css">
		<script>
			
		</script>
	</head>
	<body>
		<?php
			echo "<p id='ucid' hidden>{$_SESSION['ucid']}</p>";
		?>
		<div class="flex-container column" style="width: 100%; margin: 0%; float:left; border-right: 1px black solid;">
			<div class="flex-container row">
				<h1> <?php echo "Welcome ".$_SESSION['ucid']?> </h1>
			</div>
			<div class="flex-container row">
				<button type="button" style="height: 40px; width: 150px" onclick="location.href = 'https://web.njit.edu/~dn236/CS490/rv/studentExamList.php';">See All Exams</button>
			</div>
			<div class="flex-container row">
				<button type="button" style="height: 40px; width: 150px" onclick="location.href = 'https://web.njit.edu/~dn236/CS490/rv/logout.php';">Log Out</button>
			</div>
		</div>
		
	</body>
</html>
<?php ob_flush();?>
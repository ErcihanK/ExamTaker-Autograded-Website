<?php 
	// If session doesn't exists, redirect to login page
	session_start([
        'use_only_cookies' => 1,
        'cookie_lifetime' => 0,
        'cookie_secure' => 1,
        'cookie_httponly' => 1
    ]);
	ob_start();
	if (empty($_SESSION['ucid']) || empty($_SESSION['role'])){
		header('Location: ./index.php');
	} 
	if ($_SESSION['role'] == '2') {
		header('Location: ./teacherView.php');
	}
	$data = array();
	$data['requestType'] = 'getStudentExams';
	$data['ucid'] = $_SESSION['ucid'];
	$url = "https://web.njit.edu/~jrd62/CS490/rv/student_middle.php";
	
	$ch = curl_init($url);
	$payload = json_encode($data);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	$json = json_decode($result, true);
?>
<html>
	<head>
		<meta charset="utf-8"/>
		<link rel="stylesheet" href="styles.css">
		<script>
			function takeExam(id) {
				location.href = "".concat('https://web.njit.edu/~dn236/CS490/rv/ExamPage.php?examId=', id);
			}
			
			function viewResults(id) {
				location.href = "".concat('https://web.njit.edu/~dn236/CS490/rv/studentExamReview.php?examId=', id);
			}
		</script>
	</head>
	<body>
		<?php
			echo "<p id='ucid' hidden>{$_SESSION['ucid']}</p>";
		?>
		
		<div class="flex-container column" style="width: 100%; margin: 0%; float:left; border-right: 1px black solid;">
			<div class="flex-container row">
				<h1> Exams </h1>
			</div>
			<div class="flex-container row" style="width:98%; float:left">
				<table id="exams" style="width:100%">
					<tr>
						<th> Exam Id </th>
						<th> Status </th>
					</tr>
					<?php
						for ($i = 0; $i < count($json); $i++) {
							if ($json[$i]['status'] == 0) {
								echo "<tr>";
								echo "<td>".$json[$i]['examId']."</td>";
								echo "<td>Not Taken</td>";
								echo "<td><button type='button' id='".$json[$i]['examId']."' style='height: 40px; width: 100%' onclick='takeExam(this.id)'>Take Exam</button></td>";
								echo "</tr>";
							} else if ($json[$i]['status'] == 2) {
								echo "<tr>";
								echo "<td>".$json[$i]['examId']."</td>";
								echo "<td>Released</td>";
								echo "<td><button type='button' id='".$json[$i]['examId']."' style='height: 40px; width: 100%' onclick='viewResults(this.id)'>View Results</button></td>";
								echo "</tr>";
							}
							
						}
					?>
				</table>
			</div>
		</div>
		<div  style = "display: flex; justify-content: center; width: 100%;">
				<button type="button" style=" height: 40px; width: 150px" onclick="location.href = 'https://web.njit.edu/~dn236/CS490/rv/studentView.php';">Back</button>
		</div>
	</body>
</html>
<?php ob_flush();?>
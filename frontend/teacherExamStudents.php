<?php 
	// If session doesn't exists, redirect to login page
	session_start([
        'use_only_cookies' => 1,
        'cookie_lifetime' => 0,
        'cookie_secure' => 1,
        'cookie_httponly' => 1
    ]);
	if (empty($_SESSION['ucid']) || empty($_SESSION['role'])){
		header('Location: ./index.php');
	} 
	if ($_SESSION['role'] == '1') {
		header('Location: ./studentView.php');
	}
	
	if (isset($_GET['examId'])){
		$_SESSION['examId'] = $_GET['examId'];
	} 
	ob_start();
	
	$data = array();
	$data['requestType'] = 'getExamStatuses';
	$data['examId'] = $_SESSION['examId'];
	$url = "https://web.njit.edu/~jrd62/CS490/rv/teacher_middle_exam.php";
	
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
			function exam(id) {
				location.href = "".concat('https://web.njit.edu/~dn236/CS490/rv/teacherExamReview.php?studentId=',id);
			}
			
			function releaseExam(name) {
				var bool = confirm("Are you sure you want to release this exam?");
				if (!bool) {
					return false;
				}
				var id = name;
				let formData = new FormData();
				formData.append('requestType', 'releaseExam');
				formData.append('examId', id);
				// cURL to middle end
				fetch("https://web.njit.edu/~dn236/CS490/rv/releaseExams.php", {
					method: "POST",
					body: formData
				})
				.then((response) => {
					console.log(response);
					response.json().then((data) => {
						if (data["message"] == "Success") {
							// Redirect back after successful submission
							location.href = 'https://web.njit.edu/~dn236/CS490/rv/teacherView.php';
						}
						else {
							alert(''.concat("There was a problem releasing the exam. Please try again. Error message: ", data['error']));
						}
					})
				})
				.catch(function(error) {
					console.log(error);
				});
				return;
			} 
		</script>
	</head>
	<body>
		<?php
			echo "<p id='ucid' hidden>{$_SESSION['ucid']}</p>";
		?>
		<div class="flex-container column" style="width: 100%; margin: 0%; float:left; border-right: 1px black solid;">
			<div class="flex-container row">
				<h1> Students </h1>
			</div>
			<div class="flex-container row" style="width:98%; float:left">
				<table id="exams" style="width:100%">
					<tr>
						<th> Student Id </th>
						<th> Status </th>
						<?php
							if ($json[0]['status'] != 2) {
								echo "<th><button type='button' id=".$_GET['examId']." style='height: 100%; width: 100%;' onclick='releaseExam(this.id)'>Release Exam ".$_GET['examId']."</button>";
							}
						?>
					</tr>
					<?php
						if (!isset($json['message'])) {
							for ($i = 0; $i < count($json); $i++) {
								echo "<tr id=".$json[$i]['ucid'].">";
								echo "<td>".$json[$i]['ucid']."</td>";
								if ($json[$i]['status'] == 0) {
									echo "<td>Not Taken</td>";
									echo "<td></td>";
								} else if ($json[$i]['status'] == 1) {
									echo "<td>Graded</td>";
									echo "<td><button type='button'id='".$json[$i]['ucid']."'  style='height: 40px; width: 100%' onclick='exam(this.id)'>View Results</button></td>";
									echo "</tr>";
								} else {
									echo "<td>Released</td>";
									echo "<td><button type='button' id='".$json[$i]['ucid']."' style='height: 40px; width: 100%' onclick='exam(this.id)'>View Results</button></td>";
									echo "</tr>";
								}
							}
						}
					?>
				</table>
			</div>
			<div class="flex-container row">
				<button type="button" style=" margin: 0px 0px 0px 25px; height: 40px; width: 150px" onclick="location.href = 'https://web.njit.edu/~dn236/CS490/rv/teacherExamList.php';">Back</button>
			</div>
		</div>
		
	</body>
</html>
<?php ob_flush();?>
<?php 
	
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
	
	ob_start();
?>
<html>
	<head>
		<meta charset="utf-8"/>
		<link rel="stylesheet" href="styles.css">
		<script>
			function exam(id) {
				location.href = "".concat('https://web.njit.edu/~dn236/CS490/rv/teacherExamStudents.php?examId=', id);
			}
			
			/* function releaseExam(name) {
				var bool = confirm("Are you sure you want to release this exam?");
				if (!bool) {
					return false;
				}
				var id = name.substr(1);
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
			}  */
		</script>
	</head>
	<body>
		<?php
			echo "<p id='ucid' hidden>{$_SESSION['ucid']}</p>";
		?>
		<div class="flex-container column" style="width: 100%; margin: 0%; float:left; border-right: 1px black solid;">
			<div class="flex-container row" style="margin:0%;">
				<h1> Exams </h1>
			</div>
			<?php
				$data = array();
				$data['requestType'] = 'getExams';
				$data['ucid'] = $_SESSION['ucid'];
				$url = "https://web.njit.edu/~dn236/CS490/rv/getExams.php";
				$ch = curl_init($url);
				$payload = json_encode($data);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($ch);
				curl_close($ch);
				$json = json_decode($result, true);
				if (!isset($json['message'])) {
					for ($i = 0; $i < count($json); $i++) {
						echo "<div style='height: 50px; width:95%; display: flex; align-items: center; justify-content: center; margin: 0% 0% 0% 0%;' class='flex-container row'>";
						echo "<button class='rc' type='button' id='".$json[$i]."' style='height: 40px; width: 25%' onclick='exam(this.id)'>Exam ".$json[$i]."</button>";
						echo "</div>";
					}
					/* for ($i = 0; $i < count($json); $i++) {
						echo "<div style='height: 60px; margin: 0% 0% 0% 0%;' class='flex-container row'>";
						echo "<button type='button' id='".$json[$i]."' style='height: 40px; width: 150px' onclick='exam(this.id)'>Exam ".$json[$i]."</button>";
						echo "<button type='button' id=r".$json[$i]." style='height: 40px; width: 150px; margin: 0px 10px 0px;' onclick='releaseExam(this.id)'>Release Exam ".$json[$i]."</button>";
						echo "</div>";
					} */
				}
			?>
		</div>
		<div  style = "display: flex; justify-content: center; width: 100%;">
				<button type="button" style=" height: 40px; width: 150px" onclick="location.href = 'https://web.njit.edu/~dn236/CS490/rv/teacherView.php';">Back</button>
		</div>
		
		
	</body>
</html>
<?php ob_flush();?>
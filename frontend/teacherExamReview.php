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
	if ($_SESSION['role'] != '2') {
		header('Location: ./index.php');
	}
	if (isset($_GET['studentId'])){
		$_SESSION['studentId'] = $_GET['studentId'];
	} 
	
	$data = array();
	$data['requestType'] = 'getStudentAnswers';
	$data['examId'] = $_SESSION['examId'];
	$data['ucid'] = $_SESSION['studentId'];
	$url = "https://web.njit.edu/~jrd62/CS490/rv/teacher_middle_exam.php";
	
	$ch = curl_init($url);
	$payload = json_encode($data);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	$json = json_decode($result, true);
	
	if($json["message"]){
		$err = $json["error"];
		echo "<script> console.log(".$err.")</script>";
	}
	
	
	
	ob_start();
?>
<html>
	<head>
		<meta charset="utf-8"/>
		<link rel="stylesheet" href="styles.css">
		<script>
		function submit(){
			var table = document.getElementById("equestions");
				let formData = {};
				formData['requestType'] = 'editStudentExam';
				formData['ucid'] = document.getElementById("studentId").innerText;
				formData['examId'] = document.getElementById("examId").innerText;
				formData['questions'] = [];
				for (var i=1; i<table.rows.length-1; i++) {
					let question = {};
					var questionId = table.rows[i].id;
					var feedback = table.rows[i].cells[4].firstChild.value;
					var qtable = document.getElementById("".concat(questionId, "points"));
					var flag=2;
					
					console.log(qtable);
					question["function"] = {};
					question["colon"] = {};
					question["constraints"] = {};
					question["function"]["itemId"] = qtable.rows[0].cells[1].id;
					question["colon"]["itemId"] = qtable.rows[1].cells[1].id;
					if (qtable.rows[2].cells[0].innerHTML == "Constraint"){
						flag=3;
						question["constraints"]["itemId"] = qtable.rows[2].cells[1].id;
					}
					if (qtable.rows[0].cells[1].firstChild.value == "") {
						question["function"]["pointsEarned"] = qtable.rows[0].cells[1].firstChild.placeholder;
					}
					else {
						question["function"]["pointsEarned"] = qtable.rows[0].cells[1].firstChild.value;
					}
					if (qtable.rows[1].cells[1].firstChild.value == "") {
						question["colon"]["pointsEarned"] = qtable.rows[1].cells[1].firstChild.placeholder;
					}
					else {
						question["colon"]["pointsEarned"] = qtable.rows[1].cells[1].firstChild.value;
					}
					
					if (flag==3){
						if (qtable.rows[2].cells[1].firstChild.value == "") {
							question["constraints"]["pointsEarned"] = qtable.rows[2].cells[1].firstChild.placeholder;
						}
						else {
							question["constraints"]["pointsEarned"] = qtable.rows[2].cells[1].firstChild.value;
						}
					}
					question["testCases"] = [];
					for (var j=flag; j<qtable.rows.length; j++) {
						let temp = {};
						temp["itemId"] = qtable.rows[j].cells[1].id;
						console.log(temp["itemId"]);
						console.log(qtable.rows[j].cells[1].id);
						if (qtable.rows[j].cells[1].firstChild.value == "") {
							temp["pointsEarned"] = qtable.rows[j].cells[1].firstChild.placeholder;
						}
						else {
							temp["pointsEarned"] = qtable.rows[j].cells[1].firstChild.value;
						}
						question["testCases"].push(temp);
						console.log(temp);
					}
					question['questionId'] = questionId;
					question['feedback'] = feedback;
					formData['questions'].push(question);
					
					console.log(question);
					console.log(question['constraints']);
					console.log("one loop done");
				}
				console.log(formData);
				// cURL to middle end
				fetch("https://web.njit.edu/~jrd62/CS490/rv/teacher_middle_exam.php", {
					method: "POST",
					body: JSON.stringify(formData)
				})
				
				.then((response) => {
					console.log(response);
					if (response["message"] == "Failure") {
						console.log(response['error']);
						return false;
					}
					location.href = "".concat('https://web.njit.edu/~dn236/CS490/rv/teacherExamStudents.php?examId=',document.getElementById('examId').innerText);
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
			echo "<p id='examId' hidden>{$_SESSION['examId']}</p>";
			echo "<p id='studentId' hidden>{$_SESSION['studentId']}</p>";
		?>
		
		<div class="flex-container column" style="width: 100%; margin: 0%; float:left; border-right: 1px black solid;">
			<div class="flex-container column" style="margin: 0%; float:left;">
				<div class="flex-container row">
					<h1> <?php echo $_SESSION['studentId']."'s Exam"?> </h1>
				</div>
			</div>
			<div class="flex-container row" style="width:98%; float:left">
				<table id="equestions" style="width:100%">
					<tr>
						<th style='width:20%;'> Question </th>
						<th> Answer </th>
						<th> Points Earned </th>
						<th style='width:2%;'> Points Total </th>
						<th> Feedback </th>
					</tr>
					<?php
						$totalPointsEarned=0;
						$maxPoints=0;
						for ($i = 0; $i < count($json); $i++) {
							$questionPoints = 0;
							echo "<tr id=".$json[$i]["questionId"].">";
							echo "<td>".$json[$i]["question"]."</td>";
							echo "<td><pre style='background-color:rgb(180,180,180);'>".$json[$i]["answer"]."</pre></td>";
							echo '<td><table id="'.$json[$i]["questionId"].'points" style="width:100%">';
							$comments = explode("\n", $json[$i]["comments"]);
							
							echo '<tr><th style="width:10%;">Function Name</th>';
							echo '<td style="width:8%;" id="'.$json[$i]["function"]["itemId"].'">'."<input style='width: 50%' placeholder='".$json[$i]["function"]["pointsEarned"]."'>"."/".$json[$i]["function"]["totalSubPoints"]."</td>";
							echo '<td style="width:85%;"><textarea style="width:100%; resize:none; background-color:rgb(180,180,180);" readonly>'.$comments[0].'</textarea></td></tr>';
							$totalPointsEarned += (float)$json[$i]["function"]["pointsEarned"];
							$questionPoints += $json[$i]["function"]["pointsEarned"];
							
							echo '<tr><th style="width:10%;">Colon</th>';
							echo '<td style="width: 8%;" id="'.$json[$i]["colon"]["itemId"].'">'."<input style='width: 50%' placeholder='".$json[$i]["colon"]["pointsEarned"]."'>"."/".$json[$i]["colon"]["totalSubPoints"]."</td>";
							echo '<td style="width:85%;"><pre style="background-color:rgb(180,180,180);">'.$comments[1].'</pre></td></tr>';
							$totalPointsEarned += (float)$json[$i]["colon"]["pointsEarned"];
							$questionPoints += $json[$i]["colon"]["pointsEarned"];
							
							$flag=2;
							if(!$json[$i]["constraints"]["totalSubPoints"]==0){
								$flag=3;
								echo '<tr><th style="width:8%;">Constraint</th>';
								echo '<td style="width:8%;" id="'.$json[$i]["constraints"]["itemId"].'">'."<input style='width: 50%' placeholder='".$json[$i]["constraints"]["pointsEarned"]."'>"."/".$json[$i]["constraints"]["totalSubPoints"]."</td>";
								echo '<td style="width:85%;"><textarea style="width:100%; resize:none; background-color:rgb(180,180,180);" readonly>'.$comments[2].'</textarea></td></tr>';
								$totalPointsEarned += (float)$json[$i]["constraints"]["pointsEarned"];
								$questionPoints += $json[$i]["constraints"]["pointsEarned"];
							}
							
							$testCases = $json[$i]["testCases"];
							for ($j=0; $j < count($testCases); $j++) {
								echo '<tr><th style="min-width:100px; max-width:10%;">Test Case '.($j+1).'</th>';
								echo '<td style="width:5%;" id="'.$testCases[$j]["itemId"].'"><input style="width: 50%;" placeholder="'.$testCases[$j]["pointsEarned"].'">'.'/'.$testCases[$j]["totalSubPoints"].'</td>';
								$str = "";
								$parameters = "Parameters: ";
								$data = json_decode($testCases[$j]['data'], true);
								for ($h=0; $h < count($data['parameters']); $h++) {
									$parameters .= $data['parameters'][strval($h)]."; ";
								}
								$str .= $parameters."\nOutput: ".$data['result']."\n";
								echo '<td style="min-height:50px; width:85%"><textarea style="height:65px; width:100%; resize:none; background-color:rgb(180,180,180);" readonly>'.$str.$comments[$flag+$j].'</textarea></td></tr>';
								$totalPointsEarned += (float)$testCases[$j]["pointsEarned"];
								$questionPoints += $testCases[$j]["pointsEarned"];
							}
							echo "</table></td>";
							echo "<td style='text-align:center'>".$questionPoints."/".$json[$i]["totalPoints"]."</td>";
							echo "<td style='vertical-align:top; max-width: 400px; max-height: 500px;'><textarea style='resize:both; height:250px; max-height:500px; min-height:99%; min-width:99%; max-width:100%; margin:0px;'>".$json[$i]["feedback"]."</textarea></td>";
							echo "</tr>";
							$totalPointsEarned += (float)$json[$i]["pointsEarned"];
							$maxPoints +=  (float)$json[$i]["totalPoints"];
						}
						$percentage = ceil(100*($totalPointsEarned / $maxPoints));
						echo "<td></td><td></td><td></td><td> Percentage: ".$percentage."% </td>";
					?>
				</table>
			</div>
			<div class="flex-container row">
				<button type="button" style="height: 40px; width: 150px" onclick="submit()">Submit Changes</button>
				<button type="button" style=" margin: 0px 0px 0px 25px; height: 40px; width: 150px" onclick="location.href = 'https://web.njit.edu/~dn236/CS490/rv/teacherExamStudents.php?examId=<?php echo $_SESSION['examId']?>';">Back</button>
			</div>
		</div>
		
	</body>
</html>
<?php ob_flush();?>
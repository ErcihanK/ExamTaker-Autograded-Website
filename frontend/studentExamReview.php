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
	if ($_SESSION['role'] != '1') {
		header('Location: ./index.php');
	}
	if (isset($_GET['examId'])){
		$_SESSION['examId'] = $_GET['examId'];
	} 
	$data = array();
	$data['requestType'] = 'getStudentAnswers';
	$data['examId'] = $_SESSION['examId'];
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
			echo "<p id='examId' hidden>{$_SESSION['examId']}</p>";
		?>
		<div class="flex-container column" style="width: 100%; margin: 0%; float:left; border-right: 1px black solid;">
			<div class="flex-container column" style="margin: 0%; float:left;">
				<div class="flex-container row">
					<h1> <?php echo "Exam ".$_SESSION['examId']?> </h1>
				</div>
			</div>
			<div class="flex-container row" style="width:98%; float:left">
				<table id="equestions" style="width:100%">
					<tr>
						<th style ='width:15%;'> Question </th>
						<th> Answer </th>
						<th> Points Earned </th>
						<th> Points Total </th>
						<th> Comments </th>
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
							
							//function row
							echo '<tr><th style="min-width:100px;">Function Name</th>';
							echo '<td style="width:10%;" id="'.$json[$i]["function"]["itemId"].'">'.$json[$i]["function"]["pointsEarned"]."/".$json[$i]["function"]["totalSubPoints"]."</td>";
							echo '<td style="width:85%;"><textarea style="width:100%; resize:none; background-color:rgb(180,180,180);" readonly>'.$comments[0].'</textarea></td></tr>';
							$totalPointsEarned += (float)$json[$i]["function"]["pointsEarned"];
							$questionPoints += $json[$i]["function"]["pointsEarned"];

							//colon row
							echo '<th style="min-width:100px;">Colon</th>';
							echo '<td style="width:10%;" id="'.$json[$i]["colon"]["itemId"].'">'.$json[$i]["colon"]["pointsEarned"]."/".$json[$i]["colon"]["totalSubPoints"]."</td>";
							echo '<td style="font-size:small; width:85%;"><textarea style="width:100%; resize:none; background-color:rgb(180,180,180);" readonly>'.$comments[1].'</textarea></td></tr>';
							$totalPointsEarned += (float)$json[$i]["colon"]["pointsEarned"];
							$questionPoints += $json[$i]["colon"]["pointsEarned"];
							
							//constraints row
							$flag=2;
							if($json[$i]["constraints"]["totalSubPoints"]){
								$flag=3;
								echo '<th style="min-width:100px;">Constraint</th>';
								echo '<td style="width:10%;" id="'.$json[$i]["constraints"]["itemId"].'">'.$json[$i]["constraints"]["pointsEarned"]."/".$json[$i]["constraints"]["totalSubPoints"]."</td>";
								echo '<td style="font-size:small; width:85%;"><textarea style="width:100%; resize:none; background-color:rgb(180,180,180);" readonly>'.$comments[2].'</textarea></td></tr>';
								$totalPointsEarned += (float)$json[$i]["constraints"]["pointsEarned"];
								$questionPoints += $json[$i]["constraints"]["pointsEarned"];
							}
							
							$testCases = $json[$i]["testCases"];
							for ($j=0; $j < count($testCases); $j++) {
								echo '<tr><th style="min-width:100px">Test Case '.($j+1).'</th>';
								echo '<td style="width:10%;" id="'.$testCases[$j]["itemId"].'">'.$testCases[$j]["pointsEarned"].'/'.$testCases[$j]["totalSubPoints"].'</td>';
								$str = "";
								$parameters = "Parameters: ";
								$data = json_decode($testCases[$j]['data'], true);
								for ($h=0; $h < count($data['parameters']); $h++) {
									$parameters .= $data['parameters'][strval($h)]."; ";
								}
								$str .= $parameters."\nOutput: ".$data['result']."\n";
								echo '<td style="width:85%;"><textarea style="height:65px; width:350px; resize:none; background-color:rgb(180,180,180);" readonly>'.$str.$comments[$flag+$j].'</textarea></td></tr>';
								$totalPointsEarned += (float)$testCases[$j]["pointsEarned"];
								$questionPoints += $testCases[$j]["pointsEarned"];
							}
							echo "</table></td>";
							echo "<td>".$questionPoints."/".$json[$i]["totalPoints"]."</td>";
							echo "<td><pre style='background-color:rgb(180,180,180);'>".$json[$i]["feedback"]."</pre></td>";
							echo "</tr>";
							//$totalPointsEarned += (float)$json[$i]["pointsEarned"];
							$maxPoints +=  (float)$json[$i]["totalPoints"];
						}
						$percentage = ceil(100*($totalPointsEarned / $maxPoints));
						echo "<td></td><td></td><td></td><td> Percentage: ".$percentage."% </td>";
					?>
				</table>
			</div>
		</div>
		<div  style = "display: flex; justify-content: center; width: 100%;">
				<button type="button" style=" height: 40px; width: 150px" onclick="location.href = 'https://web.njit.edu/~dn236/CS490/rv/studentExamList.php';">Back</button>
		</div>
	</body>
</html>
<?php ob_flush();?>
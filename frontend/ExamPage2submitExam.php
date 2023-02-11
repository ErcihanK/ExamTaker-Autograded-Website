<?php	
	session_start();
	if (empty($_SESSION['ucid']) || empty($_SESSION['role'])){
		header('Location: ./index.php');
	} 
	if ($_SESSION['role'] == '2') {
		header('Location: ./teacherView.php');
	}
	
	$url = "https://web.njit.edu/~jrd62/CS490/rv/student_middle.php";
	
	$ch = curl_init($url);
	$data = array();
	$data['requestType'] = 'submitStudentExam';
	$data['ucid'] = $_POST['ucid'];
	$data['examId'] = $_POST['examId'];
	$data['questions'] = $_POST['questions'];
	$payload = json_encode($data);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	
	echo $result;
	
?>
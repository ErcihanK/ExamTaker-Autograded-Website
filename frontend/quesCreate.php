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
	
	ob_start();
?>
<html>
	<head>
		<meta charset="utf-8"/>
		<link rel="stylesheet" href="styles.css">
		<script>
			function clearInp() {
				elements = document.getElementsByTagName("input");
				for (var i=0, elm; elm=elements.item(i++);) {
					if (elm.type == 'number') {
						elm.value = '';
					}
				}
			}
			
			function create(){
				let formData = {};
				formData['requestType'] = 'newQuestion';
				formData['question'] = document.getElementById('question').value;
				formData['functionName'] = document.getElementById('functionName').value;
				formData['difficulty'] = document.getElementById('difficulty').value;
				formData['constraints'] = document.getElementById('constraint').value;
				formData['tag'] = document.getElementById('tag').value;
				formData['testCases'] = [];
				numTestCases = document.getElementById('numTestCases').value;
				for (var i=0; i<numTestCases; i++) {
					let data = {};
					var testCase = {};
					var argc = document.getElementById("".concat('input ', (i+1).toString())).value;
					data['argc'] = argc;
					data['parameters'] = [];
					var arg = document.getElementById("".concat('argument ', (i+1).toString()));
					for (var j=0; j<argc; j++) {
						data['parameters'].push(arg.children[j].children[1].value);
					}
					var output = document.getElementById("".concat('output ', (i+1).toString())).value;
					data['result'] = output;
					testCase['data'] = data;
					formData['testCases'].push(testCase);
				}
				// cURL to middle end
				fetch("https://web.njit.edu/~dn236/CS490/rv/CreateQuestion2.php", {
					method: "POST",
					body: JSON.stringify(formData)
				})
				.then((response) => {
					console.log(response);
					response.json().then((data) => {
						
						location.href = 'https://web.njit.edu/~dn236/CS490/rv/teacherView.php';
					})
				})
				.catch(function(error) {
					console.log(error);
					return false;
				});
			}
			
			function argument(value, id) {
				if (!isNaN(value)){
					var num = id.replace("input", "argument");
					document.getElementById(num).innerHTML = "";
					for (var i = 1; i<= value; i++) {
						var row = document.createElement("div");
						row.setAttribute("class", "flex-container row");
						row.setAttribute("style", "width:100%");
						var label = document.createElement("label");
						label.setAttribute("style", "width:50%");
						label.textContent = ''.concat("Argument ", i.toString());
						row.appendChild(label);
						var input = document.createElement("input");
						input.setAttribute("style", "width:50%");
						input.setAttribute("type", "text");
						input.required = true;
						row.appendChild(input);
						document.getElementById(num).appendChild(row);
					}
				}
				else {
					document.getElementById(id).value = "";
				}
				return;
			}
			
			function testCases(value, id) {
				if (!isNaN(value)){
					document.getElementById("testCases").innerHTML = "";
					if(value>1 && value <7){
						for (var i = 1; i<= value; i++) {
							var col = document.createElement("div");
							col.setAttribute("class", "flex-container column");
							col.setAttribute("style", "width:95%; border:1px black solid");
							col.setAttribute("id", ''.concat("testCase ",i.toString()));
							var title = document.createElement("label");
							title.textContent = ''.concat("Test Case ", i.toString());
							col.appendChild(title);
							var row = document.createElement("div");
							row.setAttribute("class", "flex-container row");
							row.setAttribute("style", "width:95%");
							var label = document.createElement("label");
							label.setAttribute("style", "width:50%");
							label.textContent = "# of Arguments";
							row.appendChild(label);
							var input = document.createElement("input");
							input.setAttribute("style", "width:50%");
							input.setAttribute("type", "number");
							input.setAttribute("min", "1");
							input.setAttribute("id", ''.concat("input ",i.toString()));
							input.setAttribute("oninput", "argument(this.value, this.id)")
							input.defaultValue = "";
							input.required = true;
							row.appendChild(input);
							col.appendChild(row);
							var c2 = document.createElement("div");
							c2.setAttribute("class", "flex-container column");
							c2.setAttribute("style", "width:95%");
							c2.setAttribute("id", ''.concat("argument ",i.toString()));
							col.appendChild(c2);
							var r2 = document.createElement("div");
							r2.setAttribute("class", "flex-container row");
							r2.setAttribute("style", "width:95%");
							var l2 = document.createElement("label");
							l2.setAttribute("style", "width:50%");
							l2.textContent = "Output";
							r2.appendChild(l2);
							var i2 = document.createElement("input");
							i2.setAttribute("style", "width:50%");
							i2.setAttribute("type", "text");
							i2.setAttribute("id", ''.concat("output ",i.toString()));
							i2.required = true;
							r2.appendChild(i2);
							col.appendChild(r2);
							document.getElementById("testCases").appendChild(col);
						}
					}
					else {
					document.getElementById(id).value = "";
					}
						
				}
				else {
					document.getElementById(id).value = "";
				}
				return;
			}
			
			function filter() {
				var diff = document.getElementById("qdifficulty").value;
				var tag = document.getElementById("qtag").value;
				var constraint = document.getElementById("qconstraint").value;
				var keyword = document.getElementById("keyword").value;
				let formData = new FormData();
				formData.append('requestType', 'getQuestions');
				formData.append('difficulty', diff);
				formData.append('constraints', constraint);
				formData.append('tag', tag);
				formData.append('keyword', keyword);
				for (var p of formData) {
				  console.log(p);
				}
				// cURL to middle end
				fetch("https://web.njit.edu/~dn236/CS490/rv/CreateExam2getQuestions.php", {
					method: "POST",
					body: formData
				})
				.then((response) => {
					console.log(response);
					response.json().then((data) => {
						console.log(data["message"]);
						console.log(data["error"]);
						var questions = document.getElementById('questions');
						console.log(questions);
						while (questions.childNodes.length > 2) {
							questions.removeChild(questions.lastChild);
						}
						if (data.hasOwnProperty('message')) {
							console.log(data['error']);
							return;
						}
						console.log(data['state']);
						var count = Object.keys(data).length;
						for (var i=0; i<count; i++) {
							var tr = document.createElement('tr');
							tr.setAttribute("id", data[i]['questionId']);
							var td = document.createElement('td');
							td.textContent = data[i]['question'];
							tr.appendChild(td);
							var tb = document.createElement('td');
							document.getElementById('questions').appendChild(tr);
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
	<body onload="clearInp()">
		<div class="flex-container column" style="width: 50%; margin: 0%; float:left; border-right: 1px rgb(100,100,100); solid;">
			<div class="flex-container column" style="width:100% margin: 0%; float:left;">
				<div class="flex-container row">
					<h1 class="arial";> New Question </h1>
				</div>
			</div>
			<form onsubmit="create(); return false;" style="width: 98%">
				<div class="flex-container column" style="width: 100%;margin: 0%; float:right;" id="form">
					<div class="flex-container row" style="width: 98%;">
						<label style="width: 150px;">Question:</label>
						<textarea style="width: 70%; resize:vertical" id="question" required autofocus></textarea>
					</div>
					<div class="flex-container row" style="width: 98%;">
						<label style="width: 150px;">Function Name:</label>
						<input style="width: 70%; text-align: left;" type="text" id="functionName" required/>
					</div>
					<div class="flex-container row" style="width: 98%;">
						<label style="width: 150px;">Difficulty:</label>
						<select style="width: 70%;" id="difficulty" required>
							<option value="" selected></option>
							<option value="Easy">Easy</option>
							<option value="Medium">Medium</option>
							<option value="Hard">Hard</option>
						</select>
					</div>
					<div class="flex-container row" style="width: 98%;">
						<label style="width: 150px;">Constraint:</label>
						<select style="width: 70%;" id="constraint">
							<option value="" selected></option>
							<option value="for">for</option>
							<option value="while">while</option>
							<option value="print">print</option>
							<option value="if">if</option>
							
						</select>
					</div>
					<div class="flex-container row" style="width: 98%;">
						<label style="width: 150px;">Tag:</label>
						<select style="width: 70%;" id="tag">
							<option value="none" selected></option>
							<option value="Lists">Lists</option>
							<option value="Addition">Addition</option>
							<option value="Recursion">Recursion</option>
							<option value="Multiplication">Multiplication</option>
							<option value="Subtraction">Subtraction</option>
							<option value="Division">Division</option>
							<option value="Modulus">Modulus</option>
							<option value="Operations">Operations</option>
						</select>
					</div>
					<div class="flex-container row" style="width: 98%;">
						<label style="width: 150px;"># of Test Cases:</label>
						<input style=" text-align: left; width: 70%;" type="number" id="numTestCases" min="2" max ="6" value = "" oninput="testCases(this.value, this.id)" required />
					</div>
					<div class="flex-container column" style="width: 98%;" id="testCases">
					</div>
					<div class="flex-container row">
						<input class='rc' type="submit" style=" height: 40px; width: 150px" value="Submit Question">
					</div>
				</div>
			</form>
		</div>
		<div class="flex-container column" style="width: 50%; margin: 0%; float:right; border-left: 0px black solid;">
		
			<div class="flex-container column" style="margin: 0%; float:left;">
				<div class="flex-container row">
					<h1 class="arial"> Question Bank </h1>
				</div>
			</div>
			<div id="filters" class="flex-container row" style="width:100%; float:left">
				<label> &nbsp Difficulty: </label>
				<select id="qdifficulty">
					<option value="" selected></option>
					<option value="Easy">Easy</option>
					<option value="Medium">Medium</option>
					<option value="Hard">Hard</option>
				</select>
				<label> &nbsp Constraint: </label>
				<select id="qconstraint">
					<option value="" selected></option>
					<option value="for">for</option>
					<option value="while">while</option>
					<option value="print">print</option>
					<option value="if">if</option>
				</select>
				<!--- Values will be changed --->
				<label> &nbsp Tag: </label>
				<select id="qtag">
					<option value="" selected></option>
					<option value="Lists">Lists</option>
					<option value="Addition">Addition</option>
					<option value="Recursion">Recursion</option>
					<option value="Multiplication">Multiplication</option>
					<option value="Subtraction">Subtraction</option>
					<option value="Division">Division</option>
					<option value="Modulus">Modulus</option>
					<option value="Operations">Operations</option>
				</select>
				<label> &nbsp Keyword: </label>
				<input style="text-align: left;" type="text" id="keyword"> </input>
				<button type="button" style="height: 27px; width: 80px" onclick="filter()">Filter</button>
			</div>
			<div class="flex-container row" style="width:98%; float:left">
				<table id="questions" style="width:100%">
					<tr>
						<th> Question </th>
					</tr>
				</table>
			</div>
		</div>
		<div  style = "display: flex; justify-content: center; width: 100%;">
				<button type="button" style=" height: 40px; width: 150px" onclick="location.href = 'https://web.njit.edu/~dn236/CS490/rv/teacherView.php';">Back</button>
		</div>
	</body>
</html>
<?php ob_flush();?>
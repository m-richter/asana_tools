
<?php
	session_start();
?>


<!DOCTYPE html>
<html>
	<body>

		<?php
			require 'vendor/autoload.php';
			require 'credentials.php';
			include 'asana_tools.php';


			$person2 = array("id" => "445896290018315", "name" => "person2");

			$client = Asana\Client::accessToken($_SESSION['ASSANA_ACCESS_TOKEN']);
			$me = $client->users->me();
			echo "Hello " . $me->name . "<br>";

			$workspaceId = $me->workspaces[0]->id;

			// deleteProjects($workspaceId);			


			$projects = $client->projects->findByWorkspace($workspaceId, null, array('iterator_type' => false));
			$projectId = $projects->data[0]->id;
			$project = $client->projects->findById($projectId);




			// $project = createProject();
			// $project2 = copyProject($project->id);


			// echo "<br><br>";
			// var_dump($project);
			// echo "<br><br>";	


			// $task = createTask(array($project->id, $project2->id), "double task");
			// $task = createTask(array($project2->id), "two task");;

			// $subtask = createSubTask($task->id);
			// $subtask2 = createSubTask($task->id, "sub Task");
			// $subtask3 = createSubTask($subtask2->id, "sub Task GO");


			// $copiedTask = copyTask($task->id, TRUE);


			$project2 = copyProject($project->id);

		





		// try {
		// 	$client->projects->addMembers($projectCopy->id, array("members" => $membersArray));
		// } catch (Asana\Errors\InvalidRequestError $e) {
		//   	var_dump($e->response->body);
		// }
		    
		
		?>
	</body>
</html> 
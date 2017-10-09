
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

			$tools = new AsanaTools();			


			$projects = $client->projects->findByWorkspace($workspaceId, null, array('iterator_type' => false));
			$projectId = $projects->data[0]->id;
			$project = $client->projects->findById($projectId);




			// $project = $tools->createProject();
			// $project2 = $tools->createProject();
			// $project2 = $tools->copyProject($project->id);


			// echo "<br><br>";
			// var_dump($project);
			// echo "<br><br>";	


			// $task = $tools->createTask(array($project->id), "double task");
			// $task = $tools->createTask(array($project2->id), "two task");;
// 
			// $subTask = $tools->createSubTask($task->id);
			// $subtask2 = $tools->createSubTask($task->id, "sub Task");
			// $subtask3 = $tools->createSubTask($subtask2->id, "sub Task GO");


			// $copiedTask = $tools->copyTask($task->id, array($project2->id, $project->id));
			
			// $copiedTask2 = $tools->copyTask($subTask->id, array($copiedTask->id) ,TRUE);

			$project2 = $tools->copyProject($project->id);

		





		// try {
		// 	$client->projects->addMembers($projectCopy->id, array("members" => $membersArray));
		// } catch (Asana\Errors\InvalidRequestError $e) {
		//   	var_dump($e->response->body);
		// }
		    
		
		?>
	</body>
</html> 
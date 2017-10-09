<?php  

/**
* 
*/
class AsanaTools
{
	


	/**
     * Creates a project in the globally selected workspace.
     * 
     * Returns the full record of the newly created project.
     *
     * @param  name The name for the project.
     * @return Retruns the full record of the newly created project.
     */
	public function createProject ($name = "new project") {
		global $workspaceId;
		global $client;
		$project = $client->projects->createInWorkspace($workspaceId, array('name' => $name, "public" => TRUE));
		echo "Created project with id: " . $project->id . " and name: \"" . $project->name . "\"<br>";
		
		return $project;
	} 


	/**
     * Creates a task.
     * 
     * Returns the full record of the newly created task.
     *
     * @param  projectsArr Array of projects (as ID) the task is associated with.
     * @param  name The name for the project.
     * @return Returns the full record of the newly created task.
     */
	public function createTask ($projectsArr, $name = "new Task") {
		global $client;
		global $me;
		global $workspaceId;

		$task = $client->tasks->createInWorkspace($workspaceId, array('name' => $name, "projects" => $projectsArr, "assignee" => $me));
		echo "Created task with id: " . $task->id . " and name: \"" . $task->name . "\"<br>";
		return $task;
	}

	
	/**
     * Creates a subtask.
     * 
     * Returns the full record of the newly created subtask.
     *
     * @param  parentTaskId ID of the parent task for the newly created subtask.
     * @param  name The name for the new subtask.
     * @return Returns the full record of the newly created subtask.
     */
	public function createSubTask ($parentTaskId, $name = "new subTask"){
		global $client;
		global $project;
		global $me;
		// echo "<br><br>parentTask<br>"; var_dump($parentTask);
		$subTask = $client->tasks->addSubtask($parentTaskId, array('name' => $name));
		// echo "Created sub task in task \"" . $parentTask->name . "\" with id: " . $subTask->id . " and name \"" . $subTask->name . "\" <br>";

		return $subTask;

	}


	/**
     * Copies a task.
     * 
     * Returns the full record of the newly created copy.
     *
     * @param  originalTaskId ID of the task to be copied.
     * @param  $projectIdArr Array of projects the newly copied task is associated with. If NULL the associated projects will be the same as for the original task.     
     * @param  deep If set to TRUE subtasks will be copied as well.
     * @return Returns the full record of the newly copied task.
     */
	public function copyTask($originTaskId, $projectIdArr = NULL, $deep = TRUE){
		global $client;
		global $workspaceId;

		$originTask = $client->tasks->findById($originTaskId);


		unset($originTask->created_at, $originTask->completed_at, $originTask->hearts, $originTask->modified_at, $originTask->num_hearts, $originTask->workspace,  $originTask->memberships, $originTask->parent);	


		// disable copying of followers
		// unset($originTask->followers);

		// manipulate name
		// $originTask->name = "Copy of " . $originTask->name;



		$taskArray = json_decode(json_encode($originTask), true);


		// if applied on $projectIdArr === null add the same projects as in the origin task; applied on an empty array will set no projects
		if($projectIdArr===NULL){
			$projectIdArr = array();
			foreach ($taskArray["projects"] as $project) {
				array_push($projectIdArr , $project["id"]);
			}

		}

		$taskArray["projects"] = $projectIdArr;

		// copy tags
		if($taskArray["tags"]!=NULL){
			$tagsArray = array();
			foreach ($taskArray["tags"] as $tags) {
				array_push($tagsArray , $tags["id"]);
			}
			$taskArray["tags"] = $tagsArray;
		}	


		$taskCopy = $client->tasks->createInWorkspace($workspaceId, $taskArray);
		// echo "Copied task with id: " . $taskId . ": task with id: " . $taskCopy->id . " and name: \"" . $taskCopy->name . "\"<br>";


		if($deep){
			$this->copySubTasks($originTask->id, $taskCopy->id);
		}

		return $taskCopy;

	}

	/**
     * Copies all subtasks of a given task (incl. deep subtasks).
     * 
     * Returns TRUE if succesful.
     *
     * @param  fromTaskId ID of the parent task of the subtask(s) to be copied.
     * @param  toTaskId ID of the task for the subtask(s) to be copied to.
     * @return Returns TRUE if succesful.
     */
	public function copySubTasks($fromTaskId, $toTaskId){
		global $client;


		// retrieve subtasks
		$subTasks = $client->tasks->subTasks($fromTaskId, null, array("iterator_type" => false));

		// if there are no subtasks abort
		if(!$subTasks->data){return;}



		$subTasks = json_decode(json_encode($subTasks), true);

		// iterate over subtasks
		foreach (array_reverse($subTasks['data']) as $subTask) {
			
			$subTask = $client->tasks->findById($subTask["id"]);

			unset($subTask->created_at, $subTask->completed_at, $subTask->hearts, $subTask->modified_at, $subTask->num_hearts, $subTask->workspace,  $subTask->memberships, $subTask->parent);	

			// uncomment to disable copying of followers
			// unset($subTask->followers);			

			$subTaskArray = json_decode(json_encode($subTask), true);

			// copy tags
			if($subTaskArray["tags"]!=NULL){
				$tagsArray = array();
				foreach ($subTaskArray["tags"] as $tags) {
					array_push($tagsArray , $tags["id"]);     
				}
				$subTaskArray["tags"] = $tagsArray;
			}

			$newSubTask = $client->tasks->addSubtask($toTaskId, $subTaskArray);
			$this->copySubTasks($subTask->id, $newSubTask->id);
		}

		return true;
	}

	/**
     * Copies a project.
     * 
     * Returns the full record of the newly created project.
     *
     * @param  targetProjectId ID of the project to be copied.
     * @param  deep If set to TRUE tasks and subtasks will be copied as well.
     * @return Returns the full record of the newly copied project.
     */
	public function copyProject($targetProjectId, $deep = TRUE){

		global $workspaceId;
		global $client;

		$retrievedProject = $client->projects->findById($targetProjectId);

		// save member data to add to new copy
		$members = $retrievedProject->members;
		$members = json_decode(json_encode($members), true);
		$membersArray = array();
		foreach ($members as $member) {
			array_push($membersArray, $member["id"]);
		}



		unset($retrievedProject->modified_at, $retrievedProject->members, $retrievedProject->custom_fíeld_settings, $retrievedProject->workspace, $retrievedProject->created_at);


		unset($retrievedProject->current_status); //results in ServerError


		$projectArray = json_decode(json_encode($retrievedProject), true);
		$projectArray["name"] = "Copy of " . $projectArray["name"];
		$projectCopy = $client->projects->createInWorkspace($workspaceId, $projectArray);

		// add members
		$client->projects->addMembers($projectCopy->id, array("members" => $membersArray));
		





		// if applied on $deep == true copy all tasks to the newly created project (deep copy of tasks, including subtasks)
		if($deep){
			$retrievedTasks = $client->tasks->findByProject($retrievedProject->id, NULL, array('iterator_type' => false));
			$retrievedTasksArray = json_decode(json_encode($retrievedTasks), true);

			// reverse the array to keep original order intact
			foreach (array_reverse($retrievedTasksArray["data"]) as $task) {
				$this->copyTask($task["id"], array($projectCopy->id));
			}
		}



		return $projectCopy;

	}


	public function deleteProjects($wspcId){
		global $client;

		$projectIter = $client->projects->findByWorkspace($wspcId);
		$projectIter->rewind();

		while( $projectIter->valid()){
			$retrievedProject = $projectIter->current();
			$client->projects->delete($retrievedProject->id);

			$projectIter->next();
		}
	}

	public function copyProjects($wspcId){

		global $client;

		$projectIter = $client->projects->findByWorkspace($wspcId);									

		$projectIter->rewind();

		while( $projectIter->valid()){
			
			$this->copyProject(($projectIter->current())->id);

			$projectIter->next();

		}
	}
}

?>
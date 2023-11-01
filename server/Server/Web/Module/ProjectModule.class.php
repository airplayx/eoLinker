<?php
/**
 * @name EOLINKER ams open source，EOLINKER open source version
 * @link https://global.eolinker.com/
 * @package EOLINKER
 * @author www.eolinker.com eoLinker Ltd.co 2015-2018
 * 
 * eoLinker is the world's leading and domestic largest online API interface management platform, providing functions such as automatic generation of API documents, API automated testing, Mock testing, team collaboration, etc., aiming to solve the problem of low development efficiency caused by separation of front and rear ends.
 * If you have any problems during the process of use, please join the user discussion group for feedback, we will solve the problem for you with the fastest speed and best service attitude.
 *
 * 
 *
 * Website：https://global.eolinker.com/
 * Slack：eolinker.slack.com
 * facebook：@EoLinker
 * twitter：@eoLinker
 */

class ProjectModule
{

    public function __construct()
    {
        @session_start();
    }

    /**
     * Get User Type
     *
     * @param $projectID int
     *            ProjectID
     * @return bool|int
     */
    public function getUserType(&$projectID)
    {
        $dao = new AuthorizationDao();
        $result = $dao->getProjectUserType($_SESSION['userID'], $projectID);
        if ($result === FALSE) {
            return -1;
        }
        return $result;
    }

    /**
     * Create Project
     *
     * @param $projectName string
     *            
     * @param $projectType int
     *            
     * @param $projectVersion float
     *         
     * @return bool|int
     */
    public function addProject(&$projectName, &$projectType = 0, &$projectVersion = 1.0)
    {
        $projectDao = new ProjectDao();
        $projectInfo = $projectDao->addProject($projectName, $projectType, $projectVersion, $_SESSION['userID']);
        if ($projectInfo) {
            $groupDao = new GroupDao();
            $groupName = 'Default Group';
            $groupDao->addGroup($projectInfo['projectID'], $groupName);
            $status_code_group_dao = new StatusCodeGroupDao();
            $status_code_group_dao->addGroup($projectInfo['projectID'], $groupName);

            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($projectInfo['projectID'], $_SESSION['userID'], ProjectLogDao::$OP_TARGET_PROJECT, $projectInfo['projectID'], ProjectLogDao::$OP_TYPE_UPDATE, "Create Project", date("Y-m-d H:i:s", time()));

            return $projectInfo;
        } else {
            return FALSE;
        }

    }

    /**
     * Delete Project
     *
     * @param $projectID int
     *            ProjectID
     * @return bool
     */
    public function deleteProject(&$projectID)
    {
        $dao = new ProjectDao();
        if ($dao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return $dao->deleteProject($projectID);
        } else
            return FALSE;
    }

    /**
     * Get PROJECT LIST
     *
     * @param $projectType int
     *           
     * @return bool|array
     */
    public function getProjectList(&$projectType = -1)
    {
        $dao = new ProjectDao();
        return $dao->getProjectList($_SESSION['userID'], $projectType);
    }

    /**
     * Edit Project
     *
     * @param $projectID int
     *            
     * @param $projectName string
     *            
     * @param $projectType int
     *            
     * @param $projectVersion float
     *           
     * @return bool
     */
    public function editProject(&$projectID, &$projectName, &$projectType, &$projectVersion = 1.0)
    {
        $dao = new ProjectDao();
        if ($dao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $result = $dao->editProject($projectID, $projectName, $projectType, $projectVersion);
            if ($result) {
                
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_PROJECT, $projectID, ProjectLogDao::$OP_TYPE_UPDATE, "Edit Project Information:{$projectName}", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * Get PROJECT Info
     *
     * @param $projectID int
     *            ProjectID
     * @return bool|array
     */
    public function getProject(&$projectID)
    {
        $dao = new ProjectDao();
        if ($dao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $info = $dao->getProject($projectID, $_SESSION['userID']);
            
            $log_dao = new ProjectLogDao();
            $log_info = $log_dao->getLogInADay($projectID);
            $info = array_merge($info, $log_info);
            return $info;
        } else
            return FALSE;
    }

    /**
     * Update Project
     *
     * @param $projectID int
     *            ProjectID
     * @return bool
     */
    public function updateProjectUpdateTime(&$projectID)
    {
        $dao = new ProjectDao();
        if ($dao->updateProjectUpdateTime($projectID))
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Export Project
     *
     * @param $projectID int
     *            ProejctID
     * @return bool|string
     */
    public function dumpProject(&$projectID)
    {
        $dao = new ProjectDao();
        if ($dao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $dumpJson = json_encode($dao->dumpProject($projectID));
            $fileName = 'eoLinker_dump_' . $_SESSION['userName'] . '_' . time() . '.export';
            if (file_put_contents(realpath('./dump') . DIRECTORY_SEPARATOR . $fileName, $dumpJson)) {
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_PROJECT, $projectID, ProjectLogDao::$OP_TYPE_ADD, "Export Project", date("Y-m-d H:i:s", time()));
                return $fileName;
            }

        } else
            return FALSE;
    }
    /**
     * 获取用户项目权限
     * @param int $space_id 空间ID
     * @param int $user_id 用户ID
     * @param string $project_hash_key 项目hashkey
     */
    public function getUserProjectPermission(&$project_hash_key)
    {
    	$dao = new ProjectDao();
    	$result = $dao -> getUserProjectPermissionFromDB($project_hash_key,$_SESSION['userID']);
    	if($result)
    	{
    		return $result;
    	}
    	else
    	{
    		return FALSE;
    	}
    }
    /**
     * Get api Number
     *
     * @param $projectID int
     *            ProjectID
     * @return bool|int
     */
    public function getApiNum(&$projectID)
    {
        $dao = new ProjectDao();
        if ($dao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return $dao->getApiNum($projectID);
        } else
            return FALSE;
    }

    /**
     * Get PROJECT Log
     * @param $project_id int ProjectID
     * @param $page int Page
     * @param $page_size int Info per page
     * @return bool|array
     */
    public function getProjectLogList(&$project_id, &$page, &$page_size)
    {
        $user_id = $_SESSION['userID'];

        $dao = new ProjectDao();

        if ($dao->checkProjectPermission($project_id, $user_id)) {
            $log_dao = new ProjectLogDao();
            $log_list = $log_dao->getOperationLogList($project_id, $page, $page_size, 7);
            return $log_list;
        } else
            return FALSE;
    }
}

?>
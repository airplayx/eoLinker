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

class StatusCodeGroupModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * Get User Type
     * @param $groupID int GroupID
     * @return bool|int
     */
    public function getUserType(&$groupID)
    {
        $groupDao = new StatusCodeGroupDao();
        $projectID = $groupDao->checkStatusCodeGroupPermission($groupID, $_SESSION['userID']);
        if (empty($projectID)) {
            return -1;
        }
        $dao = new AuthorizationDao();
        $result = $dao->getProjectUserType($_SESSION['userID'], $projectID);
        if ($result === FALSE) {
            return -1;
        }
        return $result;
    }

    /**
     * Add group
     * @param $projectID int ProjectID
     * @param $groupName string Group name
     * @param $parentGroupID int parent groupID
     * @param $isChild
     * @return bool|int
     */
    public function addGroup(&$projectID, &$groupName, &$parentGroupID, &$isChild)
    {
        $projectDao = new ProjectDao;
        $statusCodeGroupDao = new StatusCodeGroupDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            if (is_null($parentGroupID)) {
                $result = $statusCodeGroupDao->addGroup($projectID, $groupName);
                if ($result) {
                    
                    $log_dao = new ProjectLogDao();
                    $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE_GROUP, $result, ProjectLogDao::$OP_TYPE_ADD, "Add Status Code Group:'$groupName'", date("Y-m-d H:i:s", time()));
                    return $result;
                } else {
                    return FALSE;
                }
            } else {
                $result = $statusCodeGroupDao->addChildGroup($projectID, $groupName, $parentGroupID, $isChild);
                if ($result) {
                    $parent_group_name = $statusCodeGroupDao->getGroupName($parentGroupID);
                   
                    $log_dao = new ProjectLogDao();
                    $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE_GROUP, $result, ProjectLogDao::$OP_TYPE_ADD, "Add Status Code Children Group:'$parent_group_name>>$groupName'", date("Y-m-d H:i:s", time()));
                    return $result;
                } else {
                    return FALSE;
                }
            }
        } else
            return FALSE;
    }

    /**
     * Delete Group
     * @param $groupID int GroupID
     * @return bool
     */
    public function deleteGroup(&$groupID)
    {
        $projectDao = new ProjectDao;
        $statusCodeGroupDao = new StatusCodeGroupDao;
        if ($projectID = $statusCodeGroupDao->checkStatusCodeGroupPermission($groupID, $_SESSION['userID'])) {
            $group_name = $statusCodeGroupDao->getGroupName($groupID);
            $result = $statusCodeGroupDao->deleteGroup($groupID);
            if ($result) {
                $projectDao->updateProjectUpdateTime($projectID);
                
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE_GROUP, $groupID, ProjectLogDao::$OP_TYPE_DELETE, "Delete Status Code Group:'$group_name'", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * Get GROUP lIST
     * @param $projectID int ProjectID
     * @return bool|array
     */
    public function getGroupList(&$projectID)
    {
        $projectDao = new ProjectDao;
        $statusCodeGroupDao = new StatusCodeGroupDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return $statusCodeGroupDao->getGroupList($projectID);
        } else
            return FALSE;
    }

    /**
     * Edit Group
     * @param $groupID int
     * @param $groupName string 
     * @param $parentGroupID int 
     * @param $isChild
     * @return bool
     */
    public function editGroup(&$groupID, &$groupName, &$parentGroupID, &$isChild)
    {
        $projectDao = new ProjectDao;
        $statusCodeGroupDao = new StatusCodeGroupDao;
        if ($projectID = $statusCodeGroupDao->checkStatusCodeGroupPermission($groupID, $_SESSION['userID'])) {
            if ($parentGroupID && !$statusCodeGroupDao->checkStatusCodeGroupPermission($parentGroupID, $_SESSION['userID'])) {
                return FALSE;
            }
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $statusCodeGroupDao->editGroup($groupID, $groupName, $parentGroupID, $isChild);
            if ($result) {
                
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE_GROUP, $groupID, ProjectLogDao::$OP_TYPE_UPDATE, "Edit Status Code Group:'$groupName'", date("Y-m-d H:i:s", time()));

                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * Edit Sort Order
     * @param $projectID int 
     * @param $orderList string
     * @return bool
     */
    public function sortGroup(&$projectID, &$orderList)
    {
        $groupDao = new StatusCodeGroupDao();
        $projectDao = new ProjectDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            if ($groupDao->sortGroup($projectID, $orderList)) {
                $projectDao->updateProjectUpdateTime($projectID);
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE_GROUP, $projectID, ProjectLogDao::$OP_TYPE_UPDATE, "Edit Status Code Sort Order", date("Y-m-d H:i:s", time()));

                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    /**
     * Export Group
     * @param $group_id
     * @return bool|string
     */
    public function exportGroup(&$group_id)
    {
        $group_dao = new StatusCodeGroupDao();
        if (!($projectID = $group_dao->checkStatusCodeGroupPermission($group_id, $_SESSION['userID']))) {
            return FALSE;
        }
        $data = $group_dao->getGroupData($projectID, $group_id);
        if ($data) {
            $fileName = 'eoLinker_status_code_group_export_' . $_SESSION['userName'] . '_' . time() . '.export';
            if (file_put_contents(realpath('./dump') . DIRECTORY_SEPARATOR . $fileName, json_encode($data))) {
                $group_name = $group_dao->getGroupName($group_id);
               
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE_GROUP, $group_id, ProjectLogDao::$OP_TYPE_OTHERS, "Export Status Code Group：$group_name", date("Y-m-d H:i:s", time()));
                return $fileName;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * Import Group
     * @param $project_id
     * @param $data
     * @return bool
     */
    public function importGroup(&$project_id, &$data)
    {
        $group_dao = new StatusCodeGroupDao();
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $_SESSION['userID'])) {
            return FALSE;
        }
        $result = $group_dao->importGroup($project_id, $data);
        if ($result) {
            
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE_GROUP, $project_id, ProjectLogDao::$OP_TYPE_OTHERS, "Import Status Code Group：{$data['groupName']}", date("Y-m-d H:i:s", time()));
            return $result;
        } else {
            return FALSE;
        }
    }
}

?>
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

class StatusCodeModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * Get User Type
     * @param $codeID
     * @return bool|int
     */
    public function getUserType(&$codeID)
    {
        $statusCodeDao = new StatusCodeDao();
        $projectID = $statusCodeDao->checkStatusCodePermission($codeID, $_SESSION['userID']);
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
     * Add Code
     * @param $groupID int
     * @param $codeDesc string 
     * @param $code string 
     * @return bool|int
     */
    public function addCode(&$projectID,&$groupID, &$status_code_list)
    {
        $projectDao = new ProjectDao;
        $statusCodeGroupDao = new StatusCodeGroupDao;
        $statusCodeDao = new StatusCodeDao;
        if ($projectID = $statusCodeGroupDao->checkStatusCodeGroupPermission($groupID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $statusCodeDao->addCode($projectID,$groupID, $status_code_list);
            if ($result) {         	
            		$statu_code = '';
            		foreach ($result as $k)
            		{
            			if($k['code'] && $k['codeDesc'])
            			{
            				$statu_code .= $k['code'].',';
            			}
            		}
            	$statu_code = rtrim($statu_code, ',');
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE, $result, ProjectLogDao::$OP_TYPE_ADD, "Add Status Code:'{$code}'", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * Delete Status Code
     * @param $codeID int
     * @return bool
     */
    public function deleteCode(&$codeID)
    {
        $projectDao = new ProjectDao;
        $statusCodeDao = new StatusCodeDao;
        if ($projectID = $statusCodeDao->checkStatusCodePermission($codeID, $_SESSION['userID'])) {
            $status_codes = $statusCodeDao->getStatusCodes($code_ids);
            $result = $statusCodeDao->deleteCode($codeID);
            if ($result) {
                $projectDao->updateProjectUpdateTime($projectID);
                
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE, $code_ids, ProjectLogDao::$OP_TYPE_DELETE, "Delete Status Code:'{$status_codes}'", date("Y-m-d H:i:s", time()));

                return TRUE;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * Batch Delete Status code
     * @param $code_ids string 
     * @return bool
     */
    public function deleteCodes(&$code_ids)
    {
        $status_code_dao = new StatusCodeDao;
        $arr = explode(',', $code_ids);
        for ($i = 0; $i < count($arr); $i++) {
            if (!($projectID = $status_code_dao->checkStatusCodePermission($arr[$i], $_SESSION['userID'])))
                return FALSE;
        }
        $projectDao = new ProjectDao;
        $status_codes = $status_code_dao->getStatusCodes($code_ids);
        if ($status_code_dao->deleteCodes($code_ids)) {
            $projectDao->updateProjectUpdateTime($projectID);
            
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE, $code_ids, ProjectLogDao::$OP_TYPE_DELETE, "Delete Status Code:'{$status_codes}'", date("Y-m-d H:i:s", time()));

            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Get code lIST
     * @param $groupID int
     * @return array|bool
     */
    public function getCodeList(&$groupID)
    {
        $statusCodeGroupDao = new StatusCodeGroupDao;
        $statusCodeDao = new StatusCodeDao;
        if ($statusCodeGroupDao->checkStatusCodeGroupPermission($groupID, $_SESSION['userID'])) {
            return $statusCodeDao->getCodeList($groupID);
        } else
            return FALSE;
    }

    /**
     * gET ALL CODE LIST
     * @param $projectID int
     * @return array|bool
     */
    public function getAllCodeList(&$projectID)
    {
        $projectDao = new ProjectDao;
        $statusCodeDao = new StatusCodeDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return $statusCodeDao->getAllCodeList($projectID);
        } else
            return FALSE;
    }

    /**
     * Edit Status Code
     * @param $groupID int 
     * @param $codeID int 
     * @param $code string 
     * @param $codeDesc string 
     * @return bool
     */
    public function editCode(&$groupID, &$codeID, &$code, &$codeDesc)
    {
        $projectDao = new ProjectDao;
        $statusCodeDao = new StatusCodeDao;
        if ($projectID = $statusCodeDao->checkStatusCodePermission($codeID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $statusCodeDao->editCode($groupID, $codeID, $code, $codeDesc);
            if ($result) {
                
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE, $codeID, ProjectLogDao::$OP_TYPE_UPDATE, "Edit Status Code:'{$code}'", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * Search Status Code
     * @param $projectID int 
     * @param $tips string 
     * @return array|bool
     */
    public function searchStatusCode(&$projectID, &$tips)
    {
        $projectDao = new ProjectDao;
        $statusCodeDao = new StatusCodeDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return $statusCodeDao->searchStatusCode($projectID, $tips);
        } else
            return FALSE;
    }

    /**
     * Get Status Code Num
     * @param $projectID int
     * @return int|bool
     */
    public function getStatusCodeNum(&$projectID)
    {
        $projectDao = new ProjectDao;
        $statusCodeDao = new StatusCodeDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return $statusCodeDao->getStatusCodeNum($projectID);
        } else
            return FALSE;
    }

    /**
     * Batch add Status Code by Excel
     * @param $group_id
     * @param $code_list
     * @return bool
     */
    public function addStatusCodeByExcel(&$group_id, &$code_list)
    {
        $statusCodeGroupDao = new StatusCodeGroupDao;
        $statusCodeDao = new StatusCodeDao;
        if ($projectID = $statusCodeGroupDao->checkStatusCodeGroupPermission($group_id, $_SESSION['userID'])) {
            $result = $statusCodeDao->addStatusCodeByExcel($group_id, $code_list);
            if ($result) {
                
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE, $group_id, ProjectLogDao::$OP_TYPE_ADD, "Add Status Code by Excel", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }
}

?>
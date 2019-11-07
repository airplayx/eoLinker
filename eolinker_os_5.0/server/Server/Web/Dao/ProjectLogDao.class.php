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

class ProjectLogDao
{

    public static $OP_TYPE_ADD = 0;
    public static $OP_TYPE_UPDATE = 1;
    public static $OP_TYPE_DELETE = 2;
    public static $OP_TYPE_OTHERS = 3;
    public static $OP_TARGET_PROJECT = 0;
    public static $OP_TARGET_API = 1;
    public static $OP_TARGET_API_GROUP = 2;
    public static $OP_TARGET_STATUS_CODE = 3;
    public static $OP_TARGET_STATUS_CODE_GROUP = 4;
    public static $OP_TARGET_ENVIRONMENT = 5;
    public static $OP_TARGET_PARTNER = 6;
    public static $OP_TARGET_PROJECT_DOCUMENT_GROUP = 7;
    public static $OP_TARGET_PROJECT_DOCUMENT = 8;
    public static $OP_TARGET_AUTOMATED_TEST_CASE_GROUP = 9;
    public static $OP_TARGET_AUTOMATED_TEST_CASE = 10;

    /**
     * record operation log
     * @param $project_id
     * @param $user_id
     * @param $op_target
     * @param $op_targetID
     * @param $op_type
     * @param $op_desc
     * @param $op_time
     * @return bool
     */
    public function addOperationLog(&$project_id, &$user_id, $op_target, &$op_targetID, $op_type, $op_desc, $op_time)
    {
        $db = getDatabase();
        $db->prepareExecute('INSERT INTO eo_log_project_operation (eo_log_project_operation.opType,eo_log_project_operation.opUserID,eo_log_project_operation.opDesc,eo_log_project_operation.opTime,eo_log_project_operation.opProjectID,eo_log_project_operation.opTarget,eo_log_project_operation.opTargetID) VALUES (?,?,?,?,?,?,?);', array(
            $op_type,
            $user_id,
            $op_desc,
            $op_time,
            $project_id,
            $op_target,
            $op_targetID
        ));

        if ($db->getAffectRow() > 0)
            return $db->getLastInsertID();
        else
            return FALSE;
    }

    /**
     * get operation log
     * @param $project_id
     * @param $page
     * @param $page_size
     * @param $dayOffset
     * @return array|bool
     */
    public function getOperationLogList(&$project_id, &$page, &$page_size, $dayOffset)
    {
        $db = getDatabase();
        $result = array();
        $result['logList'] = $db->prepareExecuteAll('SELECT eo_log_project_operation.opTime,eo_log_project_operation.opType,IFNULL( eo_ams_conn_project.partnerNickName,eo_user.userNickName) as operator,eo_log_project_operation.opTarget,eo_log_project_operation.opDesc
			FROM eo_log_project_operation LEFT JOIN eo_ams_conn_project ON eo_log_project_operation.opUserID = eo_ams_conn_project.userID AND eo_log_project_operation.opProjectID = eo_ams_conn_project.projectID
			INNER JOIN eo_user ON eo_log_project_operation.opUserID = eo_user.userID
			WHERE eo_log_project_operation.opProjectID = ? AND eo_log_project_operation.opTime > DATE_SUB(NOW(),INTERVAL ? DAY) ORDER BY eo_log_project_operation.opTime DESC LIMIT ?,?;', array(
            $project_id,
            $dayOffset,
            ($page - 1) * $page_size,
            $page_size
        ));

        $log_count = $db->prepareExecute('SELECT COUNT(eo_log_project_operation.opID) AS logCount FROM eo_log_project_operation WHERE eo_log_project_operation.opProjectID = ? AND eo_log_project_operation.opTime > DATE_SUB(NOW(),INTERVAL ? DAY)', array(
            $project_id,
            $dayOffset
        ));

        $result = array_merge($result, $log_count);

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get 24/7 operation log
     * @param $project_id
     * @return array|bool
     */
    public function getLogInADay(&$project_id)
    {
        $db = getDatabase();
        $result = array();
        $result['logList'] = $db->prepareExecuteAll('SELECT eo_log_project_operation.opTime,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_log_project_operation.opDesc FROM eo_log_project_operation LEFT JOIN eo_ams_conn_project ON eo_log_project_operation.opUserID = eo_ams_conn_project.userID AND eo_log_project_operation.opProjectID = eo_ams_conn_project.projectID INNER JOIN eo_user ON eo_log_project_operation.opUserID = eo_user.userID WHERE eo_log_project_operation.opProjectID = ? AND eo_log_project_operation.opTime > DATE_SUB(NOW(),INTERVAL 1 DAY) ORDER BY eo_log_project_operation.opTime DESC LIMIT 0,10;', array(
            $project_id
        ));

        $log_count = $db->prepareExecute('SELECT COUNT(eo_log_project_operation.opID) AS logCount FROM eo_log_project_operation WHERE eo_log_project_operation.opProjectID = ? AND eo_log_project_operation.opTime > DATE_SUB(NOW(),INTERVAL 1 DAY) ', array(
            $project_id
        ));

        $result = array_merge($result, $log_count);

        if (empty($result))
            return FALSE;
        else
            return $result;
    }
}

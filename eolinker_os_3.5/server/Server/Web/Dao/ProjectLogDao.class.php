<?php

/**
 * @name eolinker ams open source，eolinker开源版本
 * @link https://www.eolinker.com/
 * @package eolinker
 * @author www.eolinker.com 广州银云信息科技有限公司 2015-2017
 * eoLinker是目前全球领先、国内最大的在线API接口管理平台，提供自动生成API文档、API自动化测试、Mock测试、团队协作等功能，旨在解决由于前后端分离导致的开发效率低下问题。
 * 如在使用的过程中有任何问题，欢迎加入用户讨论群进行反馈，我们将会以最快的速度，最好的服务态度为您解决问题。
 *
 * eoLinker AMS开源版的开源协议遵循Apache License 2.0，如需获取最新的eolinker开源版以及相关资讯，请访问:https://www.eolinker.com/#/os/download
 *
 * 官方网站：https://www.eolinker.com/
 * 官方博客以及社区：http://blog.eolinker.com/
 * 使用教程以及帮助：http://help.eolinker.com/
 * 商务合作邮箱：market@eolinker.com
 * 用户讨论QQ群：284421832
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
     * 记录操作日志
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
     * 获取操作日志
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
        $result['logList'] = $db->prepareExecuteAll('SELECT eo_log_project_operation.opTime,eo_log_project_operation.opType,eo_conn_project.partnerNickName,eo_user.userNickName,eo_log_project_operation.opTarget,eo_log_project_operation.opDesc
			FROM eo_log_project_operation LEFT JOIN eo_conn_project ON eo_log_project_operation.opUserID = eo_conn_project.userID AND eo_log_project_operation.opProjectID = eo_conn_project.projectID
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
     * 获取24小时之内操作日志以及数量
     * @param $project_id
     * @return array|bool
     */
    public function getLogInADay(&$project_id)
    {
        $db = getDatabase();
        $result = array();
        $result['logList'] = $db->prepareExecuteAll('SELECT eo_log_project_operation.opTime,eo_conn_project.partnerNickName,eo_user.userNickName,eo_log_project_operation.opDesc FROM eo_log_project_operation LEFT JOIN eo_conn_project ON eo_log_project_operation.opUserID = eo_conn_project.userID AND eo_log_project_operation.opProjectID = eo_conn_project.projectID INNER JOIN eo_user ON eo_log_project_operation.opUserID = eo_user.userID WHERE eo_log_project_operation.opProjectID = ? AND eo_log_project_operation.opTime > DATE_SUB(NOW(),INTERVAL 1 DAY) ORDER BY eo_log_project_operation.opTime DESC LIMIT 0,10;', array(
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

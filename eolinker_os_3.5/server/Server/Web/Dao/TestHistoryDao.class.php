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
class TestHistoryDao
{
    /**
     * 添加测试记录
     * @param $projectID int 项目ID
     * @param $apiID int 接口ID
     * @param $requestInfo string 测试请求信息
     * @param $resultInfo string 测试结果信息
     * @param $testTime string 测试时间
     * @return bool|int
     */
    public function addTestHistory(&$projectID, &$apiID, &$requestInfo, &$resultInfo, &$testTime)
    {
        $db = getDatabase();
        $db->prepareExecute('INSERT INTO eo_api_test_history (eo_api_test_history.projectID,eo_api_test_history.apiID,eo_api_test_history.requestInfo,eo_api_test_history.resultInfo,eo_api_test_history.testTime) VALUES (?,?,?,?,?);', array(
            $projectID,
            $apiID,
            $requestInfo,
            $resultInfo,
            $testTime
        ));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else {
            return $db->getLastInsertID();
        }
    }

    /**
     * 删除测试记录
     * @param $testID int 测试记录ID
     * @return bool
     */
    public function deleteTestHistory(&$testID)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_api_test_history WHERE eo_api_test_history.testID =?;', array($testID));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 获取测试记录信息
     * @param $testID int 测试记录ID
     * @return bool|array
     */
    public function getTestHistory(&$testID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_api_test_history.projectID,eo_api_test_history.apiID,eo_api_test_history.testID,eo_api_test_history.requestInfo,eo_api_test_history.resultInfo,eo_api_test_history.testTime FROM eo_api_test_history WHERE testID =?;', array($testID));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 检查测试记录与用户的联系
     * @param $testID int 测试记录ID
     * @param $userID int 用户ID
     * @return bool|int
     */
    public function checkTestHistoryPermission(&$testID, &$userID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_conn_project.projectID FROM eo_api_test_history INNER JOIN eo_api INNER JOIN eo_conn_project ON eo_api.projectID = eo_conn_project.projectID AND eo_api.apiID = eo_api_test_history.apiID WHERE eo_api_test_history.testID = ? AND eo_conn_project.userID = ?;', array(
            $testID,
            $userID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }

    /**
     * 删除所有测试记录
     * @param $apiID int 接口ID
     * @return bool
     */
    public function deleteAllTestHistory(&$apiID)
    {
        $db = getDatabase();
        $db->prepareExecuteAll('DELETE FROM eo_api_test_history WHERE apiID = ?;', array($apiID));
        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
}

?>
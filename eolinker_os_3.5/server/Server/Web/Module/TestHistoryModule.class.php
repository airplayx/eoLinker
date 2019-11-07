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
class TestHistoryModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * 添加测试记录
     * @param $apiID int 接口ID
     * @param $requestInfo string 测试请求信息
     * @param $resultInfo string 测试结果信息
     * @param $testTime string 测试时间
     * @return bool|int
     */
    public function addTestHistory(&$apiID, &$requestInfo, &$resultInfo, &$testTime)
    {
        //判断返回结果是否为空
        if (empty($resultInfo)) {
            $resultInfo = '';
        }

        $projectDao = new ProjectDao;
        $apiDao = new ApiDao;
        $testHistoryDao = new TestHistoryDao;

        if ($projectID = $apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            return $testHistoryDao->addTestHistory($projectID, $apiID, $requestInfo, $resultInfo, $testTime);
        } else
            return FALSE;
    }

    /**
     * 删除测试记录
     * @param $testID int 测试记录ID
     * @return bool
     */
    public function deleteTestHistory(&$testID)
    {
        $testHistoryDao = new TestHistoryDao;
        $projectDao = new ProjectDao;
        if ($projectID = $testHistoryDao->checkTestHistoryPermission($testID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            return $testHistoryDao->deleteTestHistory($testID);
        } else
            return FALSE;
    }

    /**
     * 获取测试记录信息
     * @param $testID int 测试记录ID
     * @return bool|array
     */
    public function getTestHistory(&$testID)
    {
        $testHistoryDao = new TestHistoryDao;
        $projectDao = new ProjectDao;
        if ($projectID = $testHistoryDao->checkTestHistoryPermission($testID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            return $testHistoryDao->getTestHistory($testID);
        } else
            return FALSE;
    }

    /**
     * 删除所有测试记录
     * @param $apiID int 接口ID
     * @return bool
     */
    public function deleteAllTestHistory(&$apiID)
    {
        $apiDao = new ApiDao();
        if ($apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            $dao = new TestHistoryDao();
            return $dao->deleteAllTestHistory($apiID);
        } else {
            return FALSE;
        }
    }
}

?>
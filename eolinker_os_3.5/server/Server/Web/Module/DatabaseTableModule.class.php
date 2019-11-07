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
class DatabaseTableModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * 获取数据字典用户类型
     * @param $tableID int 表ID
     * @return bool|int
     */
    public function getUserType(&$tableID)
    {
        $tableDao = new DatabaseTableDao();
        $dbID = $tableDao->checkTablePermission($tableID, $_SESSION['userID']);
        if (empty($dbID)) {
            return -1;
        }
        $dao = new AuthorizationDao();
        $result = $dao->getDatabaseUserType($_SESSION['userID'], $dbID);
        if ($result === FALSE) {
            return -1;
        } else {
            return $result;
        }
    }

    /**
     * 添加数据表
     * @param $dbID int 数据库ID
     * @param $tableName string 数据表名
     * @param $tableDesc string 数据表描述，默认为NULL
     * @return bool|int
     */
    public function addTable(&$dbID, &$tableName, &$tableDesc)
    {
        $databaseDao = new DatabaseDao;
        $databaseTableDao = new DatabaseTableDao;
        if ($dbID = $databaseDao->checkDatabasePermission($dbID, $_SESSION['userID'])) {
            $databaseDao->updateDatabaseUpdateTime($dbID);
            return $databaseTableDao->addTable($dbID, $tableName, $tableDesc);
        } else
            return FALSE;
    }

    /**
     * 删除数据表
     * @param $tableID int 数据表ID
     * @return bool
     */
    public function deleteTable(&$tableID)
    {
        $databaseDao = new DatabaseDao;
        $databaseTableDao = new DatabaseTableDao;
        if ($dbID = $databaseTableDao->checkTablePermission($tableID, $_SESSION['userID'])) {
            $databaseDao->updateDatabaseUpdateTime($dbID);
            return $databaseTableDao->deleteTable($tableID);
        } else
            return FALSE;
    }

    /**
     * 获取数据表列表
     * @param $dbID int 数据库ID
     * @return bool|array
     */
    public function getTable(&$dbID)
    {
        $databaseDao = new DatabaseDao;
        $databaseTableDao = new DatabaseTableDao;
        if ($dbID = $databaseDao->checkDatabasePermission($dbID, $_SESSION['userID'])) {
            $databaseDao->updateDatabaseUpdateTime($dbID);
            return $databaseTableDao->getTable($dbID);
        } else
            return FALSE;
    }

    /**
     * 修改数据表
     * @param $tableID int 数据表ID
     * @param $tableName string 数据表名
     * @param $tableDesc string 数据表描述，默认为NULL
     * @return bool
     */
    public function editTable(&$tableID, &$tableName, &$tableDesc)
    {
        $databaseDao = new DatabaseDao;
        $databaseTableDao = new DatabaseTableDao;
        if ($dbID = $databaseTableDao->checkTablePermission($tableID, $_SESSION['userID'])) {
            $databaseDao->updateDatabaseUpdateTime($dbID);
            return $databaseTableDao->editTable($tableID, $tableName, $tableDesc);
        } else
            return FALSE;
    }

}

?>
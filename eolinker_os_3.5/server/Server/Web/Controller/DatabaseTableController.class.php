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
class DatabaseTableController
{
    //return json object
    //返回Json类型
    private $returnJson = array('type' => 'database_table');

    /**
     * checkout login status
     * 检查登录状态
     */
    public function __construct()
    {
        // identify authentication
        // 身份验证
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * add database table
     * 添加数据表
     */
    public function addTable()
    {
        $dbID = securelyInput('dbID');
        $module = new DatabaseModule();
        $userType = $module->getUserType($dbID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $nameLen = mb_strlen(quickInput('tableName'), 'utf8');
        $tableName = securelyInput('tableName');
        $descLen = mb_strlen(quickInput('tableDescription'), 'utf8');
        $tableDesc = securelyInput('tableDescription');
        $fieldDefaultValue = securelyInput('defaultValue');

        //illegal dbID
        //数据库ID格式非法
        if (!preg_match('/^[0-9]{1,11}$/', $dbID)) {
            $this->returnJson['statusCode'] = '230001';
        } elseif (!($nameLen >= 1 && $nameLen <= 255)) {
            //illegal tableName
            //表名长度非法
            $this->returnJson['statusCode'] = '230002';
        } elseif (!($descLen >= 0 && $descLen <= 255)) {
            //illegal tableDescription
            //表描述长度非法
            $this->returnJson['statusCode'] = '230003';
        } else {
            $service = new DatabaseTableModule;
            $result = $service->addTable($dbID, $tableName, $tableDesc);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['tableID'] = $result;
            } else {
                $this->returnJson['statusCode'] = '230004';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * delete database table
     * 删除数据表
     */
    public function deleteTable()
    {
        $tableID = securelyInput('tableID');
        $module = new DatabaseTableModule();
        $userType = $module->getUserType($tableID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        //illegal tableID
        //数据表ID格式非法
        if (!preg_match('/^[0-9]{1,11}$/', $tableID)) {
            $this->returnJson['statusCode'] = '230005';
        } else {
            $service = new DatabaseTableModule;
            $result = $service->deleteTable($tableID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '230006';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * get database table list
     * 获取数据表列表
     */
    public function getTable()
    {
        $dbID = securelyInput('dbID');
        //illegal dbID
        //数据库ID格式非法
        if (!preg_match('/^[0-9]{1,11}$/', $dbID)) {
            $this->returnJson['statusCode'] = '230001';
        } else {
            $service = new DatabaseTableModule;
            $result = $service->getTable($dbID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['tableList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '230007';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * edit database table
     * 修改数据表
     */
    public function editTable()
    {
        $tableID = securelyInput('tableID');
        $module = new DatabaseTableModule();
        $userType = $module->getUserType($tableID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $nameLen = mb_strlen(quickInput('tableName'), 'utf8');
        $tableName = securelyInput('tableName');
        $descLen = mb_strlen(quickInput('tableDescription'), 'utf8');
        $tableDesc = securelyInput('tableDescription');
        $fieldDefaultValue = securelyInput('defaultValue');

        //illegal tableID
        //数据表ID格式非法
        if (!preg_match('/^[0-9]{1,11}$/', $tableID)) {
            $this->returnJson['statusCode'] = '230005';
        } elseif (!($nameLen >= 1 && $nameLen <= 255)) {
            //illegal tableName
            //表名长度非法
            $this->returnJson['statusCode'] = '230002';
        } elseif (!($descLen >= 0 && $descLen <= 255)) {
            //illegal tableDescription
            //表描述长度非法
            $this->returnJson['statusCode'] = '230003';
        } else {
            $service = new DatabaseTableModule;
            $result = $service->editTable($tableID, $tableName, $tableDesc);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '230008';
            }
        }
        exitOutput($this->returnJson);
    }

}

?>
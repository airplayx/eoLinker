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
class DatabaseTableFieldController
{
    //返回Json类型
    private $returnJson = array('type' => 'database_table_field');

    /**
     * 检查登录状态
     */
    public function __construct()
    {
        // 身份验证
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * 添加字段
     */
    public function addField()
    {
        $tableID = securelyInput('tableID');
        $module = new DatabaseTableModule();
        $userType = $module->getUserType($tableID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $nameLen = mb_strlen(quickInput('fieldName', 'utf8'));
        $fieldName = securelyInput('fieldName');
        $typeLen = mb_strlen(quickInput('fieldType', 'utf8'));
        $fieldType = securelyInput('fieldType');
        $fieldLength = securelyInput('fieldLength');
        $isNotNull = securelyInput('isNotNull');
        $isPrimaryKey = securelyInput('isPrimaryKey');
        $descLen = mb_strlen(quickInput('fieldDescription', 'utf8'));
        $fieldDescription = securelyInput('fieldDescription');
        $fieldDefaultValue = securelyInput('defaultValue');

        if (!preg_match('/^[0-9]{1,11}$/', $tableID)) {
            $this->returnJson['statusCode'] = '240001';
        } elseif (!($nameLen >= 1 && $nameLen <= 255)) {
            // 字段名长度不合法
            $this->returnJson['statusCode'] = '240002';
        } elseif (!($typeLen >= 1 && $typeLen <= 255)) {
            $this->returnJson['statusCode'] = '240003';
        } elseif (!preg_match('/^[0-9]{1}$/', $isNotNull) || !preg_match('/^[0-9]{1}$/', $isPrimaryKey)) {
            $this->returnJson['statusCode'] = '240004';
        } elseif (!($descLen >= 0 && $descLen <= 255)) {
            // 字段描述长度不合法
            $this->returnJson['statusCode'] = '240005';
        } else {
            $service = new DatabaseTableFieldModule;
            $result = $service->addField($tableID, $fieldName, $fieldType, $fieldLength, $isNotNull, $isPrimaryKey, $fieldDescription, $fieldDefaultValue);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['fieldID'] = $result;
            } else {
                $this->returnJson['statusCode'] = '240006';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除字段
     */
    public function deleteField()
    {
        $fieldID = securelyInput('fieldID');
        $module = new DatabaseTableFieldModule();
        $userType = $module->getUserType($fieldID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        if (!preg_match('/^[0-9]{1,11}$/', $fieldID)) {
            $this->returnJson['statusCode'] = '240007';
        } else {
            $service = new DatabaseTableFieldModule;
            $result = $service->deleteField($fieldID);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '240008';
            }
        }
        exitOutput($this->returnJson);

    }

    /**
     * 获取字段列表
     */
    public function getField()
    {
        $tableID = securelyInput('tableID');

        if (!preg_match('/^[0-9]{1,11}$/', $tableID)) {
            $this->returnJson['statusCode'] = '240001';
        } else {
            $service = new DatabaseTableFieldModule;
            $result = $service->getField($tableID);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['fieldList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '240009';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改字段
     */
    public function editField()
    {
        $fieldID = securelyInput('fieldID');
        $module = new DatabaseTableFieldModule();
        $userType = $module->getUserType($fieldID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $nameLen = mb_strlen(quickInput('fieldName', 'utf8'));
        $fieldName = securelyInput('fieldName');
        $typeLen = mb_strlen(quickInput('fieldType', 'utf8'));
        $fieldType = securelyInput('fieldType');
        $fieldLength = securelyInput('fieldLength');
        $isNotNull = securelyInput('isNotNull');
        $isPrimaryKey = securelyInput('isPrimaryKey');
        $descLen = mb_strlen(quickInput('fieldDescription', 'utf8'));
        $fieldDescription = securelyInput('fieldDescription');
        $fieldDefaultValue = securelyInput('defaultValue');

        if (!preg_match('/^[0-9]{1,11}$/', $fieldID)) {
            $this->returnJson['statusCode'] = '240007';
        } elseif (!($nameLen >= 1 && $nameLen <= 255)) {
            // 字段名长度不合法
            $this->returnJson['statusCode'] = '240002';
        } elseif (!($typeLen >= 1 && $typeLen < 255)) {
            $this->returnJson['statusCode'] = '240003';
        } elseif (!preg_match('/^[0-9]{1}$/', $isNotNull) || !preg_match('/^[0-9]{1}$/', $isPrimaryKey)) {
            $this->returnJson['statusCode'] = '240004';
        } elseif (!($descLen >= 0 && $descLen <= 255)) {
            // 字段描述长度不合法
            $this->returnJson['statusCode'] = '240005';
        } else {
            $service = new DatabaseTableFieldModule;
            $result = $service->editField($fieldID, $fieldName, $fieldType, $fieldLength, $isNotNull, $isPrimaryKey, $fieldDescription, $fieldDefaultValue);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '240010';
            }
        }
        exitOutput($this->returnJson);

    }

}

?>
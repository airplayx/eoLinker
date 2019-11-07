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
class DatabaseTableFieldDao
{
    /**
     * 添加字段
     * @param $tableID int 数据表ID
     * @param $fieldName string 字段名
     * @param $fieldType string  字段类型
     * @param $fieldLength int 字段长度
     * @param $isNotNull int 是否非空 [0/1]=>[否/是]，默认为0
     * @param $isPrimaryKey int 是否为主键 [0/1]=>[否/是]，默认为0
     * @param $fieldDesc string 字段描述，默认为NULL
     * @return bool|int
     */
    public function addField(&$tableID, &$fieldName, &$fieldType, &$fieldLength, &$isNotNull, &$isPrimaryKey, &$fieldDesc, &$defaultValue)
    {
        $db = getDatabase();
        $db->prepareExecute('INSERT INTO eo_database_table_field (eo_database_table_field.tableID,eo_database_table_field.fieldName,eo_database_table_field.fieldType,eo_database_table_field.fieldLength,eo_database_table_field.isNotNull,eo_database_table_field.isPrimaryKey,eo_database_table_field.fieldDescription,eo_database_table_field.defaultValue) VALUES (?,?,?,?,?,?,?,?);', array(
            $tableID,
            $fieldName,
            $fieldType,
            $fieldLength,
            $isNotNull,
            $isPrimaryKey,
            $fieldDesc,
            $defaultValue
        ));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $db->getLastInsertID();
    }

    /**
     * 检查字段与用户是否匹配
     * @param $fieldID int 字段ID
     * @param $userID int 用户ID
     * @return bool|int
     */
    public function checkFieldPermission($fieldID, $userID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_database_table.dbID FROM eo_database_table INNER JOIN eo_database_table_field ON eo_database_table.tableID = eo_database_table_field.tableID INNER JOIN eo_conn_database ON eo_database_table.dbID = eo_conn_database.dbID WHERE eo_database_table_field.fieldID = ? AND eo_conn_database.userID =?;', array(
            $fieldID,
            $userID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['dbID'];
    }

    /**
     * 删除字段
     * @param $fieldID int 字段ID
     * @return bool
     */
    public function deleteField(&$fieldID)
    {
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_database_table_field WHERE eo_database_table_field.fieldID =?;', array($fieldID));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 获取字段列表
     * @param $tableID int 数据表ID
     * @return bool|array
     */
    public function getField(&$tableID)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT eo_database_table_field.tableID,eo_database_table_field.fieldID,eo_database_table_field.fieldName,eo_database_table_field.fieldType,eo_database_table_field.fieldLength,eo_database_table_field.isNotNull,eo_database_table_field.isPrimaryKey,eo_database_table_field.fieldDescription,eo_database_table_field.defaultValue FROM eo_database_table_field WHERE eo_database_table_field.tableID = ?;', array($tableID));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 修改字段
     * @param $fieldID int 字段ID
     * @param $fieldName string 字段名
     * @param $fieldType string 字段类型
     * @param $fieldLength int 字段长度
     * @param $isNotNull int 是否非空 [0/1]=>[否/是]，默认为0
     * @param $isPrimaryKey int 是否为主键 [0/1]=>[否/是]，默认为0
     * @param $fieldDesc string 字段描述，默认为NULL
     * @param $defaultValue string 默认值
     * @return bool
     */
    public function editField(&$fieldID, &$fieldName, &$fieldType, &$fieldLength, &$isNotNull, &$isPrimaryKey, &$fieldDesc, $defaultValue)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_database_table_field SET eo_database_table_field.fieldName =?,eo_database_table_field.fieldType=?,eo_database_table_field.fieldLength =?,eo_database_table_field.isNotNull =?,eo_database_table_field.isPrimaryKey =?,eo_database_table_field.fieldDescription =?,eo_database_table_field.defaultValue =? WHERE eo_database_table_field.fieldID =?;', array(
            $fieldName,
            $fieldType,
            $fieldLength,
            $isNotNull,
            $isPrimaryKey,
            $fieldDesc,
            $defaultValue,
            $fieldID
        ));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

}

?>
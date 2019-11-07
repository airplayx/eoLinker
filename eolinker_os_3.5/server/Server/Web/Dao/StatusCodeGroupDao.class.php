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
class StatusCodeGroupDao
{
    /**
     * 添加状态码分组
     * @param $projectID int 项目ID
     * @param $groupName string 分组名
     * @return int|bool
     */
    public function addGroup(&$projectID, &$groupName)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_project_status_code_group (eo_project_status_code_group.projectID,eo_project_status_code_group.groupName) VALUES (?,?);', array(
            $projectID,
            $groupName
        ));

        $groupID = $db->getLastInsertID();

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $groupID;

    }

    /**
     * 添加子分组
     * @param $projectID int 项目ID
     * @param $groupName string 分组名
     * @param $parentGroupID int 父分组ID
     * @return bool|int
     */
    public function addChildGroup(&$projectID, &$groupName, &$parentGroupID)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_project_status_code_group (eo_project_status_code_group.projectID,eo_project_status_code_group.groupName,eo_project_status_code_group.parentGroupID,eo_project_status_code_group.isChild) VALUES (?,?,?,1);', array(
            $projectID,
            $groupName,
            $parentGroupID
        ));

        $groupID = $db->getLastInsertID();

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $groupID;
    }

    /**
     * 判断用户和分组是否匹配
     * @param $groupID int 分组ID
     * @param $userID int 用户ID
     * @return bool|int
     */
    public function checkStatusCodeGroupPermission(&$groupID, &$userID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_conn_project.projectID FROM eo_conn_project INNER JOIN eo_project_status_code_group ON eo_conn_project.projectID = eo_project_status_code_group.projectID WHERE groupID = ? AND userID = ?;', array(
            $groupID,
            $userID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }

    /**
     * 删除分组
     * @param $groupID int 分组ID
     * @return bool
     */
    public function deleteGroup(&$groupID)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_project_status_code_group WHERE eo_project_status_code_group.groupID = ?;', array($groupID));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 获取分组列表
     * @param $projectID int 项目ID
     * @return bool|array
     */
    public function getGroupList(&$projectID)
    {
        $db = getDatabase();

        $groupList = $db->prepareExecuteAll('SELECT eo_project_status_code_group.groupID,eo_project_status_code_group.groupName FROM eo_project_status_code_group WHERE projectID = ? AND isChild = 0 ORDER BY eo_project_status_code_group.groupID DESC;', array($projectID));

        if (is_array($groupList))
            foreach ($groupList as &$parentGroup) {
                $parentGroup['childGroupList'] = array();
                $childGroup = $db->prepareExecuteAll('SELECT eo_project_status_code_group.groupID,eo_project_status_code_group.groupName,eo_project_status_code_group.parentGroupID FROM eo_project_status_code_group WHERE projectID = ? AND isChild = 1 AND parentGroupID = ? ORDER BY eo_project_status_code_group.groupID DESC;', array(
                    $projectID,
                    $parentGroup['groupID']
                ));

                //判断是否有子分组
                if (!empty($childGroup))
                    $parentGroup['childGroupList'] = $childGroup;
            }

        $result = array();
        $result['groupList'] = $groupList;
        $groupOrder = $db->prepareExecute('SELECT eo_api_status_code_group_order.orderList FROM eo_api_status_code_group_order WHERE projectID = ?;', array(
            $projectID
        ));
        $result['groupOrder'] = $groupOrder['orderList'];

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 修改分组
     * @param $groupID int 分组ID
     * @param $groupName string 分组名
     * @param $parentGroupID int 父分组ID
     * @return bool
     */
    public function editGroup(&$groupID, &$groupName, $parentGroupID)
    {
        $db = getDatabase();

        if (!$parentGroupID) {
            $db->prepareExecute('UPDATE eo_project_status_code_group SET eo_project_status_code_group.groupName = ?,isChild = 0,parentGroupID = NULL WHERE eo_project_status_code_group.groupID = ?;', array(
                $groupName,
                $groupID
            ));
        } else {
            $db->prepareExecute('UPDATE eo_project_status_code_group SET eo_project_status_code_group.groupName = ?,isChild = 1,parentGroupID = ? WHERE eo_project_status_code_group.groupID = ?;', array(
                $groupName,
                $parentGroupID,
                $groupID
            ));
        }


        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 更新分组排序
     * @param $projectID int 项目ID
     * @param $orderList string 排序列表
     * @return bool
     */
    public function sortGroup(&$projectID, &$orderList)
    {
        $db = getDatabase();
        $db->prepareExecute('REPLACE INTO eo_api_status_code_group_order(projectID, orderList) VALUES (?,?);', array(
            $projectID,
            $orderList
        ));
        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取分组名称
     * @param $group_id
     * @return bool
     */
    public function getGroupName(&$group_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_project_status_code_group.groupName FROM eo_project_status_code_group WHERE eo_project_status_code_group.groupID = ?;', array($group_id));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['groupName'];
        }
    }

    /**
     * 获取分组数据
     * @param $project_id
     * @param $group_id
     * @return array|bool
     */
    public function getGroupData(&$project_id, &$group_id)
    {
        $db = getDatabase();
        $result = array();
        $group = $db->prepareExecute('SELECT eo_project_status_code_group.groupName,eo_project_status_code_group.isChild FROM eo_project_status_code_group WHERE eo_project_status_code_group.projectID = ? AND eo_project_status_code_group.groupID = ?;', array(
            $project_id,
            $group_id
        ));
        $result['statusCodeList'] = $db->prepareExecuteAll("SELECT eo_project_status_code.codeID,eo_project_status_code.code,eo_project_status_code.codeDescription FROM eo_project_status_code WHERE eo_project_status_code.groupID = ?", array(
            $group_id
        ));
        $result['groupName'] = $group['groupName'];
        if ($group['isChild'] == 0) {
            $child_group_list = $db->prepareExecuteAll('SELECT eo_project_status_code_group.groupID,eo_project_status_code_group.groupName FROM eo_project_status_code_group WHERE eo_project_status_code_group.parentGroupID = ? AND eo_project_status_code_group.projectID = ?', array(
                $group_id,
                $project_id
            ));
            if ($child_group_list) {
                $i = 0;
                foreach ($child_group_list as $group) {
                    $result['childGroupList'][$i]['groupName'] = $group['groupName'];
                    $result['childGroupList'][$i]['statusCodeList'] = $db->prepareExecuteAll("SELECT eo_project_status_code.codeID,eo_project_status_code.code,eo_project_status_code.codeDescription FROM eo_project_status_code WHERE eo_project_status_code.groupID = ?", array(
                        $group['groupID']
                    ));
                    $i++;
                }
            }
        }
        if ($result)
            return $result;
        else
            return FALSE;
    }

    /**
     * 导入状态码分组
     * @param $project_id
     * @param $data
     * @return bool
     */
    public function importGroup(&$project_id, &$data)
    {
        $db = getDatabase();
        try {
            $db->beginTransaction();
            // 插入分组
            $db->prepareExecute('INSERT INTO eo_project_status_code_group (eo_project_status_code_group.projectID,eo_project_status_code_group.groupName) VALUES (?,?);', array(
                $project_id,
                $data['groupName']
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("add statusCodeGroup error");
            $group_id = $db->getLastInsertID();
            if ($data['statusCodeList']) {
                // 插入状态码
                foreach ($data['statusCodeList'] as $status_code) {
                    $db->prepareExecute('INSERT INTO eo_project_status_code (eo_project_status_code.groupID,eo_project_status_code.code,eo_project_status_code.codeDescription) VALUES (?,?,?);', array(
                        $group_id,
                        $status_code['code'],
                        $status_code['codeDescription']
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("add statusCode error");
                }
            }
            if ($data['childGroupList']) {
                $group_id_parent = $group_id;
                foreach ($data['childGroupList'] as $child_group) {
                    // 插入分组
                    $db->prepareExecute('INSERT INTO eo_project_status_code_group (eo_project_status_code_group.projectID,eo_project_status_code_group.groupName,eo_project_status_code_group.parentGroupID,eo_project_status_code_group.isChild) VALUES (?,?,?,?);', array(
                        $project_id,
                        $child_group['groupName'],
                        $group_id_parent,
                        1
                    ));
                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("add statusCodeGroup error");
                    }

                    $group_id = $db->getLastInsertID();
                    if ($child_group['statusCodeList']) {
                        // 插入状态码
                        foreach ($child_group['statusCodeList'] as $status_code) {
                            $db->prepareExecute('INSERT INTO eo_project_status_code (eo_project_status_code.groupID,eo_project_status_code.code,eo_project_status_code.codeDescription) VALUES (?,?,?);', array(
                                $group_id,
                                $status_code['code'],
                                $status_code['codeDescription']
                            ));

                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("add statusCode error");
                            }
                        }
                    }
                }
            }
            $db->commit();
            return TRUE;
        } catch (\PDOException $e) {
            $db->rollback();
            return FALSE;
        }
    }
}

?>
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
class DocumentGroupDao
{
    /**
     * 添加文档分组
     * @param $project_id int 项目ID
     * @param $group_name string 分组名称
     * @return bool|int
     */
    public function addGroup(&$project_id, &$group_name)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_project_document_group (eo_project_document_group.groupName,eo_project_document_group.projectID) VALUES (?,?);', array(
            $group_name,
            $project_id,
        ));

        $group_id = $db->getLastInsertID();

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $group_id;
    }

    /**
     * 添加文档子分组
     * @param $project_id int 项目ID
     * @param $parent_group_id int 父分组ID
     * @param $group_name string 分组名称
     * @return bool|int
     */
    public function addChildGroup(&$project_id, &$group_name, &$parent_group_id)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_project_document_group (eo_project_document_group.groupName,eo_project_document_group.projectID,eo_project_document_group.parentGroupID,eo_project_document_group.isChild) VALUES (?,?,?,1);', array(
            $group_name,
            $project_id,
            $parent_group_id
        ));

        $group_id = $db->getLastInsertID();

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $group_id;
    }

    /**
     * 删除文档分组
     * @param $group_id int 分组ID
     * @return bool
     */
    public function deleteGroup(&$group_id)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_project_document_group WHERE eo_project_document_group.groupID = ?;', array($group_id));
        $result = $db->getAffectRow();
        $db->prepareExecute('DELETE FROM eo_project_document_group WHERE eo_project_document_group.parentGroupID = ?;', array($group_id));
        $db->prepareExecute('DELETE FROM eo_project_document WHERE eo_project_document.groupID = ?;', array($group_id));

        if ($result > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取文档分组列表
     * @param $project_id int 项目ID
     * @return bool|array
     */
    public function getGroupList(&$project_id)
    {
        $db = getDatabase();
        $result = array();
        $group_list = $db->prepareExecuteAll('SELECT eo_project_document_group.groupID,eo_project_document_group.groupName FROM eo_project_document_group WHERE projectID = ? AND isChild = 0 ORDER BY groupID  ASC;', array($project_id));

        //检查是否含有子分组
        if (is_array($group_list)) {
            foreach ($group_list as &$parentGroup) {
                $parentGroup['childGroupList'] = array();
                $childGroup = $db->prepareExecuteAll('SELECT eo_project_document_group.groupID,eo_project_document_group.groupName,eo_project_document_group.parentGroupID FROM eo_project_document_group WHERE projectID = ? AND isChild = 1 AND parentGroupID = ? ORDER BY groupID ASC;', array(
                    $project_id,
                    $parentGroup['groupID']
                ));

                //判断是否有子分组
                if (!empty($childGroup))
                    $parentGroup['childGroupList'] = $childGroup;
            }
        }
        if (empty($group_list)) {
            return FALSE;
        }
        $result['groupList'] = $group_list;
        $order_list = $db->prepareExecute('SELECT eo_project_document_group_order.orderList FROM eo_project_document_group_order WHERE eo_project_document_group_order.projectID = ?;', array(
            $project_id
        ));
        $result['groupOrder'] = $order_list['orderList'];
        return $result;
    }

    /**
     * 修改文档分组信息
     * @param $group_id int 分组ID
     * @param $group_name string 分组名称
     * @param $parent_group_id int 父分组ID
     * @return bool
     */
    public function editGroup(&$group_id, &$group_name, &$parent_group_id)
    {
        $db = getDatabase();

        //如果没有父分组
        if ($parent_group_id <= 0) {
            $db->prepareExecute('UPDATE eo_project_document_group SET eo_project_document_group.groupName = ?,eo_project_document_group.parentGroupID = 0,eo_project_document_group.isChild = 0 WHERE eo_project_document_group.groupID = ?;', array(
                $group_name,
                $group_id
            ));
        } else {
            //有父分组
            $db->prepareExecute('UPDATE eo_project_document_group SET eo_project_document_group.groupName = ?,eo_project_document_group.parentGroupID = ?,eo_project_document_group.isChild = 1 WHERE eo_project_document_group.groupID = ?;', array(
                $group_name,
                $parent_group_id,
                $group_id
            ));
        }

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 判断文档分组和用户是否匹配
     * @param $group_id int 分组ID
     * @param $user_id int 用户ID
     * @return bool|int
     */
    public function checkGroupPermission(&$group_id, &$user_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_conn_project.projectID FROM eo_conn_project INNER JOIN eo_project_document_group ON eo_project_document_group.projectID = eo_conn_project.projectID WHERE userID = ? AND groupID = ?;', array(
            $user_id,
            $group_id
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }

    /**
     * 获取文档分组名称
     * @param $group_id int 分组ID
     * @return bool|string
     */
    public function getGroupName($group_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT eo_project_document_group.groupName FROM eo_project_document_group WHERE eo_project_document_group.groupID = ?;", array($group_id));

        if (empty($result))
            return FALSE;
        else
            return $result['groupName'];
    }

    /**
     * 更新文档分组排序
     * @param $project_id
     * @param $order_list
     * @return bool
     */
    public function updateGroupOrder(&$project_id, &$order_list)
    {
        $db = getDatabase();
        $db->prepareExecute("REPLACE INTO eo_project_document_group_order(projectID,orderList)VALUES(?,?);", array($project_id, $order_list));
        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
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
        $group = $db->prepareExecute('SELECT eo_project_document_group.groupName,eo_project_document_group.isChild FROM eo_project_document_group WHERE eo_project_document_group.projectID = ? AND eo_project_document_group.groupID = ?;', array(
            $project_id,
            $group_id
        ));
        $result['pageList'] = $db->prepareExecuteAll("SELECT eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title FROM eo_project_document WHERE eo_project_document.groupID = ? AND eo_project_document.projectID = ?", array(
            $group_id,
            $project_id
        ));
        $result['groupName'] = $group['groupName'];
        if ($group['isChild'] == 0) {
            $child_group_list = $db->prepareExecuteAll('SELECT eo_project_document_group.groupID,eo_project_document_group.groupName FROM eo_project_document_group WHERE eo_project_document_group.parentGroupID = ? AND eo_project_document_group.projectID = ?', array(
                $group_id,
                $project_id
            ));
            if ($child_group_list) {
                $i = 0;
                foreach ($child_group_list as $group) {
                    $result['childGroupList'][$i]['groupName'] = $group['groupName'];
                    $result['childGroupList'][$i]['pageList'] = $db->prepareExecuteAll("SELECT eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title FROM eo_project_document WHERE eo_project_document.groupID = ? AND eo_project_document.projectID = ?", array(
                        $group['groupID'],
                        $project_id
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
     * 导入文档分组
     * @param $project_id
     * @param $user_id
     * @param $data
     * @return bool
     */
    public function importGroup(&$project_id, &$user_id, &$data)
    {
        $db = getDatabase();
        try {
            $db->beginTransaction();
            // 插入分组
            $db->prepareExecute('INSERT INTO eo_project_document_group (eo_project_document_group.projectID,eo_project_document_group.groupName) VALUES (?,?);', array(
                $project_id,
                $data['groupName']
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("addPageGroup error");
            $group_id = $db->getLastInsertID();
            if ($data['pageList']) {
                // 插入状态码
                foreach ($data['pageList'] as $page) {
                    $db->prepareExecute('INSERT INTO eo_project_document (eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.updateTime,eo_project_document.userID) VALUES (?,?,?,?,?,?,?,?);', array(
                        $group_id,
                        $project_id,
                        $page['contentType'],
                        $page['contentRaw'],
                        $page['content'],
                        $page['title'],
                        date('Y-m-d H:i:s'),
                        $user_id
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addPage error");
                }
            }
            if ($data['childGroupList']) {
                $group_id_parent = $group_id;
                foreach ($data['childGroupList'] as $child_group) {
                    // 插入分组
                    $db->prepareExecute('INSERT INTO eo_project_document_group (eo_project_document_group.projectID,eo_project_document_group.groupName,eo_project_document_group.parentGroupID,eo_project_document_group.isChild) VALUES (?,?,?,?);', array(
                        $project_id,
                        $child_group['groupName'],
                        $group_id_parent,
                        1
                    ));
                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("addPageGroup error");
                    }

                    $group_id = $db->getLastInsertID();
                    if ($child_group['pageList']) {
                        // 插入状态码
                        foreach ($child_group['pageList'] as $page) {
                            $db->prepareExecute('INSERT INTO eo_project_document (eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.updateTime,eo_project_document.userID) VALUES (?,?,?,?,?,?,?,?);', array(
                                $group_id,
                                $project_id,
                                $page['contentType'],
                                $page['contentRaw'],
                                $page['content'],
                                $page['title'],
                                date('Y-m-d H:i:s'),
                                $user_id
                            ));

                            if ($db->getAffectRow() < 1)

                                throw new \PDOException("addPage error");
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
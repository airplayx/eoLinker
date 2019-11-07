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
class DocumentDao
{
    /**
     * 添加文档
     * @param $group_id
     * @param $project_id
     * @param $content_type
     * @param $content
     * @param $content_raw
     * @param $title
     * @param $user_id
     * @return bool
     */
    public function addDocument(&$group_id, &$project_id, &$content_type, &$content, &$content_raw, &$title, &$user_id)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_project_document (eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.userID) VALUES (?,?,?,?,?,?,?);', array(
            $group_id,
            $project_id,
            $content_type,
            $content_raw,
            $content,
            $title,
            $user_id
        ));

        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return $db->getLastInsertID();
        }
    }

    /**
     * 删除文档
     * @param $document_id
     * @return bool
     */
    public function deleteDocument(&$document_id)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_project_document WHERE eo_project_document.documentID = ?;', array(
            $document_id
        ));

        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 根据分组获取文档列表
     * @param $group_id
     * @return bool
     */
    public function getDocumentList(&$group_id)
    {
        $db = getDatabase();

        $result = $db->prepareExecuteAll("SELECT eo_project_document.groupID,eo_project_document.projectID,eo_project_document.documentID,eo_project_document_group.groupName,eo_project_document.updateTime,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.userID,eo_user.userNickName FROM eo_project_document LEFT JOIN eo_user ON eo_project_document.userID = eo_user.userID LEFT JOIN eo_project_document_group ON eo_project_document.groupID = eo_project_document_group.groupID WHERE eo_project_document.groupID = ? OR (eo_project_document_group.parentGroupID = ? AND eo_project_document_group.isChild = 1) ORDER BY eo_project_document.updateTime DESC;", array(
            $group_id,
            $group_id
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 获取所有文档列表
     * @param $project_id
     * @return bool
     */
    public function getAllDocumentList(&$project_id)
    {
        $db = getDatabase();

        $result = $db->prepareExecuteAll('SELECT eo_project_document.groupID,eo_project_document.projectID,eo_project_document.documentID,eo_project_document_group.groupName,eo_project_document.updateTime,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.userID,eo_user.userNickName FROM eo_project_document LEFT JOIN eo_user ON eo_project_document.userID = eo_user.userID LEFT JOIN eo_project_document_group ON eo_project_document_group.groupID = eo_project_document.groupID WHERE eo_project_document_group.projectID = ?;', array($project_id));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 修改文档
     * @param $document_id
     * @param $group_id
     * @param $content_type
     * @param $content
     * @param $content_raw
     * @param $title
     * @param $user_id
     * @return bool
     */
    public function editDocument(&$document_id, &$group_id, &$content_type, &$content, &$content_raw, &$title, &$user_id)
    {
        $db = getDatabase();

        $db->prepareExecute('UPDATE eo_project_document SET eo_project_document.groupID = ?,eo_project_document.contentType = ?,eo_project_document.contentRaw = ?,eo_project_document.content = ?,eo_project_document.title = ?,eo_project_document.userID = ? WHERE eo_project_document.documentID = ?;', array(
            $group_id,
            $content_type,
            $content_raw,
            $content,
            $title,
            $user_id,
            $document_id
        ));

        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 搜索文档
     * @param $project_id
     * @param $tips
     * @return bool
     */
    public function searchDocument(&$project_id, &$tips)
    {
        $db = getDatabase();

        $result = $db->prepareExecuteAll('SELECT eo_project_document.groupID,eo_project_document.projectID,eo_project_document.documentID,eo_project_document_group.groupName,eo_project_document.updateTime,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.userID,eo_user.userNickName FROM eo_project_document LEFT JOIN eo_user ON eo_project_document.userID = eo_user.userID LEFT JOIN eo_project_document_group ON eo_project_document_group.groupID = eo_project_document.groupID WHERE eo_project_document_group.projectID = ? AND eo_project_document.title LIKE ?;', array(
            $project_id,
            '%' . $tips . '%'
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 获取文档详情
     * @param $document_id
     * @return array|bool
     */
    public function getDocument(&$document_id)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.userID,eo_user.userNickName,eo_project_document.updateTime FROM eo_project_document LEFT JOIN eo_user ON eo_project_document.userID = eo_user.userID LEFT JOIN eo_project_document_group ON eo_project_document_group.groupID = eo_project_document.groupID WHERE eo_project_document.documentID = ?;', array($document_id));
        $parentGroupInfo = $db->prepareExecute('SELECT eo_project_document_group.parentGroupID,eo_project_document_group.groupName AS parentGroupName FROM eo_project_document_group WHERE eo_project_document_group.groupID = ? AND isChild = 1;', array($result['groupID']));
        if ($parentGroupInfo) {
            $result = array_merge($result, $parentGroupInfo);
        }

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 批量删除文档
     * @param $document_ids
     * @param $project_id
     * @return bool
     */
    public function deleteDocuments(&$document_ids, $project_id)
    {
        $db = getDatabase();
        $db->prepareExecute("DELETE FROM eo_project_document WHERE eo_project_document.documentID in ($document_ids) AND eo_project_document.projectID = ?;", array(
            $project_id
        ));

        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 检查文档权限
     * @param $document_id
     * @param $user_id
     * @return bool
     */
    public function checkDocumentPermission(&$document_id, &$user_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_conn_project.projectID FROM eo_project_document LEFT JOIN eo_conn_project ON eo_project_document.projectID = eo_conn_project.projectID WHERE eo_conn_project.userID = ? AND eo_project_document.documentID = ?;', array(
            $user_id,
            $document_id
        ));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['projectID'];
        }
    }

    /**
     * 获取文档标题
     * @param $document_ids
     * @return bool
     */
    public function getDocumentTitle(&$document_ids)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT GROUP_CONCAT(eo_project_document.title) AS title FROM eo_project_document WHERE eo_project_document.documentID IN ($document_ids);", array());
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['title'];
        }
    }
}
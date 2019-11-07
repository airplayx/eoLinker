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
class DocumentModule
{
    public function getUserType(&$document_id)
    {
        $dao = new DocumentDao();
        if (!($project_id = $dao->checkDocumentPermission($document_id, $_SESSION['userID']))) {
            return -1;
        }
        $auth_dao = new AuthorizationDao();
        $result = $auth_dao->getProjectUserType($_SESSION['userID'], $project_id);
        if ($result === FALSE) {
            return -1;
        } else {
            return $result;
        }
    }

    /**
     * 添加文档
     * @param $user_id int 用户ID
     * @param $group_id int 分组ID
     * @param $content_type int 内容编码类型[0/1]=>[富文本/markdown]
     * @param $content string 文档内容
     * @param $content_raw string 文档内容
     * @param $title string 文档标题
     * @return bool|int
     */
    public function addDocument(&$user_id, &$group_id, &$content_type, &$content, &$content_raw, &$title)
    {
        $document_group_dao = new DocumentGroupDao();
        $document_dao = new DocumentDao();
        if (!($project_id = $document_group_dao->checkGroupPermission($group_id, $user_id))) {
            return FALSE;
        }

        $document_id = $document_dao->addDocument($group_id, $project_id, $content_type, $content, $content_raw, $title, $user_id);
        if ($document_id) {
            $project_dao = new ProjectDao();
            $project_dao->updateProjectUpdateTime($project_id);
            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_PROJECT_DOCUMENT, $document_id, ProjectLogDao::$OP_TYPE_ADD, "添加项目文档:'{$title}'", date("Y-m-d H:i:s", time()));
            return $document_id;
        } else {
            return FALSE;
        }
    }

    /**
     * 删除文档
     * @param $user_id int 用户ID
     * @param $document_id int 文档ID
     * @return bool
     */
    public function deleteDocument(&$user_id, &$document_id)
    {
        $document_dao = new DocumentDao();
        if (!($project_id = $document_dao->checkDocumentPermission($document_id, $user_id))) {
            return FALSE;
        }
        $document_title = $document_dao->getDocumentTitle($document_id);
        if ($document_dao->deleteDocument($document_id)) {
            $project_dao = new ProjectDao();
            $project_dao->updateProjectUpdateTime($project_id);

            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_PROJECT_DOCUMENT, $document_id, ProjectLogDao::$OP_TYPE_DELETE, "删除项目文档：{$document_title}", date('Y-m-d H:i:s', time()));
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 获取文档列表
     * @param $group_id int 分组ID
     * @param $user_id int 用户ID
     * @return bool|array
     */
    public function getDocumentList(&$group_id, &$user_id)
    {
        $document_group_dao = new DocumentGroupDao();
        if (!$document_group_dao->checkGroupPermission($group_id, $user_id)) {
            return FALSE;
        }
        $document_dao = new DocumentDao();
        return $document_dao->getDocumentList($group_id);
    }

    /**
     * 获取所有文档列表
     * @param $project_id int 项目ID
     * @param $user_id int 用户ID
     * @return bool|array
     */
    public function getAllDocumentList(&$project_id, &$user_id)
    {
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $user_id)) {
            return FALSE;
        }
        $document_dao = new DocumentDao();
        return $document_dao->getAllDocumentList($project_id);
    }

    /**
     * 修改文档
     * @param $user_id
     * @param $group_id
     * @param $document_id
     * @param $content_type
     * @param $content
     * @param $content_raw
     * @param $title
     * @return bool
     */
    public function editDocument(&$user_id, &$group_id, &$document_id, &$content_type, &$content, &$content_raw, &$title)
    {
        $document_group_dao = new DocumentGroupDao();
        if (!($project_id = $document_group_dao->checkGroupPermission($group_id, $user_id))) {
            return FALSE;
        }
        $document_dao = new DocumentDao();
        if (!$document_dao->checkDocumentPermission($document_id, $user_id)) {
            return FALSE;
        }
        if ($document_dao->editDocument($document_id, $group_id, $content_type, $content, $content_raw, $title, $user_id)) {
            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_PROJECT_DOCUMENT, $document_id, ProjectLogDao::$OP_TYPE_UPDATE, "修改项目文档:'{$title}'", date("Y-m-d H:i:s", time()));
            $project_dao = new ProjectDao();
            $project_dao->updateProjectUpdateTime($project_id);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 搜索状态码
     * @param $project_id int 项目ID
     * @param $tips string 关键字
     * @param $user_id int 用户ID
     * @return bool|array
     */
    public function searchDocument(&$project_id, &$tips, &$user_id)
    {
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $user_id)) {
            return FALSE;
        }
        $document_dao = new DocumentDao();
        return $document_dao->searchDocument($project_id, $tips);
    }

    /**
     * 获取文档详情
     * @param $document_id int 文档ID
     * @param $user_id int 用户ID
     * @return bool|array
     */
    public function getDocument(&$document_id, &$user_id)
    {
        $document_dao = new DocumentDao();
        if (!$document_dao->checkDocumentPermission($document_id, $user_id)) {
            return FALSE;
        }
        return $document_dao->getDocument($document_id);
    }

    /**
     * 删除文档
     * @param $project_id int 项目ID
     * @param $user_id int 用户ID
     * @param $document_ids string 文档ID列表
     * @return bool
     */
    public function deleteDocuments(&$project_id, &$user_id, &$document_ids)
    {
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $user_id)) {
            return FALSE;
        }
        $document_dao = new DocumentDao();
        $document_title = $document_dao->getDocumentTitle($document_ids);
        if ($document_dao->deleteDocuments($document_ids, $project_id)) {
            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_PROJECT_DOCUMENT, $document_ids, ProjectLogDao::$OP_TYPE_DELETE, "删除项目文档:'{$document_title}'", date("Y-m-d H:i:s", time()));
            $project_dao = new ProjectDao();
            $project_dao->updateProjectUpdateTime($project_id);
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
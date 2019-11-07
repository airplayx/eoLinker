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
class StatusCodeGroupModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * 获取项目用户类型
     * @param $groupID int 分组ID
     * @return bool|int
     */
    public function getUserType(&$groupID)
    {
        $groupDao = new StatusCodeGroupDao();
        $projectID = $groupDao->checkStatusCodeGroupPermission($groupID, $_SESSION['userID']);
        if (empty($projectID)) {
            return -1;
        }
        $dao = new AuthorizationDao();
        $result = $dao->getProjectUserType($_SESSION['userID'], $projectID);
        if ($result === FALSE) {
            return -1;
        }
        return $result;
    }

    /**
     * 添加状态码分组
     * @param $projectID int 项目ID
     * @param $groupName string 分组名
     * @param $parentGroupID int 父分组ID
     * @return bool|int
     */
    public function addGroup(&$projectID, &$groupName, &$parentGroupID)
    {
        $projectDao = new ProjectDao;
        $statusCodeGroupDao = new StatusCodeGroupDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            if (is_null($parentGroupID)) {
                $result = $statusCodeGroupDao->addGroup($projectID, $groupName);
                if ($result) {
                    //将操作写入日志
                    $log_dao = new ProjectLogDao();
                    $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE_GROUP, $result, ProjectLogDao::$OP_TYPE_ADD, "新增状态码分组:'$groupName'", date("Y-m-d H:i:s", time()));
                    return $result;
                } else {
                    return FALSE;
                }
            } else {
                $result = $statusCodeGroupDao->addChildGroup($projectID, $groupName, $parentGroupID);
                if ($result) {
                    $parent_group_name = $statusCodeGroupDao->getGroupName($parentGroupID);
                    //将操作写入日志
                    $log_dao = new ProjectLogDao();
                    $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE_GROUP, $result, ProjectLogDao::$OP_TYPE_ADD, "新增状态码子分组:'$parent_group_name>>$groupName'", date("Y-m-d H:i:s", time()));
                    return $result;
                } else {
                    return FALSE;
                }
            }
        } else
            return FALSE;
    }

    /**
     * 删除分组
     * @param $groupID int 分组ID
     * @return bool
     */
    public function deleteGroup(&$groupID)
    {
        $projectDao = new ProjectDao;
        $statusCodeGroupDao = new StatusCodeGroupDao;
        if ($projectID = $statusCodeGroupDao->checkStatusCodeGroupPermission($groupID, $_SESSION['userID'])) {
            $group_name = $statusCodeGroupDao->getGroupName($groupID);
            $result = $statusCodeGroupDao->deleteGroup($groupID);
            if ($result) {
                $projectDao->updateProjectUpdateTime($projectID);
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE_GROUP, $groupID, ProjectLogDao::$OP_TYPE_DELETE, "删除状态码分组:'$group_name'", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * 获取状态码分组列表
     * @param $projectID int 项目ID
     * @return bool|array
     */
    public function getGroupList(&$projectID)
    {
        $projectDao = new ProjectDao;
        $statusCodeGroupDao = new StatusCodeGroupDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return $statusCodeGroupDao->getGroupList($projectID);
        } else
            return FALSE;
    }

    /**
     * 修改状态码分组
     * @param $groupID int 分组ID
     * @param $groupName string 分组名
     * @param $parentGroupID int 父分组ID
     * @return bool
     */
    public function editGroup(&$groupID, &$groupName, &$parentGroupID)
    {
        $projectDao = new ProjectDao;
        $statusCodeGroupDao = new StatusCodeGroupDao;
        if ($projectID = $statusCodeGroupDao->checkStatusCodeGroupPermission($groupID, $_SESSION['userID'])) {
            if ($parentGroupID && !$statusCodeGroupDao->checkStatusCodeGroupPermission($parentGroupID, $_SESSION['userID'])) {
                return FALSE;
            }
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $statusCodeGroupDao->editGroup($groupID, $groupName, $parentGroupID);
            if ($result) {
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE_GROUP, $groupID, ProjectLogDao::$OP_TYPE_UPDATE, "修改状态码分组:'$groupName'", date("Y-m-d H:i:s", time()));

                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * 修改分组排序
     * @param $projectID int 项目ID
     * @param $orderList string 排序列表
     * @return bool
     */
    public function sortGroup(&$projectID, &$orderList)
    {
        $groupDao = new StatusCodeGroupDao();
        $projectDao = new ProjectDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            if ($groupDao->sortGroup($projectID, $orderList)) {
                $projectDao->updateProjectUpdateTime($projectID);
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE_GROUP, $projectID, ProjectLogDao::$OP_TYPE_UPDATE, "修改状态码分组排序", date("Y-m-d H:i:s", time()));

                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    /**
     * 导出分组
     * @param $group_id
     * @return bool|string
     */
    public function exportGroup(&$group_id)
    {
        $group_dao = new StatusCodeGroupDao();
        if (!($projectID = $group_dao->checkStatusCodeGroupPermission($group_id, $_SESSION['userID']))) {
            return FALSE;
        }
        $data = $group_dao->getGroupData($projectID, $group_id);
        if ($data) {
            $fileName = 'eoLinker_status_code_group_export_' . $_SESSION['userName'] . '_' . time() . '.export';
            if (file_put_contents(realpath('./dump') . DIRECTORY_SEPARATOR . $fileName, json_encode($data))) {
                $group_name = $group_dao->getGroupName($group_id);
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE_GROUP, $group_id, ProjectLogDao::$OP_TYPE_OTHERS, "导出状态码分组：$group_name", date("Y-m-d H:i:s", time()));
                return $fileName;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * 导入分组
     * @param $project_id
     * @param $data
     * @return bool
     */
    public function importGroup(&$project_id, &$data)
    {
        $group_dao = new StatusCodeGroupDao();
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $_SESSION['userID'])) {
            return FALSE;
        }
        $result = $group_dao->importGroup($project_id, $data);
        if ($result) {
            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE_GROUP, $project_id, ProjectLogDao::$OP_TYPE_OTHERS, "导入状态码分组：{$data['groupName']}", date("Y-m-d H:i:s", time()));
            return $result;
        } else {
            return FALSE;
        }
    }
}

?>
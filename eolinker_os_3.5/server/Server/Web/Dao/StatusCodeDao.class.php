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
class StatusCodeDao
{
    /**
     * 添加状态码
     * @param $groupID int 分组ID
     * @param $codeDesc string 状态码描述，默认为NULL
     * @param $code string 状态码
     * @return bool|int
     */
    public function addCode(&$groupID, &$codeDesc, &$code)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_project_status_code (eo_project_status_code.groupID,eo_project_status_code.code,eo_project_status_code.codeDescription) VALUES (?,?,?);', array($groupID, $code, $codeDesc));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $db->getLastInsertID();

    }

    /**
     * 删除状态码
     * @param $codeID int 状态码ID
     * @return bool
     */
    public function deleteCode(&$codeID)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_project_status_code WHERE eo_project_status_code.codeID = ?;', array($codeID));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 获取状态码列表
     * @param $groupID int 分组ID
     * @return bool|array
     */
    public function getCodeList(&$groupID)
    {
        $db = getDatabase();

        $result = $db->prepareExecuteAll('SELECT eo_project_status_code.codeID,eo_project_status_code.code,eo_project_status_code.codeDescription,eo_project_status_code_group.groupName,eo_project_status_code_group.groupID,eo_project_status_code_group.parentGroupID FROM eo_project_status_code INNER JOIN eo_project_status_code_group ON eo_project_status_code.groupID = eo_project_status_code_group.groupID WHERE (eo_project_status_code_group.groupID = ? OR eo_project_status_code_group.parentGroupID = ?) ORDER BY eo_project_status_code.code ASC;', array($groupID, $groupID));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 获取所有状态码列表
     * @param $projectID int 项目ID
     * @return bool|array
     */
    public function getAllCodeList(&$projectID)
    {
        $db = getDatabase();

        $result = $db->prepareExecuteAll('SELECT eo_project_status_code_group.groupID,eo_project_status_code_group.parentGroupID,eo_project_status_code_group.groupName,eo_project_status_code.codeID,eo_project_status_code.code,eo_project_status_code.codeDescription FROM eo_project_status_code INNER JOIN eo_project_status_code_group ON eo_project_status_code.groupID = eo_project_status_code_group.groupID WHERE projectID = ? ORDER BY eo_project_status_code.code ASC;', array($projectID));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 修改状态码
     * @param $groupID int 分组ID
     * @param $codeID int 状态码ID
     * @param $code string 状态码
     * @param $codeDesc string 状态码描述，默认为NULL
     * @return bool
     */
    public function editCode(&$groupID, &$codeID, &$code, &$codeDesc)
    {
        $db = getDatabase();

        $db->prepareExecute('UPDATE eo_project_status_code SET eo_project_status_code.groupID = ?, eo_project_status_code.code = ? ,eo_project_status_code.codeDescription = ? WHERE codeID = ?;', array($groupID, $code, $codeDesc, $codeID));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 检查状态码与用户的联系
     * @param $codeID int 状态码ID
     * @param $userID int 用户ID
     * @return bool|int
     */
    public function checkStatusCodePermission(&$codeID, &$userID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_conn_project.projectID FROM eo_project_status_code INNER JOIN eo_conn_project INNER JOIN eo_project_status_code_group ON eo_conn_project.projectID = eo_project_status_code_group.projectID AND eo_project_status_code_group.groupID = eo_project_status_code.groupID WHERE codeID = ? AND userID = ?;', array($codeID, $userID));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }

    /**
     * 搜索状态码
     * @param $projectID int 项目ID
     * @param $tips string 搜索关键字
     * @return bool|array
     */
    public function searchStatusCode(&$projectID, &$tips)
    {
        $db = getDatabase();

        $result = $db->prepareExecuteAll('SELECT eo_project_status_code_group.groupID,eo_project_status_code_group.parentGroupID,eo_project_status_code_group.groupName,eo_project_status_code.codeID,eo_project_status_code.code,eo_project_status_code.codeDescription FROM eo_project_status_code INNER JOIN eo_project_status_code_group ON eo_project_status_code.groupID = eo_project_status_code_group.groupID WHERE projectID = ? AND (eo_project_status_code.code LIKE ? OR eo_project_status_code.codeDescription LIKE ?);', array($projectID, '%' . $tips . '%', '%' . $tips . '%'));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 获取状态码数量
     * @param $projectID int 项目ID
     * @return bool|int
     */
    public function getStatusCodeNum(&$projectID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT COUNT(*) AS num FROM eo_project_status_code LEFT JOIN eo_project_status_code_group ON eo_project_status_code.groupID = eo_project_status_code_group.groupID WHERE eo_project_status_code_group.projectID = ?;', array($projectID));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 批量删除状态码
     * @param $code_ids string 状态码列表
     * @return bool
     */
    public function deleteCodes(&$code_ids)
    {
        $db = getDatabase();
        $db->prepareExecuteAll("DELETE FROM eo_project_status_code WHERE codeID IN ($code_ids)", array());
        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 获取状态码名称
     * @param $code_ids
     * @return bool
     */
    public function getStatusCodes(&$code_ids)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT GROUP_CONCAT(DISTINCT eo_project_status_code.code) AS statusCodes FROM eo_project_status_code WHERE eo_project_status_code.codeID IN ($code_ids)", array());
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['statusCodes'];
        }
    }

    /**
     * 通过Excel批量添加状态码
     * @param $group_id
     * @param $code_list
     * @return bool
     */
    public function addStatusCodeByExcel(&$group_id, &$code_list)
    {
        $db = getDatabase();
        $db->beginTransaction();
        foreach ($code_list as $code) {
            $db->prepareExecute('INSERT INTO eo_project_status_code (code,codeDescription,groupID) VALUES (?,?,?);', array(
                $code['code'],
                $code['codeDesc'],
                $group_id
            ));
            if ($db->getAffectRow() < 1) {
                $db->rollback();
                return FALSE;
            }
        }
        $db->commit();
        return TRUE;
    }
}

?>
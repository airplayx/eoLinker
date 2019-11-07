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
class PartnerDao
{
    /**
     * 邀请协作人员
     * @param $projectID int 项目ID
     * @param $inviteUserID int 邀请人ID
     * @return bool|int
     */
    public function invitePartner(&$projectID, &$userID, &$inviteUserID)
    {
        $db = getDatabase();
        $db->prepareExecute('INSERT INTO eo_conn_project (eo_conn_project.projectID,eo_conn_project.userID,eo_conn_project.userType,eo_conn_project.inviteUserID) VALUES (?,?,2,?);', array(
            $projectID,
            $userID,
            $inviteUserID
        ));

        if ($db->getAffectRow() > 0)
            return $db->getLastInsertID();
        else
            return FALSE;
    }

    /**
     * 移除协作人员
     * @param $projectID int 项目ID
     * @param $connID int 用户与项目联系ID
     * @return bool
     */
    public function removePartner(&$projectID, &$connID)
    {
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_conn_project WHERE eo_conn_project.projectID = ? AND eo_conn_project.connID = ? AND eo_conn_project.userType != 0;', array(
            $projectID,
            $connID
        ));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取协作人员列表
     * @param $projectID int 项目ID
     * @return bool|array
     */
    public function getPartnerList(&$projectID)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT eo_conn_project.userID,eo_conn_project.connID,eo_conn_project.userType,eo_user.userName,eo_user.userNickName,eo_conn_project.partnerNickName FROM eo_conn_project INNER JOIN eo_user ON eo_conn_project.userID = eo_user.userID WHERE eo_conn_project.projectID = ? ORDER BY eo_conn_project.userType ASC;', array($projectID));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 退出协作项目
     * @param $projectID int 项目ID
     * @param $userID int 用户ID
     * @return bool
     */
    public function quitPartner(&$projectID, &$userID)
    {
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_conn_project WHERE eo_conn_project.projectID = ? AND eo_conn_project.userID = ? AND eo_conn_project.userType != 0;', array(
            $projectID,
            $userID
        ));

        if ($db->getAffectRow() > 0) {
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * 查询是否已经加入过项目
     * @param $projectID int 项目ID
     * @param $userName string 用户名
     * @return bool
     */
    public function checkIsInvited(&$projectID, &$userName)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT eo_conn_project.connID FROM eo_conn_project INNER JOIN eo_user ON eo_user.userID = eo_conn_project.userID WHERE eo_conn_project.projectID = ? AND eo_user.userName = ?;', array(
            $projectID,
            $userName
        ));
        if (empty($result))
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 获取用户ID
     * @param $connID int 用户与项目联系ID
     * @return bool|int
     */
    public function getUserID(&$connID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_conn_project.userID FROM eo_conn_project WHERE eo_conn_project.connID = ?;', array($connID));
        if (empty($result))
            return FALSE;
        else
            return $result['userID'];
    }

    /**
     * 修改协作成员的昵称
     * @param $project_id int 项目ID
     * @param $conn_id int 连接ID
     * @param $nick_name string 昵称
     * @return bool
     */
    public function editPartnerNickName(&$project_id, &$conn_id, &$nick_name)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_conn_project SET eo_conn_project.partnerNickName = ? WHERE eo_conn_project.connID = ? AND eo_conn_project.projectID = ?;', array(
            $nick_name,
            $conn_id,
            $project_id
        ));

        if ($db->getAffectRow() > 0) {
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * 修改协作成员的类型
     * @param $project_id int 项目ID
     * @param $conn_id int 连接ID
     * @param $user_type int 用户类型
     * @return bool
     */
    public function editPartnerType(&$project_id, &$conn_id, &$user_type)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_conn_project SET eo_conn_project.userType = ? WHERE eo_conn_project.connID = ? AND eo_conn_project.projectID = ?;', array(
            $user_type,
            $conn_id,
            $project_id
        ));

        if ($db->getAffectRow() > 0) {
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * 获取协作成员账号名
     * @param $user_id
     * @return bool
     */
    public function getPartnerUserCall(&$user_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_user.userName FROM eo_user WHERE eo_user.userID = ?;', array(
            $user_id
        ));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['userName'];
        }
    }

    public function getProjectInviteCode(&$project_id)
    {
        $db = getDatabase();
        // 尝试次数,超过3次则认为是服务器出错
        $count = 0;
        do {
            $count++;
            // 获取随机6位字符串
            $invite_code = '';
            $strPool = 'NMqlzxcvdfghjQXCER67ty5HuasJKLZYTWmPASDFGk12iBpn34UIb9werV8';
            for ($i = 0; $i <= 5; $i++) {
                $invite_code .= $strPool[rand(0, 58)];
            }

            // 查重
            $result = $db->prepareExecute('SELECT eo_project_invite.projectID FROM eo_project_invite WHERE eo_project_invite.projectInviteCode = ?;', array(
                $invite_code
            ));
        } while (!empty($result) && $count < 3);
        if (!empty($result)) {
            return FALSE;
        }
    }
}

?>
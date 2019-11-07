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
class UserDao
{
    /**
     * 修改密码
     * @param $hashPassword string 新密码
     * @param $userID int 用户ID
     * @return bool
     */
    public function changePassword($hashPassword, $userID)
    {
        $db = getDatabase();

        $db->prepareExecute('UPDATE eo_user SET eo_user.userPassword =? WHERE eo_user.userID = ?;', array(
            $hashPassword,
            $userID
        ));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 修改昵称
     * @param $userID int 用户ID
     * @param $nickName string 昵称
     * @return bool
     */
    public function changeNickName(&$userID, &$nickName)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_user SET eo_user.userNickName =? WHERE eo_user.userID = ?;', array(
            $nickName,
            $userID
        ));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 检查用户是否存在
     * @param $userName 用户名
     * @return bool|array
     */
    public function checkUserExist(&$userName)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_user.userID,eo_user.userNickName FROM eo_user WHERE eo_user.userName = ?;', array($userName));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

}

?>
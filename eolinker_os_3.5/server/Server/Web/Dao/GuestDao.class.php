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
class GuestDao
{
    /**
     * 用户名注册
     * @param $userName string 用户名
     * @param $hashPassword string 密码
     * @param $userNickName string 昵称
     * @return bool
     */
    public function register(&$userName, &$hashPassword, &$userNickName)
    {
        $db = getDatabase();

        //判断是否已存在用户
        $result = $db->prepareExecute('SELECT eo_user.userID FROM eo_user WHERE userName=?;', array($userName));

        //已存在则返回
        if (!empty($result))
            return FALSE;

        //若不存在则插入
        $result = $db->prepareExecute('INSERT INTO eo_user (eo_user.userName,eo_user.userPassword,eo_user.userNickName) VALUES (?,?,?);', array(
            $userName,
            $hashPassword,
            $userNickName
        ));

        //插入成功
        if ($db->getAffectRow() > 0)
            return $db->getLastInsertID();
        else
            return FALSE;
    }

    /**
     * 检查用户名是否存在
     * @param $userName string 用户名
     * @return bool
     */
    public function checkUserNameExist(&$userName)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT * FROM eo_user WHERE eo_user.userName = ?;', array($userName));

        if (empty($result))
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取用户信息
     * @param $loginName string 登录用户名
     * @return bool
     */
    public function getLoginInfo(&$loginName)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_user.userID,eo_user.userName,eo_user.userPassword,eo_user.userNickName FROM eo_user WHERE eo_user.userName = ?;', array($loginName));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

}

?>
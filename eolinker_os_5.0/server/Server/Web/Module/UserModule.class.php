<?php
/**
 * @name EOLINKER ams open source，EOLINKER open source version
 * @link https://global.eolinker.com/
 * @package EOLINKER
 * @author www.eolinker.com eoLinker Ltd.co 2015-2018
 * 
 * eoLinker is the world's leading and domestic largest online API interface management platform, providing functions such as automatic generation of API documents, API automated testing, Mock testing, team collaboration, etc., aiming to solve the problem of low development efficiency caused by separation of front and rear ends.
 * If you have any problems during the process of use, please join the user discussion group for feedback, we will solve the problem for you with the fastest speed and best service attitude.
 *
 * 
 *
 * Website：https://global.eolinker.com/
 * Slack：eolinker.slack.com
 * facebook：@EoLinker
 * twitter：@eoLinker
 */

class UserModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * Get User Info
     */
    public function getUserInfo()
    {
        $dao = new MessageDao;
        $userInfo['userID'] = $_SESSION['userID'];
        $userInfo['userNickName'] = $_SESSION['userNickName'];
        $userInfo['userName'] = $_SESSION['userName'];
        $userInfo['unreadMsgNum'] = $dao->getUnreadMessageNum($_SESSION['userID']);
        return $userInfo;
    }

    /**
     * Change Passsword
     * @param $oldPassword string 
     * @param $newPassword string 
     * @return bool
     */
    public function changePassword(&$oldPassword, &$newPassword)
    {
        $guestDao = new GuestDao;
        $userDao = new UserDao;
        $userInfo = $guestDao->getLoginInfo($_SESSION['userName']);

        if (md5($oldPassword) == $userInfo['userPassword']) {
            return $userDao->changePassword(md5($newPassword), $_SESSION['userID']);
        } else
            return FALSE;
    }

    /**
     * Edit Nick Name
     * @param $nickName string
     * @return bool
     */
    public function changeNickName(&$nickName)
    {
        $dao = new UserDao;
        if ($dao->changeNickName($_SESSION['userID'], $nickName)) {
            $_SESSION['userNickName'] = $nickName;
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * Check User Exist
     * @param $userName string
     * @return bool|array
     */
    public function checkUserExist(&$userName)
    {
        $dao = new UserDao;
        return $dao->checkUserExist($userName);
    }

}

?>
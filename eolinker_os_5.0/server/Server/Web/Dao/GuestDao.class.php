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

class GuestDao
{
    /**
     * User register
     * @param $userName string username
     * @param $hashPassword string password
     * @param $userNickName string nickname
     * @return bool
     */
    public function register(&$userName, &$hashPassword, &$userNickName)
    {
        $db = getDatabase();

        
        $result = $db->prepareExecute('SELECT eo_user.userID FROM eo_user WHERE userName=?;', array($userName));

        
        if (!empty($result))
            return FALSE;

       
        $result = $db->prepareExecute('INSERT INTO eo_user (eo_user.userName,eo_user.userPassword,eo_user.userNickName) VALUES (?,?,?);', array(
            $userName,
            $hashPassword,
            $userNickName
        ));

       
        if ($db->getAffectRow() > 0)
            return $db->getLastInsertID();
        else
            return FALSE;
    }

    /**
     *check user name exist
     * @param $userName string 
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
     * get login info
     * @param $loginName string 
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
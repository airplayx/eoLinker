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
class UserDao
{
    /**
     * change password
     * @param $hashPassword string new password
     * @param $userID int UserID
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
     * change nick name
     * @param $userID int userID
     * @param $nickName string nickname
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
     * check user exist
     * @param $userName usernmae
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
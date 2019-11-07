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

class MessageDao
{
    /**
     * get Message list
     * @param $userID int UserID
     * @param $page int Page
     * @return bool|array
     */
    public function getMessageList(&$userID, &$page)
    {
        $db = getDatabase();
        $result['messageList'] = $db->prepareExecuteAll('SELECT eo_message.msgID,eo_message.msgType,eo_message.msg,eo_message.summary,eo_message.msgSendTime,eo_message.isRead FROM eo_message WHERE eo_message.toUserID = ? ORDER BY eo_message.msgSendTime DESC LIMIT ?,15;', array(
            $userID,
            ($page - 1) * 15
        ));

        $msgCount = $db->prepareExecute('SELECT COUNT(eo_message.msgID) AS msgCount FROM eo_message WHERE eo_message.toUserID = ?', array($userID));

        $result['msgCount'] = $msgCount['msgCount'];

        if (empty($result['messageList'][0]))
            return FALSE;
        else
            return $result;
    }

    /**
     * read message
     * @param $msgID int messageID
     * @return bool
     */
    public function readMessage(&$msgID)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_message SET eo_message.isRead = 1 WHERE eo_message.msgID = ?;', array($msgID));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * delete message
     * @param $msgID int messageID
     * @return bool
     */
    public function delMessage(&$msgID)
    {
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_message WHERE eo_message.msgID = ?;', array($msgID));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * clean message
     * @param $userID int userID
     * @return bool
     */
    public function cleanMessage(&$userID)
    {
        //本接口只能清空所有接收到的消息
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_message WHERE eo_message.toUserID = ?;', array($userID));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * send message
     * @param $fromUserID int 
     * @param $toUserID int 
     * @param $msgType int 
     * @param $summary string 
     * @param $msg string 
     * @return bool
     */
    public function sendMessage($fromUserID, $toUserID, $msgType, &$summary, &$msg)
    {
        $db = getDatabase();
        $db->prepareExecute('INSERT INTO eo_message (eo_message.fromUserID,eo_message.toUserID,eo_message.msgType,eo_message.summary,eo_message.msg) VALUES (?,?,?,?,?);', array(
            $fromUserID,
            $toUserID,
            $msgType,
            $summary,
            $msg
        ));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Get unread message
     * @param $userID int userID
     * @return bool|int
     */
    public function getUnreadMessageNum(&$userID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT COUNT(eo_message.msgID) AS unreadMsgNum FROM eo_message WHERE eo_message.toUserID = ? AND eo_message.isRead = 0;', array($userID));

        if (empty($result))
            return FALSE;
        else
            return $result['unreadMsgNum'];
    }

}

?>
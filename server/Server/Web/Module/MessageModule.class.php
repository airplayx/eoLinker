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

class MessageModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * Get Message List
     * @param $page int Page
     * @return bool|array
     */
    public function getMessageList(&$page)
    {
        $dao = new MessageDao;
        $result = $dao->getMessageList($_SESSION['userID'], $page);
        if ($result) {
            $result['pageCount'] = ceil($result['msgCount'] / 15);
            $result['pageNow'] = $page;
            return $result;
        } else
            return FALSE;
    }

    /**
     * Read Message
     * @param $msgID int MessageID
     * @return bool
     */
    public function readMessage(&$msgID)
    {
        $dao = new MessageDao;
        return $dao->readMessage($msgID);
    }

    /**
     * Delete Info
     * @param $msgID int InformationID
     * @return bool
     */
    public function delMessage(&$msgID)
    {
        $dao = new MessageDao;
        return $dao->delMessage($msgID);
    }

    /**
     * Clear Message
     */
    public function cleanMessage()
    {
        $dao = new MessageDao;
        return $dao->cleanMessage($_SESSION['userID']);
    }

    /**
     * Get Unread Message Num
     */
    public function getUnreadMessageNum()
    {
        $dao = new MessageDao;
        return $dao->getUnreadMessageNum($_SESSION['userID']);
    }

}

?>
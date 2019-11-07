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

class MessageController
{
    // Return json Type
    private $returnJson = array('type' => 'message');

    /**
     * Check Login
     */
    public function __construct()
    {
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * Get Message List
     */
    public function getMessageList()
    {
        $page = securelyInput('page', 1);
        $server = new MessageModule;
        $result = $server->getMessageList($page);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson = array_merge($this->returnJson, $result);
        } else {
            $this->returnJson['statusCode'] = '260001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Read Message
     */
    public function readMessage()
    {
        $msgID = securelyInput('msgID');
        if (!preg_match('/^[0-9]{1,11}$/', $msgID)) {
            $this->returnJson['statusCode'] = '260004';
        } else {
            $server = new MessageModule;
            if ($server->readMessage($msgID)) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '260002';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Delete Message
     */
    public function delMessage()
    {
        $msgID = securelyInput('msgID');
        if (!preg_match('/^[0-9]{1,11}$/', $msgID)) {
            $this->returnJson['statusCode'] = '260004';
        } else {
            $server = new MessageModule;
            if ($server->delMessage($msgID)) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '260005';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Clean Message
     */
    public function cleanMessage()
    {
        $server = new MessageModule;
        $result = $server->cleanMessage();
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '260001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get Unread Message Num
     */
    public function getUnreadMessageNum()
    {
        $server = new MessageModule;
        $result = $server->getUnreadMessageNum();
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['unreadMsgNum'] = $result;
        } else {
            $this->returnJson['statusCode'] = '260001';
        }
        exitOutput($this->returnJson);
    }

}

?>
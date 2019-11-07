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
class MessageDao
{
    /**
     * 获取消息列表
     * @param $userID int 用户ID
     * @param $page int 页码
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
     * 已阅消息
     * @param $msgID int 消息ID
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
     * 删除消息
     * @param $msgID int 消息ID
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
     * 清空消息
     * @param $userID int 用户ID
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
     * 发送消息
     * @param $fromUserID int 发送者用户ID，系统消息ID为0
     * @param $toUserID int 接收者用户ID
     * @param $msgType int 消息类型 [0/1]=>[官方消息/团队消息]
     * @param $summary string 消息概要，默认为NULL
     * @param $msg string 消息内容
     * @return bool
     */
    public function sendMessage($fromUserID, $toUserID, $msgType, &$summary, &$msg)
    {
        //fromUserID默认为0也就是官方消息
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
     * 获取未读消息数量
     * @param $userID int 用户ID
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
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
class MessageController
{
    // 返回json类型
    private $returnJson = array('type' => 'message');

    /**
     * 检查登录状态
     */
    public function __construct()
    {
        // 身份验证
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * 获取消息列表
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
            //消息列表为空
            $this->returnJson['statusCode'] = '260001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 已阅消息
     */
    public function readMessage()
    {
        $msgID = securelyInput('msgID');

        // 判断ID格式是否合法
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
     * 删除消息
     */
    public function delMessage()
    {
        $msgID = securelyInput('msgID');

        // 判断ID格式是否合法
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
     * 清空消息
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
     * 获取未读消息数量
     */
    public function getUnreadMessageNum()
    {
        $server = new MessageModule;
        $result = $server->getUnreadMessageNum();
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['unreadMsgNum'] = $result;
        } else {
            //消息列表为空
            $this->returnJson['statusCode'] = '260001';
        }
        exitOutput($this->returnJson);
    }

}

?>
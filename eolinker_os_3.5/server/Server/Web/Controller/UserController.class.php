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
class UserController
{
    // 返回json类型
    private $returnJson = array('type' => 'user');

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
     * 退出登录
     */
    public function logout()
    {
        @session_start();
        @session_destroy();
        $this->returnJson['statusCode'] = '000000';
        exitOutput(json_encode($this->returnJson));
    }

    /**
     * 修改密码
     */
    public function changePassword()
    {
        $oldPassword = securelyInput('oldPassword');
        $newPassword = securelyInput('newPassword');

        if (!preg_match('/^[0-9a-zA-Z]{32}$/', $newPassword) || !preg_match('/^[0-9a-zA-Z]{32}$/', $oldPassword)) {
            //密码非法
            $this->returnJson['statusCode'] = '130002';
        } elseif ($oldPassword == $newPassword) {
            //密码相同
            $this->returnJson['statusCode'] = '000000';
        } else {
            $server = new UserModule;
            $result = $server->changePassword($oldPassword, $newPassword);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '130006';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改昵称
     */
    public function changeNickName()
    {
        $nickNameLength = mb_strlen(quickInput('nickName'), 'utf8');
        $nickName = securelyInput('nickName');

        if ($nickNameLength > 20) {
            //昵称格式非法
            $this->returnJson['statusCode'] = '130008';
        } else {
            $server = new UserModule;
            $result = $server->changeNickName($nickName);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '130009';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 确认用户名
     */
    public function confirmUserName()
    {
        $userName = securelyInput('userName');

        //验证用户名,4~16位非纯数字，英文数字下划线组合，只能以英文开头
        if (!preg_match('/^[a-zA-Z][0-9a-zA-Z_]{3,59}$/', $userName)) {
            //用户名非法
            $this->returnJson['statusCode'] = '130001';
        } else {
            $server = new UserModule;
            $result = $server->confirmUserName($userName);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '130010';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo()
    {
        $server = new UserModule;
        $result = $server->getUserInfo();
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['userInfo'] = $result;
        } else {
            $this->returnJson['statusCode'] = '130013';
        }
        exitOutput($this->returnJson);
    }

}

?>
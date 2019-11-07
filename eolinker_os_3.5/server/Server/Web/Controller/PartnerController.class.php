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
class PartnerController
{
    // 返回json类型
    private $returnJson = array('type' => 'partner');

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
     * 获取人员信息
     */
    public function getPartnerInfo()
    {
        $userName = securelyInput('userName');
        $projectID = securelyInput('projectID');

        if (!preg_match('/^([a-zA-Z][0-9a-zA-Z_]{3,59})$/', $userName)) {
            //userName格式非法
            $this->returnJson['statusCode'] = '250001';
        } else {
            $userServer = new UserModule;
            $userInfo = $userServer->checkUserExist($userName);
            if ($userInfo) {
                $partnerServer = new PartnerModule;
                if ($partnerServer->checkIsInvited($projectID, $userName)) {
                    $this->returnJson['statusCode'] = '250007';
                    $this->returnJson['userInfo']['userName'] = $userName;
                    $this->returnJson['userInfo']['userNickName'] = $userInfo['userNickName'];
                    $this->returnJson['userInfo']['isInvited'] = 1;
                } else {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['userInfo']['userName'] = $userName;
                    $this->returnJson['userInfo']['userNickName'] = $userInfo['userNickName'];
                    $this->returnJson['userInfo']['isInvited'] = 0;
                }
            } else {
                //用户不存在
                $this->returnJson['statusCode'] = '250002';
            }

        }
        exitOutput($this->returnJson);
    }

    /**
     * 邀请协作人员
     */
    public function invitePartner()
    {
        $userName = securelyInput('userName');
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }

        if (!preg_match('/^([a-zA-Z][0-9a-zA-Z_]{3,59})$/', $userName)) {
            //userName格式非法
            $this->returnJson['statusCode'] = '250001';
        } else {
            $userServer = new UserModule;
            $userInfo = $userServer->checkUserExist($userName);
            if ($userInfo) {
                $partnerServer = new PartnerModule;
                //检查是否已经被邀请过
                if ($partnerServer->checkIsInvited($projectID, $userName)) {
                    //已被邀请
                    $this->returnJson['statusCode'] = '250007';
                } else {
                    if ($connID = $partnerServer->invitePartner($projectID, $userInfo['userID'])) {
                        $this->returnJson['statusCode'] = '000000';
                        $this->returnJson['connID'] = $connID;
                    } else {
                        //添加协作成员失败，成员已经添加
                        $this->returnJson['statusCode'] = '250003';
                    }
                }
            } else {
                //用户不存在
                $this->returnJson['statusCode'] = '250002';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 移除协作人员
     */
    public function removePartner()
    {
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $connID = securelyInput('connID');

        $server = new PartnerModule;
        if ($server->removePartner($projectID, $connID)) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            //移除成员失败，成员已经被移出
            $this->returnJson['statusCode'] = '250004';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取协作人员列表
     */
    public function getPartnerList()
    {
        $projectID = securelyInput('projectID');

        $server = new PartnerModule;
        $result = $server->getPartnerList($projectID);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['partnerList'] = $result;
        } else {
            //协作人员列表为空
            $this->returnJson['statusCode'] = '250005';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 退出协作项目
     */
    public function quitPartner()
    {
        $projectID = securelyInput('projectID');

        $server = new PartnerModule;
        $result = $server->quitPartner($projectID);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            //退出协作项目失败，已退出协作项目
            $this->returnJson['statusCode'] = '250006';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改协作成员的昵称
     */
    public function editPartnerNickName()
    {
        $projectID = securelyInput('projectID');
        $conn_id = securelyInput('connID');
        $nick_name = securelyInput('nickName');
        $name_length = mb_strlen(quickInput('nickName'), 'utf8');
        //判断关联ID是否合法
        if (!preg_match('/^[0-9]{1,11}$/', $conn_id)) {
            //关联ID格式非法
            $this->returnJson['statusCode'] = '250003';
        } elseif ($name_length < 1 || $name_length > 32) {
            //昵称格式非法
            $this->returnJson['statusCode'] = '250004';
        } else {
            $module = new PartnerModule();
            $result = $module->editPartnerNickName($projectID, $conn_id, $nick_name);
            if ($result) {
                //成功
                $this->returnJson['statusCode'] = '000000';
            } else {
                //失败
                $this->returnJson['statusCode'] = '250000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改协作成员的类型
     */
    public function editPartnerType()
    {
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $conn_id = securelyInput('connID');
        $user_type = securelyInput('userType');

        if (!preg_match('/^[0-9]{1,11}$/', $conn_id)) {
            //关联ID格式非法
            $this->returnJson['statusCode'] = '250003';
        } elseif (!preg_match('/^[1-3]{1}$/', $user_type)) {
            //用户类型格式非法
            $this->returnJson['statusCode'] = '250005';
        } else {
            $module = new PartnerModule();
            $result = $module->editPartnerType($projectID, $conn_id, $user_type);
            if ($result) {
                //成功
                $this->returnJson['statusCode'] = '000000';
            } else {
                //失败
                $this->returnJson['statusCode'] = '250000';
            }
        }
        exitOutput($this->returnJson);
    }

    public function getProjectInviteCode()
    {
        $projectID = securelyInput('projectID');
    }

    public function joinProjectByInviteCode()
    {
        $projectID = securelyInput('projectID');
    }
}

?>
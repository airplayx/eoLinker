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
class GroupController
{
    // 返回json类型
    private $returnJson = array('type' => 'group');

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
     * 添加项目api分组
     */
    public function addGroup()
    {
        $nameLen = mb_strlen(quickInput('groupName'), 'utf8');
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $groupName = securelyInput('groupName');
        $parentGroupID = securelyInput('parentGroupID', NULL);
        // 判断项目ID和组名格式是否合法
        if (preg_match('/^[0-9]{1,11}$/', $projectID) && $nameLen >= 1 && $nameLen <= 30) {
            // 项目ID和组名合法
            $service = new GroupModule();
            $result = $service->addGroup($projectID, $groupName, $parentGroupID);
            // 判断添加项目api分组是否成功
            if ($result) {
                // 添加项目api分组成功
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['groupID'] = $result;
            } else
                // 添加项目api分组失败
                $this->returnJson['statusCode'] = '150001';
        } else {
            $this->returnJson['statusCode'] = '150002';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除项目api分组
     */
    public function deleteGroup()
    {
        $groupID = securelyInput('groupID');
        $module = new GroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        // 判断分组ID格式是否合法
        if (preg_match('/^[0-9]{1,11}$/', $groupID)) {
            // 分组ID格式合法
            $service = new GroupModule();
            $result = $service->deleteGroup($groupID);
            // 判断删除项目api分组是否成功
            if ($result)
                // 删除项目api分组成功
                $this->returnJson['statusCode'] = '000000';
            else
                // 删除api分组失败
                $this->returnJson['statusCode'] = '150003';
        } else {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '150004';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取项目api分组列表
     */
    public function getGroupList()
    {
        $projectID = securelyInput('projectID');
        if (preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $service = new GroupModule;
            $result = $service->getGroupList($projectID);
            $orderList = $service->getGroupOrderList($projectID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['groupList'] = $result;
                $this->returnJson['groupOrder'] = $orderList;
            } else {
                $this->returnJson['statusCode'] = '150008';
            }
        } else {
            $this->returnJson['statusCode'] = '150007';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改api分组
     */
    public function editGroup()
    {
        $nameLen = mb_strlen(quickInput('groupName'), 'utf8');
        $groupID = securelyInput('groupID');
        $parentGroupID = securelyInput('parentGroupID');
        $module = new GroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $groupName = securelyInput('groupName');
        // 判断分组ID和组名格式是否合法
        if (preg_match('/^[0-9]{1,11}$/', $groupID) && $nameLen >= 1 && $nameLen <= 30) {
            if ($groupID == $parentGroupID) {
                $this->returnJson['statusCode'] = '150008';
                exitOutput($this->returnJson);
            }
            $service = new GroupModule();
            $result = $service->editGroup($groupID, $groupName, $parentGroupID);
            if ($result)
                // 修改api分组成功
                $this->returnJson['statusCode'] = '000000';
            else
                // 修改api分组失败
                $this->returnJson['statusCode'] = '150005';
        } else {
            // 分组ID和组名格式不合法
            $this->returnJson['statusCode'] = '150002';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改分组列表排序
     */
    public function sortGroup()
    {
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        //排序json字符串
        $orderList = quickInput('orderList');
        //判断排序格式是否合法
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '150007';
        } else if (empty($orderList)) {
            //排序格式非法
            $this->returnJson['statusCode'] = '150004';
        } else {
            $service = new GroupModule;
            $result = $service->sortGroup($projectID, $orderList);
            //验证结果
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '150000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 导出接口分组
     */
    public function exportGroup()
    {
        //分组ID
        $group_id = securelyInput('groupID');
        if (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '150003';
        } else {
            $service = new GroupModule();
            $user_type = $service->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $result = $service->exportGroup($group_id);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['fileName'] = $result;
                } else {
                    $this->returnJson['statusCode'] = '150000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 导入接口分组
     */
    public function importGroup()
    {
        $project_id = securelyInput('projectID');
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '150007';
        } //判断导入数据是否为空
        elseif (empty($data)) {
            $this->returnJson['statusCode'] = '150005';
            exitOutput($this->returnJson);
        } else {
            $service = new ProjectModule();
            $user_type = $service->getUserType($project_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            }
            $server = new GroupModule();
            $result = $server->importGroup($project_id, $data);
            //验证结果
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '150000';
            }
        }
        exitOutput($this->returnJson);
    }
}

?>
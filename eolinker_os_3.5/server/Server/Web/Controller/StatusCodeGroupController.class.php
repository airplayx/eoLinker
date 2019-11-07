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
class StatusCodeGroupController
{

    // 返回json类型
    private $returnJson = array('type' => 'status_code_group');

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
     * 添加分组
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

        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            //项目ID格式不合法
            $this->returnJson['statusCode'] = '180005';
        } elseif (!($nameLen >= 1 && $nameLen <= 32)) {
            //分组名称不合法
            $this->returnJson['statusCode'] = '180004';
        } else {
            $service = new StatusCodeGroupModule;
            $result = $service->addGroup($projectID, $groupName, $parentGroupID);

            if ($result) {
                //添加分组成功
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['statusGroupID'] = $result;
            } else {
                //添加分组失败
                $this->returnJson['statusCode'] = '180002';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除分组
     */
    public function deleteGroup()
    {
        $groupID = securelyInput('groupID');
        $module = new StatusCodeGroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }

        if (!preg_match('/^[0-9]{1,11}$/', $groupID)) {
            //分组ID格式不合法
            $this->returnJson['statusCode'] = '180003';
        } else {
            $service = new StatusCodeGroupModule;
            $result = $service->deleteGroup($groupID);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '180006';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取分组列表
     */
    public function getGroupList()
    {
        $projectID = securelyInput('projectID');

        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            //项目ID格式不合法
            $this->returnJson['statusCode'] = '180005';
        } else {
            $service = new StatusCodeGroupModule;
            $result = $service->getGroupList($projectID);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson = array_merge($this->returnJson, $result);
            } else {
                $this->returnJson['statusCode'] = '180001';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改分组
     */
    public function editGroup()
    {
        $nameLen = mb_strlen(quickInput('groupName'), 'utf8');
        $groupID = securelyInput('groupID');
        $parentGroupID = securelyInput('parentGroupID');
        $module = new StatusCodeGroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $groupName = securelyInput('groupName');

        if (!preg_match('/^[0-9]{1,11}$/', $groupID) || ($parentGroupID != NULL && !preg_match('/^[0-9]{1,11}$/', $parentGroupID))) {
            //项目ID格式不合法
            $this->returnJson['statusCode'] = '180003';
        } elseif (!($nameLen >= 1 && $nameLen <= 32)) {
            //分组名称不合法
            $this->returnJson['statusCode'] = '180004';
        } elseif ($groupID == $parentGroupID) {
            $this->returnJson['statusCode'] = '180009';
        } else {
            $service = new StatusCodeGroupModule;
            $result = $service->editGroup($groupID, $groupName, $parentGroupID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '180007';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改状态码分组列表排序
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
            $this->returnJson['statusCode'] = '180005';
        } else if (empty($orderList)) {
            //排序格式非法
            $this->returnJson['statusCode'] = '180008';
        } else {
            $service = new StatusCodeGroupModule();
            $result = $service->sortGroup($projectID, $orderList);
            //验证结果
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '180000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 导出分组
     */
    public function exportGroup()
    {
        //分组ID
        $group_id = securelyInput('groupID');
        if (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '180003';
        } else {
            $service = new StatusCodeGroupModule();
            $user_type = $service->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $result = $service->exportGroup($group_id);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['fileName'] = $result;
                } else {
                    $this->returnJson['statusCode'] = '180000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 导入分组
     */
    public function importGroup()
    {
        $project_id = securelyInput('projectID');
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '180007';
        } //判断导入数据是否为空
        elseif (empty($data)) {
            $this->returnJson['statusCode'] = '180005';
            exitOutput($this->returnJson);
        } else {
            $service = new ProjectModule();
            $user_type = $service->getUserType($project_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            }
            $server = new StatusCodeGroupModule();
            $result = $server->importGroup($project_id, $data);
            //验证结果
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '180000';
            }
        }
        exitOutput($this->returnJson);
    }
}

?>
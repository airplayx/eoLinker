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
class DocumentGroupController
{
    private $user_id;
    // return an json object
    // 返回json类型
    private $returnJson = array('type' => 'document_group');

    /**
     * Checkout login status
     * 验证登录状态
     */
    public function __construct()
    {
        // identity authentication
        // 身份验证
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        } else {
            $this->user_id = $_SESSION['userID'];
        }
    }

    /**
     * 添加文档分组
     */
    public function addGroup()
    {
        $project_id = securelyInput('projectID');
        $name_length = mb_strlen(quickInput('groupName'), 'utf8');
        $group_name = securelyInput('groupName');
        $parent_group_id = securelyInput('parentGroupID', NULL);

        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '220005';
        } elseif ($name_length < 1 || $name_length > 32) {
            //分组名称格式非法
            $this->returnJson['statusCode'] = '220001';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $parent_group_id) && $parent_group_id != NULL) {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '220002';
        } else {
            $project_module = new ProjectModule();
            $user_type = $project_module->getUserType($project_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $service = new DocumentGroupModule();
                $result = $service->addGroup($project_id, $this->user_id, $group_name, $parent_group_id);

                if ($result) {
                    // 添加项目api分组成功
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['groupID'] = $result;
                } else {
                    // 添加项目api分组失败
                    $this->returnJson['statusCode'] = '220000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除api分组
     */
    public function deleteGroup()
    {
        $group_id = securelyInput('groupID');

        if (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '220003';
        } else {
            // 分组ID格式合法
            $service = new DocumentGroupModule();
            $user_type = $service->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $result = $service->deleteGroup($this->user_id, $group_id);
                if ($result) {
                    // 删除项目api分组成功
                    $this->returnJson['statusCode'] = '000000';
                } else {
                    // 删除api分组失败
                    $this->returnJson['statusCode'] = '220000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取api分组
     */
    public function getGroupList()
    {
        $project_id = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '220005';
        } else {
            $service = new DocumentGroupModule();
            $result = $service->getGroupList($project_id, $this->user_id);
            //验证结果
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson = array_merge($this->returnJson, $result);
            } else {
                $this->returnJson['statusCode'] = '220000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改api分组
     */
    public function editGroup()
    {
        $name_length = mb_strlen(quickInput('groupName'), 'utf8');
        $group_id = securelyInput('groupID');
        $group_name = securelyInput('groupName');
        $parent_group_id = securelyInput('parentGroupID', NULL);

        // 判断分组ID和组名格式是否合法
        if ($name_length < 1 && $name_length > 32) {
            $this->returnJson['statusCode'] = '220001';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $parent_group_id) && $parent_group_id != NULL) {
            // 父分组ID格式不合法
            $this->returnJson['statusCode'] = '220002';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '220003';
        } elseif ($group_id == $parent_group_id) {
            //父分组和子分组
            $this->returnJson['statusCode'] = '220006';
        } else {
            $service = new DocumentGroupModule();
            $user_type = $service->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $result = $service->editGroup($this->user_id, $group_id, $group_name, $parent_group_id);
                if ($result)
                    // 修改api分组成功
                    $this->returnJson['statusCode'] = '000000';
                else
                    // 修改api分组失败
                    $this->returnJson['statusCode'] = '220000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 分组排序
     */
    public function sortDocumentGroup()
    {
        $project_id = securelyInput('projectID');
        $order_list = quickInput('orderList');
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '220005';
        } //判断排序格式是否合法
        elseif (empty($order_list)) {
            //排序格式非法
            $this->returnJson['statusCode'] = '220004';
        } else {
            $project_module = new ProjectModule();
            $user_type = $project_module->getUserType($project_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $service = new DocumentGroupModule();
                $result = $service->updateGroupOrder($project_id, $order_list, $this->user_id);
                if ($result) {
                    //成功
                    $this->returnJson['statusCode'] = '000000';
                } else {
                    //失败
                    $this->returnJson['statusCode'] = '220000';
                }
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
            $this->returnJson['statusCode'] = '220003';
        } else {
            $service = new DocumentGroupModule();
            $user_type = $service->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $result = $service->exportGroup($group_id);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['fileName'] = $result;
                } else {
                    $this->returnJson['statusCode'] = '220000';
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
            $this->returnJson['statusCode'] = '220007';
        } //判断导入数据是否为空
        elseif (empty($data)) {
            $this->returnJson['statusCode'] = '220005';
            exitOutput($this->returnJson);
        } else {
            $service = new ProjectModule();
            $user_type = $service->getUserType($project_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            }
            $server = new DocumentGroupModule();
            $result = $server->importGroup($project_id, $data);
            //验证结果
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '220000';
            }
        }
        exitOutput($this->returnJson);
    }
}
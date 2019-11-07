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
class EnvController
{
    // 返回json类型
    private $returnJson = array('type' => 'environment');

    /**
     * 构造函数,在此判断用户登录状态以及初始化各变量
     */
    public function __construct()
    {
        // 身份验证
        $module = new GuestModule;
        if (!$module->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * 获取项目环境列表
     */
    public function getEnvList()
    {
        $service = new EnvModule;
        $projectID = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '170003';
        } else {
            $result = $service->getEnvList($projectID);
            //验证结果
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['envList'] = $result;
            } else {
                //环境列表为空
                $this->returnJson['statusCode'] = '170000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 添加项目环境
     */
    public function addEnv()
    {
        //环境名称
        $env_name = securelyInput('envName');
        //环境名称长度
        $name_length = mb_strlen(quickInput('envName'), 'utf8');
        //前置URI地址
        $front_uri = securelyInput('frontURI');
        //请求头部
        $headers = json_decode(quickInput('headers'), TRUE);
        //全局变量
        $params = json_decode(quickInput('params'), TRUE);
        //额外参数
        $additional_params = json_decode(quickInput('additionalParams'), TRUE);
        $projectID = securelyInput('projectID');
        $apply_protocol = -1;
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '170003';
        } //判断名称长度是否合法
        elseif ($name_length < 1 || $name_length > 32) {
            //环境名称格式非法
            $this->returnJson['statusCode'] = '170001';
        } else {
            $service = new EnvModule;
            $result = $service->addEnv($projectID, $env_name, $front_uri, $headers, $params, $apply_protocol, $additional_params);
            //验证结果是否成功
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['envID'] = $result;
            } else {
                $this->returnJson['statusCode'] = '170000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除项目环境
     */
    public function deleteEnv()
    {
        $env_id = securelyInput('envID');
        $project_id = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '170003';
        } //判断环境ID是否合法
        elseif (!preg_match('/^[0-9]{1,11}$/', $env_id)) {
            //环境ID不合法
            $this->returnJson['statusCode'] = '170002';
        } else {
            $service = new EnvModule();
            if ($service->deleteEnv($project_id, $env_id)) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                //删除环境失败，projectID与envID不匹配
                $this->returnJson['statusCode'] = '170000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改项目环境
     */
    public function editEnv()
    {
        $env_id = securelyInput('envID');
        $env_name = securelyInput('envName');
        $name_length = mb_strlen(quickInput('envName'), 'utf8');
        //前置URI地址
        $front_uri = securelyInput('frontURI');
        //请求头部
        $headers = json_decode(quickInput('headers'), TRUE);
        //全局变量
        $params = json_decode(quickInput('params'), TRUE);
        //额外参数
        $additional_params = json_decode(quickInput('additionalParams'), TRUE);
        $apply_protocol = -1;
        if ($name_length < 1 || $name_length > 32) {
            //环境名称格式非法
            $this->returnJson['statusCode'] = '170001';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $env_id)) {
            //环境ID不合法
            $this->returnJson['statusCode'] = '170002';
        } else {
            $service = new EnvModule();
            if ($service->editEnv($env_id, $env_name, $front_uri, $headers, $params, $apply_protocol, $additional_params)) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                //修改失败
                $this->returnJson['statusCode'] = '170000';
            }
        }
        exitOutput($this->returnJson);
    }
}
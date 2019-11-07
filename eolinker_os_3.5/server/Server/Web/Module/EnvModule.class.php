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
class EnvModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * 获取环境列表
     * @param $project_id int 项目的数字ID
     * @return bool|array
     */
    public function getEnvList(&$project_id)
    {
        $projectDao = new ProjectDao;
        if (!$projectDao->checkProjectPermission($project_id, $_SESSION['userID'])) {
            return FALSE;
        }
        $env_dao = new EnvDao;
        return $env_dao->getEnvList($project_id);
    }

    /**
     * 添加环境
     * @param $project_id int 项目的数字ID
     * @param $env_name string 环境名称
     * @param $front_uri string 前置URI
     * @param $headers array 请求头部
     * @param $params array 全局变量
     * @param $apply_protocol int 应用的请求类型,[-1]=>[所有请求类型]
     * @param $additional_params array 额外参数
     * @return bool|int
     */
    public function addEnv(&$project_id, &$env_name, &$front_uri, &$headers, &$params, $apply_protocol, &$additional_params)
    {
        $env_dao = new EnvDao;
        $projectDao = new ProjectDao;
        if (!$projectDao->checkProjectPermission($project_id, $_SESSION['userID'])) {
            return FALSE;
        }
        $env_id = $env_dao->addEnv($project_id, $env_name, $front_uri, $headers, $params, $apply_protocol, $additional_params);
        if ($env_id) {
            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_ENVIRONMENT, $env_id, ProjectLogDao::$OP_TYPE_ADD, "添加环境:'{$env_name}'", date("Y-m-d H:i:s", time()));
            return $env_id;
        } else {
            return FALSE;
        }
    }

    /**
     * 删除环境
     * @param $project_id int 项目的数字ID
     * @param $env_id int 环境的数字ID
     * @return bool
     */
    public function deleteEnv(&$project_id, &$env_id)
    {
        $env_dao = new EnvDao;
        $projectDao = new ProjectDao;
        if ($projectDao->checkProjectPermission($project_id, $_SESSION['userID'])) {
            if (!$env_dao->checkEnvPermission($env_id, $_SESSION['userID'])) {
                return FALSE;
            }
            $env_name = $env_dao->getEnvName($env_id);
            if ($env_dao->deleteEnv($project_id, $env_id)) {
                //将操作写入日志
                $log_dao = new \ProjectLogDao();
                $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_ENVIRONMENT, $env_id, ProjectLogDao::$OP_TYPE_DELETE, "删除环境:'$env_name'", date("Y-m-d H:i:s", time()));

                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * 修改环境
     * @param $env_id int 环境的数字ID
     * @param $env_name string 环境名称
     * @param $front_uri string 前置URI
     * @param $headers array 请求头部
     * @param $params array 全局变量
     * @param $apply_protocol int 应用的请求类型,[-1]=>[所有请求类型]
     * @param $additional_params array 额外参数
     * @return bool
     */
    public function editEnv(&$env_id, &$env_name, &$front_uri, &$headers, &$params, $apply_protocol, &$additional_params)
    {
        $env_dao = new EnvDao;
        if (!($project_id = $env_dao->checkEnvPermission($env_id, $_SESSION['userID']))) {
            return FALSE;
        }
        if ($env_dao->editEnv($env_id, $env_name, $front_uri, $headers, $params, $apply_protocol, $additional_params)) {
            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_ENVIRONMENT, $project_id, ProjectLogDao::$OP_TYPE_UPDATE, "修改环境:'{$env_name}'", date("Y-m-d H:i:s", time()));

            return TRUE;
        } else {
            return FALSE;
        }
    }
}
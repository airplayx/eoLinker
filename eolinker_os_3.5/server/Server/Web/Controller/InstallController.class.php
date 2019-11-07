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
class InstallController
{
    //返回Json类型
    private $returnJson = array('type' => 'install');

    /**
     * 检测环境
     */
    public function checkoutEnv()
    {
        //获取必要的信息
        $dbURL = quickInput('dbURL');
        $dbName = quickInput('dbName');
        $dbUser = quickInput('dbUser');
        $dbPassword = quickInput('dbPassword');
        $server = new InstallModule;
        $result = $server->checkoutEnv($dbURL, $dbName, $dbUser, $dbPassword);
        if (isset($result['error'])) {
            $this->returnJson['statusCode'] = '200004';
            $this->returnJson['error'] = $result['error'];
        } else {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['envStatus'] = $result;
        }
        exitOutput($this->returnJson);
    }

    /**
     * 检查配置
     */
    public function checkConfig()
    {
        if (!file_exists(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php')) {
            //强化判断，是否已经定义了数据库的地址信息
            if (@!defined(DB_URL))
                //不存在配置文件，需要跳转至引导页面进行安装
                $this->returnJson['statusCode'] = '200003';
        } else {
            $this->returnJson['statusCode'] = '000000';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 安装eolinker
     */
    public function start()
    {
        ini_set("max_execution_time", 0);
        //检查是否已经存在配置文件或者是否可以获取到数据库地址
        if (file_exists(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php') || defined(DB_URL)) {
            //直接返回成功
            $this->returnJson['statusCode'] = '000000';
            exitOutput($this->returnJson);
        } elseif (!file_exists(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php')) {
            //获取必要的信息
            $dbURL = quickInput('dbURL');
            $dbName = quickInput('dbName');
            $dbUser = quickInput('dbUser');
            $dbPassword = quickInput('dbPassword');
            $websiteName = quickInput('websiteName');
            $language = quickInput('language');
            if (empty($language)) {
                $language = 'zh-cn';
            }
            if (empty($dbURL) || empty($dbName) || empty($dbUser)) {
                $this->returnJson['statusCode'] = '200003';
                exitOutput($this->returnJson);
            }
            $server = new InstallModule;
            if ($server->createConfigFile($dbURL, $dbName, $dbUser, $dbPassword, $websiteName, $language)) {
                //写入成功
                quickRequire(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php');
                if ($server->installDatabase()) {
                    $this->returnJson['statusCode'] = '000000';
                    @session_start();
                    @session_destroy();
                } else {
                    //创建数据库失败，确认是否拥有数据库操作权限
                    $this->returnJson['statusCode'] = '200002';
                    unlink(realpath(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php'));
                }
            } else {
                //写入失败，确认是否拥有文件操作权限
                $this->returnJson['statusCode'] = '200001';
                unlink(realpath(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php'));
            }
            exitOutput($this->returnJson);
        }
    }

}

?>
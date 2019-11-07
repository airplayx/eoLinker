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
class UpdateController
{
    //返回Json类型
    private $returnJson = array('type' => 'update');

    /**
     * 检查是否有更新
     */
    public function checkUpdate()
    {
        if (ALLOW_UPDATE) {
            $server = new ProxyModule;
            $updateInfo = $server->proxyToDesURL('GET', 'https://api.eolinker.com/openSource/Update/checkout');
            $result = json_decode($updateInfo['testResult']['body'], TRUE);
            if ($result) {
                if (OS_VERSION_CODE < $result['versionCode']) {
                    $this->returnJson['statusCode'] = '000000';
                    if (!is_session_started()) {
                        session_start();
                    }
                    if (is_session_started()) {
                        session_destroy();
                    }
                } else {
                    $this->returnJson['statusCode'] = '320002';
                }
            } else {
                $this->returnJson['statusCode'] = '320001';
            }
            exitOutput($this->returnJson);
        } else {
            //更新已被禁用
            $this->returnJson['statusCode'] = '320004';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 自动更新项目
     */
    public function autoUpdate()
    {
        ini_set("max_execution_time", 0);
        if (ALLOW_UPDATE) {
            try {

                $proxyServer = new ProxyModule;
                $updateInfo = $proxyServer->proxyToDesURL('GET', 'https://api.eolinker.com/openSource/Update/checkout');
                $result = json_decode($updateInfo['testResult']['body'], TRUE);
                if ($result) {
                    if (OS_VERSION_CODE < $result['versionCode']) {
                        $updateServer = new UpdateModule;
                        if ($updateServer->autoUpdate($result['updateUrl'])) {
                            $this->returnJson['statusCode'] = '000000';
                            if (!is_session_started()) {
                                session_start();
                            }
                            if (is_session_started()) {
                                session_destroy();
                            }
                        } else {
                            //更新失败
                            $this->returnJson['statusCode'] = '320003';
                        }
                    } else {
                        //已是最新版本，无需更新
                        $this->returnJson['statusCode'] = '320002';
                    }
                } else {
                    //无法获取更新信息(可能断网等)
                    $this->returnJson['statusCode'] = '320001';
                }
            } catch (Exception $e) {
                //更新失败
                $this->returnJson['statusCode'] = '320003';
                $this->returnJson['errorMsg'] = $e->getMessage();
            }
        } else {
            //更新已被禁用
            $this->returnJson['statusCode'] = '320004';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 手动更新项目
     */
    public function manualUpdate()
    {
        ini_set("max_execution_time", 0);
        if (ALLOW_UPDATE) {
            try {
                $updateServer = new UpdateModule;
                if ($updateServer->manualUpdate()) {
                    $this->returnJson['statusCode'] = '000000';
                    if (!is_session_started()) {
                        session_start();
                    }
                    if (is_session_started()) {
                        session_destroy();
                    }
                } else {
                    //更新失败
                    $this->returnJson['statusCode'] = '320003';
                }
            } catch (\Exception $e) {
                $this->returnJson['statusCode'] = '320003';
                $this->returnJson['errorMsg'] = $e->getMessage();
            }
        } else {
            //更新已被禁用
            $this->returnJson['statusCode'] = '320004';
        }
        exitOutput($this->returnJson);
    }

}

?>

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
class MockController
{

    /**
     * 返回示例结果(简易mock)
     */
    public function simple()
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST,GET,PUT,DELETE,PATCH,OPTIONS');
        header('Access-Control-Allow-Headers:x-requested-with,content-type,x-custom-header,Accept,Authorization,other_header,x-csrf-token');
        header("Content-type: text/html; charset=UTF-8");

        $project_id = $_GET['projectID'];
        $result_type = $_GET['resultType'] ? $_GET['resultType'] : 'success';
        $type = array(
            'POST' => '0',
            'GET' => '1',
            'PUT' => '2',
            'DELETE' => '3',
            'HEAD' => '4',
            'OPTIONS' => '5',
            'PATCH' => '6'
        );
        $request_type = $type[$_SERVER['REQUEST_METHOD']];
        $api_uri = $_GET['uri'];

        $service = new MockModule();
        switch ($result_type) {
            case 'success':
                {
                    $result = $service->success($project_id, $api_uri, $request_type);
                    break;
                }
            case 'failure':
                {
                    $result = $service->failure($project_id, $api_uri, $request_type);
                    break;
                }
            default:
                {
                    exit('error result type.');
                }
        }
        if ($result) {
            exit($result);
        } else {
            exit('sorry,this api without the mock data.');
        }
    }

    /**
     * 获取高级mock结果
     */
    public function mock()
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST,GET,PUT,DELETE,PATCH,OPTIONS');
        header('Access-Control-Allow-Headers:x-requested-with,content-type,x-custom-header,Accept,Authorization,other_header,x-csrf-token');
        header("Content-type: application/json; charset=UTF-8");

        $project_id = $_GET['projectID'];
        $type = array(
            'POST' => '0',
            'GET' => '1',
            'PUT' => '2',
            'DELETE' => '3',
            'HEAD' => '4',
            'OPTIONS' => '5',
            'PATCH' => '6'
        );
        $request_type = $type[$_SERVER['REQUEST_METHOD']];
        $api_uri = $_GET['uri'];

        $module = new MockModule();
        $result = $module->getMockResult($project_id, $api_uri, $request_type);
        if ($result) {
            $decoded_result = htmlspecialchars_decode($result);
            if ($decoded_result) {
                exit($decoded_result);
            }
            exit($result);
        } else {
            exit('sorry,this api without the mock data.');
        }
    }
}
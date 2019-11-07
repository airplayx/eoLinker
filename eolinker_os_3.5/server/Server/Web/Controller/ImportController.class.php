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
class ImportController
{
    // 返回json类型
    private $returnJson = array('type' => 'import');

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
     * 导入eoapi数据
     */
    public function importEoapi()
    {
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        if (!$data) {
            $this->returnJson['statusCode'] = '310004';
            exitOutput($this->returnJson);
        }
        $server = new ImportModule;
        $result = $server->eoapiImport($data);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '310005';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 导入DHC数据
     */
    public function importDHC()
    {
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        if (!$data) {
            $this->returnJson['statusCode'] = '310004';
            exitOutput($this->returnJson);
        }
        $server = new ImportModule;
        $result = $server->importDHC($data);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '310001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 导入postman数据
     */
    public function importPostman()
    {
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        $version = securelyInput('version');
        if (!$data) {
            $this->returnJson['statusCode'] = '310004';
            exitOutput($this->returnJson);
        } elseif ($version != 1 and $version != 2) {
            $this->returnJson['statusCode'] = '310002';
            exitOutput($this->returnJson);
        }
        $server = new ImportModule;
        if ($version == 1) {
            $result = $server->importPostmanV1($data);
        } else {
            $result = $server->importPostmanV2($data);
        }
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '310003';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 导入swagger数据
     */
    public function importSwagger()
    {
        $data = quickInput('data');
        $json = json_decode($data, TRUE);
        if (empty($json)) {
            $this->returnJson['statusCode'] = '310004';
            exitOutput($this->returnJson);
        }
        $server = new ImportModule;
        $result = $server->importSwagger($data);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '310001';
        }
        exitOutput($this->returnJson);
    }

    /**
     *导入RAP
     */
    public function importRAP()
    {
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        //判断数据是否为空
        if (empty($data['modelJSON'])) {
            $this->returnJson['statusCode'] = '310001';
            exitOutput($this->returnJson);
        }
        $model_json = json_decode(str_replace("\'", "'", $data['modelJSON']), TRUE);
        //以json格式解析modelJSON失败
        if (empty($model_json)) {
            $this->returnJson['statusCode'] = '310003';
            exitOutput($this->returnJson);
        }
        $server = new ImportModule();
        $result = $server->importRAP($model_json);
        //验证结果
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '310000';
        }
        exitOutput($this->returnJson);
    }
}

?>
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
class MockDao
{
    /**
     * 获取api的成功示例数据
     */
    public function getSuccessResult(&$project_id, &$api_uri, &$request_type)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT eo_api.apiSuccessMock FROM eo_api WHERE eo_api.projectID = ? AND eo_api.apiURI = ? AND eo_api.apiRequestType = ? AND eo_api.removed = 0 ORDER BY eo_api.apiUpdateTime DESC;", array(
            $project_id,
            $api_uri,
            $request_type
        ));

        if (empty($result)) {
            $result = $this->getRestfulMock($project_id, $api_uri, $request_type);
            if ($result) {
                return $result['apiSuccessMock'];
            } else
                return FALSE;
        } else
            return $result['apiSuccessMock'];
    }

    /**
     * 获取api的失败数据
     */
    public function getFailureResult(&$project_id, &$api_uri, &$request_type)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT eo_api.apiFailureMock FROM eo_api WHERE eo_api.projectID = ? AND eo_api.apiURI = ? AND eo_api.apiRequestType = ?  AND eo_api.removed = 0 ORDER BY eo_api.apiUpdateTime DESC;", array(
            $project_id,
            $api_uri,
            $request_type
        ));

        if (empty($result)) {
            $result = $this->getRestfulMock($project_id, $api_uri, $request_type);
            if ($result) {
                return $result['apiFailureMock'];
            } else
                return FALSE;
        } else
            return $result['apiFailureMock'];
    }

    /**
     * 获取高级mock结果
     * @param $project_id
     * @param $api_uri
     * @param $request_type
     * @return bool
     */
    public function getMockResult(&$project_id, &$api_uri, &$request_type)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_api.mockResult FROM eo_api WHERE eo_api.projectID = ? AND eo_api.apiURI = ? AND eo_api.apiRequestType = ? AND eo_api.removed = 0 ORDER BY eo_api.apiUpdateTime DESC;', array(
            $project_id,
            $api_uri,
            $request_type
        ));
        if (empty($result)) {
            $result = $this->getRestfulMock($project_id, $api_uri, $request_type);
            if ($result) {
                return $result['mockResult'];
            } else {
                return FALSE;
            }
        } else {
            return $result['mockResult'];
        }
    }

    /**
     * 获取restful的mock数据
     */
    public function getRestfulMock(&$project_id, &$api_uri, &$request_type)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT eo_api.apiURI,eo_api.apiID,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.mockResult FROM eo_api WHERE eo_api.projectID = ?  AND eo_api.removed = 0 AND eo_api.apiRequestType = ? ORDER BY eo_api.apiUpdateTime DESC;', array(
            $project_id,
            $request_type
        ));
        if (empty($result)) {
            return FALSE;
        } else {
            foreach ($result as $param) {

                $msg = preg_replace('/\{[^\/]+\}/', '[^/]+', $param['apiURI']);
                $msg = str_replace("amp;", "", $msg);
                $msg = preg_replace('/:[^\/]+/', '[^/]+', $msg);
                $msg = preg_replace('/\//', '\/', $msg);
                $msg = preg_replace("/\?/", '\?', $msg);
                $msg = '/^' . $msg . '$/';
                $api_uri = str_replace("amp;", "", $api_uri);
                if (preg_match($msg, $api_uri)) {
                    return $param;
                }
            }
            return FALSE;
        }
    }
}
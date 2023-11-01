<?php
/**
 * @name eolinker open source，eolinker开源版本
 * @link https://www.eolinker.com
 * @package eolinker
 * @author www.eolinker.com 广州银云信息科技有限公司 2015-2018

 * eolinker，业内领先的Api接口管理及测试平台，为您提供最专业便捷的在线接口管理、测试、维护以及各类性能测试方案，帮助您高效开发、安全协作。
 * 如在使用的过程中有任何问题，可通过http://help.eolinker.com寻求帮助
 *
 * 注意！eolinker开源版本遵循GPL V3开源协议，仅供用户下载试用，禁止“一切公开使用于商业用途”或者“以eoLinker开源版本为基础而开发的二次版本”在互联网上流通。
 * 注意！一经发现，我们将立刻启用法律程序进行维权。
 * 再次感谢您的使用，希望我们能够共同维护国内的互联网开源文明和正常商业秩序。
 *
 */

class MockModule
{
    /**
     * 获取成功结果示例
     * @param $project_id
     * @param $api_uri
     * @param $request_type
     * @return bool
     */
    public function success(&$project_id, &$api_uri, &$request_type)
    {
        $dao = new MockDao();
        return $dao->getSuccessResult($project_id, $api_uri, $request_type);
    }

    /**
     * 获取失败结果示例
     * @param $project_id
     * @param $api_uri
     * @param $request_type
     * @return bool
     */
    public function failure(&$project_id, &$api_uri, &$request_type)
    {
        $dao = new MockDao();
        return $dao->getFailureResult($project_id, $api_uri, $request_type);
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
        $dao = new MockDao();
        return $dao->getMockResult($project_id, $api_uri, $request_type);
    }

    /**
     * 保存简易mock
     * @param $project_id
     * @param $api_id
     * @param $mock_type
     * @param $mock_data
     * @param $status_code
     * @return bool
     */
    public function saveSimpleMock(&$project_id, &$api_id, &$mock_type, &$mock_data, &$status_code)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($apiDao->checkApiPermission($api_id, $_SESSION['userID'])) {
            if ($project_id = $apiDao->checkApiPermission($api_id, $_SESSION['userID'])) {
                $projectDao->updateProjectUpdateTime($project_id);
                $dao = new MockDao();
                return $dao->saveSimpleMock($project_id, $api_id, $_SESSION['userID'], $mock_type, $mock_data, $status_code);
            }else {
               return FALSE;
            }
        } else
            return FALSE;
    }


}
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

class MockDao
{
    /**
     * 获取api的成功示例数据
     */
    public function getSuccessResult(&$project_id, &$api_uri, &$request_type)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT eo_ams_api.apiSuccessMock FROM eo_ams_api WHERE eo_ams_api.projectID = ? AND eo_ams_api.apiURI = ? AND eo_ams_api.apiRequestType = ? AND eo_ams_api.removed = 0 ORDER BY eo_ams_api.apiUpdateTime DESC;", array(
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
        $result = $db->prepareExecute("SELECT eo_ams_api.apiFailureMock FROM eo_ams_api WHERE eo_ams_api.projectID = ? AND eo_ams_api.apiURI = ? AND eo_ams_api.apiRequestType = ?  AND eo_ams_api.removed = 0 ORDER BY eo_ams_api.apiUpdateTime DESC;", array(
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
        $result = $db->prepareExecute('SELECT eo_ams_api.mockResult FROM eo_ams_api WHERE eo_ams_api.projectID = ? AND eo_ams_api.apiURI = ? AND eo_ams_api.apiRequestType = ? AND eo_ams_api.removed = 0 ORDER BY eo_ams_api.apiUpdateTime DESC;', array(
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
        $result = $db->prepareExecuteAll('SELECT eo_ams_api.apiURI,eo_ams_api.apiID,eo_ams_api.apiSuccessMock,eo_ams_api.apiFailureMock,eo_ams_api.mockResult FROM eo_ams_api WHERE eo_ams_api.projectID = ?  AND eo_ams_api.removed = 0 AND eo_ams_api.apiRequestType = ? ORDER BY eo_ams_api.apiUpdateTime DESC;', array(
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

        /**
         * 保存简易mock
         */
        public function saveSimpleMock(&$project_id, &$api_id, &$user_id, &$mock_type, &$mock_data, &$status_code)
        {
        	$db = getDatabase();
        	try
        	{
        		if($mock_type == 0)
        		{
        			$db->prepareExecute("UPDATE eo_ams_api SET apiSuccessMock  = ?, updateUserID = ?, apiUpdateTime = ?,apiSuccessStatusCode = ? WHERE apiID  = ? AND projectID = ?", Array(
        					$mock_data,
        					$user_id,
        					date("Y-m-d H:i:s", time()),
        					$status_code,
        					$api_id,
        					$project_id
        					));
        			$api = $db->prepareExecute("SELECT cacheID,apiJson FROM eo_ams_api_cache WHERE apiID = ?", array($api_id));
        			if($api)
        			{
        				$api_json = json_decode($api['apiJson'], TRUE);
        				$api_json['baseInfo']['apiSuccessMock'] = $mock_data;
        				$api_json['baseInfo']['apiSuccessStatusCode'] = $status_code;
        				$api_json = Json_encode($api_json);
        				$db->prepareExecute("UPDATE eo_ams_api_cache SET apiJson = ?, updateUserID =? WHERE cacheID = ?", Array(
        						$api_json,
        						$user_id,
        						$api['cacheID']
        						));
        			}
        		}
        		else
        		{
        			$db->prepareExecute("UPDATE eo_ams_api SET apiFailureMock  = ?, updateUserID = ?, apiUpdateTime = ?,apiSuccessStatusCode = ? WHERE apiID  = ? AND projectID = ?", Array(
        					$mock_data,
        					$user_id,
        					date("Y-m-d H:i:s", time()),
        					$status_code,
        					$api_id,
        					$project_id
        					));
        			$api = $db->prepareExecute("SELECT cacheID,apiJson FROM eo_ams_api_cache WHERE apiID = ?", array($api_id));
        			if($api)
        			{
        				$api_json = json_decode($api['apiJson'], TRUE);
        				$api_json['baseInfo']['apiFailureMock'] = $mock_data;
        				$api_json['baseInfo']['apiFailureStatusCode'] = $status_code;
        				$api_json = Json_encode($api_json);
        				$db->prepareExecute("UPDATE eo_ams_api_cache SET apiJson = ?, updateUserID =? WHERE cacheID = ?", Array(
        						$api_json,
        						$user_id,
        						$api['cacheID']
        						));
        			}
        		}
        		return TRUE;
        	}
        	catch(\PDOException $e)
        	{
        		return FALSE;
        	}
        }
}
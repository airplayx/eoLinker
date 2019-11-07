<?php
/**
 * @name EOLINKER ams open source，EOLINKER open source version
 * @link https://global.eolinker.com/
 * @package EOLINKER
 * @author www.eolinker.com eoLinker Ltd.co 2015-2018
 * 
 * eoLinker is the world's leading and domestic largest online API interface management platform, providing functions such as automatic generation of API documents, API automated testing, Mock testing, team collaboration, etc., aiming to solve the problem of low development efficiency caused by separation of front and rear ends.
 * If you have any problems during the process of use, please join the user discussion group for feedback, we will solve the problem for you with the fastest speed and best service attitude.
 *
 * 
 *
 * Website：https://global.eolinker.com/
 * Slack：eolinker.slack.com
 * facebook：@EoLinker
 * twitter：@eoLinker
 */

class TestController
{
    //Return Json Type
    private $returnJson = array('type' => 'test');

    /**
     * Check Login
     */
    public function __construct()
    {
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * get Test
     */
    public function get()
    {
        $method = 'GET';
        $apiProtocol = quickInput('apiProtocol');
        $URL = quickInput('URL');
        $headers = json_decode(quickInput('headers'), TRUE);
        $requestParam = json_decode(quickInput('params'), TRUE);
        $apiID = securelyInput('apiID');

        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
            
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        }

        if ($headers) {
            
            foreach ($headers as $name => $value) {
                $requestHeader[] = $name . ': ' . $value;
                $requestHeaderInfo[] = array(
                    'name' => $name,
                    'value' => $value
                );
            }
        }

        if ($requestParam) {
            //			foreach ($requestParam as $key => $value)
            //			{
            //				$arr[] = $key . '=' . $value;
            //			}
            //			$URL = $URL . '?' . join('&', $arr);
            $URL = $URL . '?' . http_build_query($requestParam);

        }

        if ($apiProtocol == 0) {
            $completeURL = 'http://' . $URL;
        } else {
            $completeURL = 'https://' . $URL;
        }

       
        if (!$completeURL || !filter_var($completeURL, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_QUERY_REQUIRED)) {
            $this->returnJson['statusCode'] = '210001';
            exitOutput($this->returnJson);
        }

        $service = new ProxyModule;
        $result = $service->proxyToDesURL($method, $completeURL, $requestHeader);

        if ($result) {
            $requestInfo = json_encode(array(
                'apiProtocol' => $apiProtocol,
                'method' => $method,
                'URL' => $URL,
                'headers' => $requestHeaderInfo ? $requestHeaderInfo : array(),
                'requestType' => 0,
                'params' => $requestParamInfo ? $requestParamInfo : array()
            ));
            $resultInfo = json_encode(array(
                'headers' => $result['testResult']['headers'],
                'body' => $result['testResult']['body'],
                'httpCode' => $result['testHttpCode'],
                'testDeny' => $result['testDeny']
            ));
            $testTime = $result['testTime'];
            $server = new TestHistoryModule;
            $testID = $server->addTestHistory($apiID, $requestInfo, $resultInfo, $testTime);
            if ($testID) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testHttpCode'] = $result['testHttpCode'];
                $this->returnJson['testResult'] = $result['testResult'];
                $this->returnJson['testDeny'] = $result['testDeny'];
                $this->returnJson['testID'] = $testID;
            } else {
                
                $this->returnJson['statusCode'] = '210009';
            }
        } else {
            $this->returnJson['statusCode'] = '210002';
        }
        exitOutput($this->returnJson);

    }

    /**
     * post test
     */
    public function post()
    {
        $method = 'POST';
        $apiProtocol = quickInput('apiProtocol');
        $URL = quickInput('URL');
        $headers = json_decode(quickInput('headers'), TRUE);
        $apiID = securelyInput('apiID');
        $requestType = quickInput('requestType');
        switch ($requestType) {
            case 0 :
                {
                    $param = json_decode(quickInput('params'), TRUE);
                    foreach ($param as $key => $value) {
                        $requestParamInfo[] = array(
                            'key' => $key,
                            'value' => $value
                        );
                    }
                    break;
                }
            case 1 :
                {
                    $param = quickInput('params');
                    break;
                }
            default :
                {
                    
                    $this->returnJson['statusCode'] = '210013';
                    exitOutput($this->returnJson);
                }
        }

        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
            
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        }

        if ($headers) {
            
            foreach ($headers as $name => $value) {
                $requestHeader[] = $name . ': ' . $value;
                $requestHeaderInfo[] = array(
                    'name' => $name,
                    'value' => $value
                );
            }
        }

        if ($apiProtocol == 0) {
            $completeURL = 'http://' . $URL;
        } else {
            $completeURL = 'https://' . $URL;
        }

        
        if (!$completeURL || !filter_var($completeURL, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_QUERY_REQUIRED)) {
            $this->returnJson['statusCode'] = '210001';
            exitOutput($this->returnJson);
        }

        $service = new ProxyModule;
        $result = $service->proxyToDesURL($method, $completeURL, $requestHeader, $param);

        if ($result) {
            
            if ($requestType == 0) {
                
                $requestParam = $requestParamInfo ? $requestParamInfo : array();
            } else {
               
                $requestParam = $param;
            }

            $requestInfo = json_encode(array(
                'apiProtocol' => $apiProtocol,
                'method' => $method,
                'URL' => $URL,
                'headers' => $requestHeaderInfo ? $requestHeaderInfo : array(),
                'requestType' => $requestType,
                'params' => $requestParam
            ));
            $resultInfo = json_encode(array(
                'headers' => $result['testResult']['headers'],
                'body' => $result['testResult']['body'],
                'httpCode' => $result['testHttpCode'],
                'testDeny' => $result['testDeny']
            ));
            $testTime = $result['testTime'];
            $server = new TestHistoryModule;
            $testID = $server->addTestHistory($apiID, $requestInfo, $resultInfo, $testTime);
            if ($testID) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testHttpCode'] = $result['testHttpCode'];
                $this->returnJson['testResult'] = $result['testResult'];
                $this->returnJson['testDeny'] = $result['testDeny'];
                $this->returnJson['testID'] = $testID;
            } else {
                
                $this->returnJson['statusCode'] = '210009';
            }
        } else {
            $this->returnJson['statusCode'] = '210003';
        }
        exitOutput($this->returnJson);
    }

    /**
     * delete Test
     */
    public function delete()
    {
        $method = 'DELETE';
        $apiProtocol = quickInput('apiProtocol');
        $URL = quickInput('URL');
        $headers = json_decode(quickInput('headers'), TRUE);
        $requestParam = json_decode(quickInput('params'), TRUE);
        $apiID = securelyInput('apiID');

        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        }

        if ($headers) {
            foreach ($headers as $name => $value) {
                $requestHeader[] = $name . ': ' . $value;
                $requestHeaderInfo[] = array(
                    'name' => $name,
                    'value' => $value
                );
            }
        }

        if ($apiProtocol == 0) {
            $completeURL = 'http://' . $URL;
        } else {
            $completeURL = 'https://' . $URL;
        }

        
        if (!$completeURL || !filter_var($completeURL, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_QUERY_REQUIRED)) {
            $this->returnJson['statusCode'] = '210001';
            exitOutput($this->returnJson);
        }

        $service = new ProxyModule;
        $result = $service->proxyToDesURL($method, $completeURL, $requestHeader, $requestParam);

        if ($result) {
            $requestInfo = json_encode(array(
                'apiProtocol' => $apiProtocol,
                'method' => $method,
                'URL' => $URL,
                'headers' => $requestHeaderInfo ? $requestHeaderInfo : array(),
                'requestType' => 0,
                'params' => $requestParamInfo ? $requestParamInfo : array()
            ));
            $resultInfo = json_encode(array(
                'headers' => $result['testResult']['headers'],
                'body' => $result['testResult']['body'],
                'httpCode' => $result['testHttpCode'],
                'testDeny' => $result['testDeny']
            ));
            $testTime = $result['testTime'];
            $server = new TestHistoryModule;
            $testID = $server->addTestHistory($apiID, $requestInfo, $resultInfo, $testTime);
            if ($testID) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testHttpCode'] = $result['testHttpCode'];
                $this->returnJson['testResult'] = $result['testResult'];
                $this->returnJson['testDeny'] = $result['testDeny'];
                $this->returnJson['testID'] = $testID;
            } else {
                
                $this->returnJson['statusCode'] = '210009';
            }
        } else {
            $this->returnJson['statusCode'] = '210004';
        }
        exitOutput($this->returnJson);
    }

    /**
     * head Test
     */
    public function head()
    {
        $method = 'HEAD';
        $apiProtocol = quickInput('apiProtocol');
        $URL = quickInput('URL');
        $headers = json_decode(quickInput('headers'), TRUE);
        $requestParam = json_decode(quickInput('params'), TRUE);
        $apiID = securelyInput('apiID');

        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
            
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        }

        if ($headers) {
           
            foreach ($headers as $name => $value) {
                $requestHeader[] = $name . ': ' . $value;
                $requestHeaderInfo[] = array(
                    'name' => $name,
                    'value' => $value
                );
            }
        }
        if ($requestParam) {
            foreach ($requestParam as $key => $value) {
                $requestParamInfo[] = array(
                    'key' => $key,
                    'value' => $value
                );
            }
        }

        if ($apiProtocol == 0) {
            $completeURL = 'http://' . $URL;
        } else {
            $completeURL = 'https://' . $URL;
        }

        
        if (!$completeURL || !filter_var($completeURL, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_QUERY_REQUIRED)) {
            $this->returnJson['statusCode'] = '210001';
            exitOutput($this->returnJson);
        }

        $service = new ProxyModule;
        $result = $service->proxyToDesURL($method, $completeURL, $requestHeader, $requestParam);

        if ($result) {
            $requestInfo = json_encode(array(
                'apiProtocol' => $apiProtocol,
                'method' => $method,
                'URL' => $URL,
                'headers' => $requestHeaderInfo ? $requestHeaderInfo : array(),
                'requestType' => 0,
                'params' => $requestParamInfo ? $requestParamInfo : array()
            ));
            $resultInfo = json_encode(array(
                'headers' => $result['testResult']['headers'],
                'body' => $result['testResult']['body'],
                'httpCode' => $result['testHttpCode'],
                'testDeny' => $result['testDeny']
            ));
            $testTime = $result['testTime'];
            $server = new TestHistoryModule;
            $testID = $server->addTestHistory($apiID, $requestInfo, $resultInfo, $testTime);
            if ($testID) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testHttpCode'] = $result['testHttpCode'];
                $this->returnJson['testResult'] = $result['testResult'];
                $this->returnJson['testDeny'] = $result['testDeny'];
                $this->returnJson['testID'] = $testID;
            } else {
               
                $this->returnJson['statusCode'] = '210009';
            }
        } else {
            $this->returnJson['statusCode'] = '210005';
        }
        exitOutput($this->returnJson);
    }

    /**
     * options Test
     */
    public function options()
    {
        $method = 'OPTIONS';
        $apiProtocol = quickInput('apiProtocol');
        $URL = quickInput('URL');
        $headers = json_decode(quickInput('headers'), TRUE);
        $requestParam = json_decode(quickInput('params'), TRUE);
        $apiID = securelyInput('apiID');

        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
            
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        }

        if ($headers) {
            
            foreach ($headers as $name => $value) {
                $requestHeader[] = $name . ': ' . $value;
                $requestHeaderInfo[] = array(
                    'name' => $name,
                    'value' => $value
                );
            }
        }
        if ($requestParam) {
            foreach ($requestParam as $key => $value) {
                $requestParamInfo[] = array(
                    'key' => $key,
                    'value' => $value
                );
            }
        }

        if ($apiProtocol == 0) {
            $completeURL = 'http://' . $URL;
        } else {
            $completeURL = 'https://' . $URL;
        }

        
        if (!$completeURL || !filter_var($completeURL, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_QUERY_REQUIRED)) {
            $this->returnJson['statusCode'] = '210001';
            exitOutput($this->returnJson);
        }

        $service = new ProxyModule;
        $result = $service->proxyToDesURL($method, $completeURL, $requestHeader, $requestParam);

        if ($result) {
            $requestInfo = json_encode(array(
                'apiProtocol' => $apiProtocol,
                'method' => $method,
                'URL' => $URL,
                'headers' => $requestHeaderInfo ? $requestHeaderInfo : array(),
                'requestType' => 0,
                'params' => $requestParamInfo ? $requestParamInfo : array()
            ));
            $resultInfo = json_encode(array(
                'headers' => $result['testResult']['headers'],
                'body' => $result['testResult']['body'],
                'httpCode' => $result['testHttpCode'],
                'testDeny' => $result['testDeny']
            ));
            $testTime = $result['testTime'];
            $server = new TestHistoryModule;
            $testID = $server->addTestHistory($apiID, $requestInfo, $resultInfo, $testTime);
            if ($testID) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testHttpCode'] = $result['testHttpCode'];
                $this->returnJson['testResult'] = $result['testResult'];
                $this->returnJson['testDeny'] = $result['testDeny'];
                $this->returnJson['testID'] = $testID;
            } else {
                
                $this->returnJson['statusCode'] = '210009';
            }
        } else {
            $this->returnJson['statusCode'] = '210006';
        }
        exitOutput($this->returnJson);
    }

    /**
     * patch Test
     */
    public function patch()
    {
        $method = 'PATCH';
        $apiProtocol = quickInput('apiProtocol');
        $URL = quickInput('URL');
        $headers = json_decode(quickInput('headers'), TRUE);
        $requestParam = json_decode(quickInput('params'), TRUE);
        $apiID = securelyInput('apiID');

        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
            
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        }

        if ($headers) {
            
            foreach ($headers as $name => $value) {
                $requestHeader[] = $name . ': ' . $value;
                $requestHeaderInfo[] = array(
                    'name' => $name,
                    'value' => $value
                );
            }
        }
        if ($requestParam) {
            foreach ($requestParam as $key => $value) {
                $requestParamInfo[] = array(
                    'key' => $key,
                    'value' => $value
                );
            }
        }

        if ($apiProtocol == 0) {
            $completeURL = 'http://' . $URL;
        } else {
            $completeURL = 'https://' . $URL;
        }

        
        if (!$completeURL || !filter_var($completeURL, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_QUERY_REQUIRED)) {
            $this->returnJson['statusCode'] = '210001';
            exitOutput($this->returnJson);
        }

        $service = new ProxyModule;
        $result = $service->proxyToDesURL($method, $completeURL, $requestHeader, $requestParam);

        if ($result) {
            $requestInfo = json_encode(array(
                'apiProtocol' => $apiProtocol,
                'method' => $method,
                'URL' => $URL,
                'headers' => $requestHeaderInfo ? $requestHeaderInfo : array(),
                'requestType' => 0,
                'params' => $requestParamInfo ? $requestParamInfo : array()
            ));
            $resultInfo = json_encode(array(
                'headers' => $result['testResult']['headers'],
                'body' => $result['testResult']['body'],
                'httpCode' => $result['testHttpCode'],
                'testDeny' => $result['testDeny']
            ));
            $testTime = $result['testTime'];
            $server = new TestHistoryModule;
            $testID = $server->addTestHistory($apiID, $requestInfo, $resultInfo, $testTime);
            if ($testID) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testHttpCode'] = $result['testHttpCode'];
                $this->returnJson['testResult'] = $result['testResult'];
                $this->returnJson['testDeny'] = $result['testDeny'];
                $this->returnJson['testID'] = $testID;
            } else {
                
                $this->returnJson['statusCode'] = '210009';
            }
        } else {
            $this->returnJson['statusCode'] = '210007';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Delete Test History
     */
    public function deleteTestHistory()
    {
        $testID = securelyInput('testID');

        if (!preg_match('/^[0-9]{1,11}$/', $testID)) {
            
            $this->returnJson['statusCode'] = '210010';
        } else {
            $service = new TestHistoryModule;
            $result = $service->deleteTestHistory($testID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                
                $this->returnJson['statusCode'] = '210011';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get Test History
     */
    public function getTestHistory()
    {
        $testID = securelyInput('testID');

        if (!preg_match('/^[0-9]{1,11}$/', $testID)) {
           
            $this->returnJson['statusCode'] = '210010';
        } else {
            $service = new TestHistoryModule;
            $result = $service->getTestHistory($testID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['projectID'] = $result['projectID'];
                $this->returnJson['apiID'] = $result['apiID'];
                $this->returnJson['testID'] = $result['testID'];
                $this->returnJson['requestInfo'] = json_decode($result['requestInfo'], TRUE);
                $this->returnJson['resultInfo'] = json_decode($result['resultInfo'], TRUE);
                $this->returnJson['testTime'] = $result['testTime'];
            } else {
                $this->returnJson['statusCode'] = '210012';
            }
        }
        exitOutput($this->returnJson);
    }
    /**
     * 获取测试记录
     */
    public function getTestHistoryList()
    {
    	//测试记录ID
    	$api_id = securelyInput('apiID');
    	$projectID = securelyInput('projectID');
    	if (!preg_match('/^[0-9]{1,11}$/', $api_id))
    	{
    		//testID格式非法
    		$this -> returnJson['statusCode'] = '210004';
    	}
    	else
    	{
    		$service = new TestHistoryModule;
    		$result = $service -> getTestHistoryList($projectID, $api_id);
    		//验证结果
    		$this -> returnJson['statusCode'] = '000000';
    		$this -> returnJson['testHistoryList'] = $result;
    	}
    	exitOutput($this -> returnJson);
    }
    
    /**
     * put Test
     */
    public function put()
    {
        $method = 'PUT';
        $apiProtocol = quickInput('apiProtocol');
        $URL = quickInput('URL');
        $headers = json_decode(quickInput('headers'), TRUE);
        $apiID = securelyInput('apiID');
        $requestType = quickInput('requestType');
        switch ($requestType) {
            case 0 :
                {
                    $param = json_decode(quickInput('params'), TRUE);
                    foreach ($param as $key => $value) {
                        $requestParamInfo[] = array(
                            'key' => $key,
                            'value' => $value
                        );
                    }
                    break;
                }
            case 1 :
                {
                    $param = quickInput('params');
                    break;
                }
            default :
                {
                    
                    $this->returnJson['statusCode'] = '210013';
                    exitOutput($this->returnJson);
                }
        }

        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
            
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        }

        if ($headers) {
           
            foreach ($headers as $name => $value) {
                $requestHeader[] = $name . ': ' . $value;
                $requestHeaderInfo[] = array(
                    'name' => $name,
                    'value' => $value
                );
            }
        }

        if ($apiProtocol == 0) {
            $completeURL = 'http://' . $URL;
        } else {
            $completeURL = 'https://' . $URL;
        }

        
        if (!$completeURL || !filter_var($completeURL, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_QUERY_REQUIRED)) {
            $this->returnJson['statusCode'] = '210001';
            exitOutput($this->returnJson);
        }

        $service = new ProxyModule;
        $result = $service->proxyToDesURL($method, $completeURL, $requestHeader, $param);

        if ($result) {
            
            if ($requestType == 0) {
                
                $requestParam = $requestParamInfo ? $requestParamInfo : array();
            } else {
                
                $requestParam = $param;
            }

            $requestInfo = json_encode(array(
                'apiProtocol' => $apiProtocol,
                'method' => $method,
                'URL' => $URL,
                'headers' => $requestHeaderInfo ? $requestHeaderInfo : array(),
                'requestType' => $requestType,
                'params' => $requestParam
            ));
            $resultInfo = json_encode(array(
                'headers' => $result['testResult']['headers'],
                'body' => $result['testResult']['body'],
                'httpCode' => $result['testHttpCode'],
                'testDeny' => $result['testDeny']
            ));
            $testTime = $result['testTime'];
            $server = new TestHistoryModule;
            $testID = $server->addTestHistory($apiID, $requestInfo, $resultInfo, $testTime);
            if ($testID) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testHttpCode'] = $result['testHttpCode'];
                $this->returnJson['testResult'] = $result['testResult'];
                $this->returnJson['testDeny'] = $result['testDeny'];
                $this->returnJson['testID'] = $testID;
            } else {
                
                $this->returnJson['statusCode'] = '210009';
            }
        } else {
            $this->returnJson['statusCode'] = '210013';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Delete All Test History
     */
    public function deleteAllTestHistory()
    {
        $apiID = securelyInput('apiID');
        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
           
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        } else {
            $module = new TestHistoryModule();
            $result = $module->deleteAllTestHistory($apiID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '210000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Add Test History
     */
    public function addTestHistory()
    {
        $api_id = securelyInput('apiID');
        $request_info = quickInput('requestInfo');
        $result_info = quickInput('resultInfo');
        $test_time = date('Y-m-d H:i:s', time());
        if (!preg_match('/^[0-9]{1,11}$/', $api_id)) {
            $this->returnJson['statusCode'] = '210008';
        } else {
            $server = new TestHistoryModule();
            $result = $server->addTestHistory($api_id, $request_info, $result_info, $test_time);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testID'] = $result;
            } else {
                $this->returnJson['statusCode'] = '210000';
            }
        }
        exitOutput($this->returnJson);
    }
}

?>
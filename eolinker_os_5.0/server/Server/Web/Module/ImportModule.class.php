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

class ImportModule
{
    function __construct()
    {
        @session_start();
    }

    /**
     * Import eoAPI format and Export Json Data
     * @param $data string Export Json
     * @return bool
     */
    public function eoapiImport(&$data)
    {
        $dao = new ImportDao;
        return $dao->importEoapi($data, $_SESSION['userID']);
    }

    /**
     * Import DHC
     * @param $data string Export DHC data
     * @return bool
     */
    public function importDHC(&$data)
    {
        try {
            $projectInfo = array('projectName' => $data['nodes'][0]['name'], 'projectType' => 0, 'projectVersion' => 1.0);

            $groupInfoList[] = array('groupName' => 'Import DHC', 'id' => $data['nodes'][0]['id']);
            if (is_array($data['nodes'])) {
                foreach ($data['nodes'] as $element) {
                    if ($element['type'] == 'Service') {
                        $groupInfoList[] = array('groupName' => $element['name'], 'id' => $element['id']);
                    }
                }
            }

            if (is_array($groupInfoList)) {
                foreach ($groupInfoList as &$groupInfo) {
                    $apiList = array();
                    if (is_array($data['nodes'])) {
                        foreach ($data['nodes'] as $element) {
                            if ($element['type'] != 'Request' || $element['parentId'] != $groupInfo['id']) {
                                continue;
                            }

                            $apiInfo['baseInfo']['apiName'] = $element['name'];
                            $apiInfo['baseInfo']['apiURI'] = $element['uri']['path'];
                            $apiInfo['baseInfo']['apiProtocol'] = ($element['uri']['scheme']['name'] == 'http') ? 0 : 1;
                            $apiInfo['baseInfo']['apiSuccessMock'] = '';
                            $apiInfo['baseInfo']['apiFailureMock'] = '';
                            $apiInfo['baseInfo']['apiStatus'] = 0;
                            $apiInfo['baseInfo']['starred'] = 0;
                            $apiInfo['baseInfo']['apiRequestParamType'] = 0;
                            $apiInfo['baseInfo']['apiRequestRaw'] = '';
                            $apiInfo['baseInfo']['apiUpdateTime'] = date("Y-m-d H:i:s", time());
                            switch ($element['method']['name']) {
                                case 'POST' :
                                    $apiInfo['baseInfo']['apiRequestType'] = 0;
                                    break;
                                case 'GET' :
                                    $apiInfo['baseInfo']['apiRequestType'] = 1;
                                    break;
                                case 'PUT' :
                                    $apiInfo['baseInfo']['apiRequestType'] = 2;
                                    break;
                                case 'DELETE' :
                                    $apiInfo['baseInfo']['apiRequestType'] = 3;
                                    break;
                                case 'HEAD' :
                                    $apiInfo['baseInfo']['apiRequestType'] = 4;
                                    break;
                                case 'OPTIONS' :
                                    $apiInfo['baseInfo']['apiRequestType'] = 5;
                                    break;
                                case 'PATCH' :
                                    $apiInfo['baseInfo']['apiRequestType'] = 6;
                                    break;
                            }

                            $headerInfo = array();

                            if (is_array($element['headers'])) {
                                foreach ($element['headers'] as $header) {
                                    $headerInfo[] = array('headerName' => $header['name'], 'headerValue' => $header['value']);
                                }
                            }
                            $apiInfo['headerInfo'] = $headerInfo;
                            unset($headerInfo);

                            $apiRequestParam = array();
                            if ($element['method']['requestBody']) {
                                $items = $element['body']['formBody']['items'];
                                if (is_array($items)) {
                                    foreach ($items as $item) {
                                        $param['paramKey'] = $item['name'];
                                        $param['paramValue'] = $item['value'];
                                        $param['paramType'] = ($item['type'] == 'Text') ? 0 : 1;
                                        $param['paramNotNull'] = $item['enabled'] ? 0 : 1;
                                        $param['paramName'] = '';
                                        $param['paramLimit'] = '';
                                        $param['paramValueList'] = array();
                                        $apiRequestParam[] = $param;
                                        unset($param);
                                    }
                                }
                            }
                            $apiInfo['requestInfo'] = $apiRequestParam;
                            unset($apiRequestParam);
                            $apiInfo['resultInfo'] = array();

                            $apiList[] = $apiInfo;
                            unset($apiInfo);
                        }
                    }
                    $groupInfo['apiList'] = $apiList;
                    unset($apiList);
                }
            }
            $dao = new ImportDao;
            return $dao->importOther($projectInfo, $groupInfoList, $_SESSION['userID']);
        } catch (\PDOException $e) {
            return FALSE;
        }
    }

    /**
     * Import v1 Version from postman
     * @param $data string
     * @return bool
     */
    public function importPostmanV1(&$data)
    {
        try {
            $projectInfo = array('projectName' => $data['name'], 'projectType' => 0, 'projectVersion' => 1.0);

            $groupInfoList[] = array('groupName' => 'Default Group', 'folderID' => 'default');
            if (is_array($data['folders'])) {
                foreach ($data['folders'] as $folder) {
                    $groupInfoList[] = array('groupName' => $folder['name'], 'folderID' => $folder['id']);
                }
            }

            if (is_array($groupInfoList)) {
                foreach ($groupInfoList as &$groupInfo) {
                    $apiList = array();
                    if (is_array($data['requests'])) {
                        foreach ($data['requests'] as $request) {
                            if (empty($request['folder'])) {
                                $request['folder'] = 'default';
                            }
                            if ($request['folder'] != $groupInfo['folderID']) {
                                continue;
                            }
                            $apiInfo['baseInfo']['apiName'] = $request['name'];
                            $apiInfo['baseInfo']['apiURI'] = $request['url'];
                            $apiInfo['baseInfo']['apiProtocol'] = (strpos($request['url'], 'https') !== 0) ? 0 : 1;
                            $apiInfo['baseInfo']['apiStatus'] = 0;
                            $apiInfo['baseInfo']['starred'] = 0;
                            $apiInfo['baseInfo']['apiSuccessMock'] = '';
                            $apiInfo['baseInfo']['apiFailureMock'] = '';
                            $apiInfo['baseInfo']['apiRequestParamType'] = 0;
                            $apiInfo['baseInfo']['apiRequestRaw'] = '';
                            $apiInfo['baseInfo']['apiUpdateTime'] = date("Y-m-d H:i:s", time());
                            switch ($request['method']) {
                                case 'POST' :
                                    $apiInfo['baseInfo']['apiRequestType'] = 0;
                                    break;
                                case 'GET' :
                                    $apiInfo['baseInfo']['apiRequestType'] = 1;
                                    break;
                                case 'PUT' :
                                    $apiInfo['baseInfo']['apiRequestType'] = 2;
                                    break;
                                case 'DELETE' :
                                    $apiInfo['baseInfo']['apiRequestType'] = 3;
                                    break;
                                case 'HEAD' :
                                    $apiInfo['baseInfo']['apiRequestType'] = 4;
                                    break;
                                case 'OPTIONS' :
                                    $apiInfo['baseInfo']['apiRequestType'] = 5;
                                    break;
                                case 'PATCH' :
                                    $apiInfo['baseInfo']['apiRequestType'] = 6;
                                    break;
                            }

                            $headerInfo = array();
                            $header_rows = array_filter(explode(chr(10), $request['headers']), "trim");

                            if (is_array($header_rows)) {
                                foreach ($header_rows as $row) {
                                    $keylen = strpos($row, ':');
                                    if ($keylen) {
                                        $headerInfo[] = array('headerName' => substr($row, 0, $keylen), 'headerValue' => trim(substr($row, $keylen + 1)));
                                    }
                                }
                            }
                            $apiInfo['headerInfo'] = $headerInfo;
                            unset($headerInfo);

                            $apiRequestParam = array();
                            $items = $request['data'];
                            if (is_array($items)) {
                                foreach ($items as $item) {
                                    $param['paramKey'] = $item['key'];
                                    $param['paramValue'] = $item['value'];
                                    $param['paramType'] = ($item['type'] == 'text') ? 0 : 1;
                                    $param['paramNotNull'] = $item['enabled'] ? 0 : 1;
                                    $param['paramName'] = '';
                                    $param['paramLimit'] = '';
                                    $param['paramValueList'] = array();
                                    $apiRequestParam[] = $param;
                                    unset($param);
                                }
                            }
                            $apiInfo['requestInfo'] = $apiRequestParam;
                            unset($apiRequestParam);
                            $apiInfo['resultInfo'] = array();

                            $apiList[] = $apiInfo;
                            unset($apiInfo);
                        }
                    }
                    $groupInfo['apiList'] = $apiList;
                    unset($apiList);
                }
            }
            $dao = new ImportDao;
            return $dao->importOther($projectInfo, $groupInfoList, $_SESSION['userID']);
        } catch (\PDOException $e) {
            var_dump($e->getMessage());
            return FALSE;
        }
    }

    /**
     * Import v2 Version from Postman
     * @param $data string
     * @return bool
     */
    public function importPostmanV2(&$data)
    {
        try {
            $project_info = array(
                'projectName' => $data['info']['name'],
                'projectType' => 0,
                'projectVersion' => 1.0
            );
            $groups = array();
            $groups[0]['groupName'] = 'Default Group';
            $groups[0]['apiList'] = array();

            $group_count = 1;
            foreach ($data['item'] as $item) {
                $api_info = array();
                if (empty($item['item'])) {
                    $api_info['baseInfo']['apiName'] = $item['name'];
                    if (!empty($item['request']['url']['raw'])) {
                        $api_info['baseInfo']['apiURI'] = explode('?', $item['request']['url']['raw'])[0];
                    } else {
                        $api_info['baseInfo']['apiURI'] = $item['request']['url'];
                    }
                    if (is_array($item['request']['url'])) {
                        $api_info['baseInfo']['apiProtocol'] = (strpos($item['request']['url']['raw'], 'https') !== 0) ? 0 : 1;
                    } else {
                        $api_info['baseInfo']['apiProtocol'] = (strpos($item['request']['url'], 'https') !== 0) ? 0 : 1;
                    }
                    $api_info['baseInfo']['apiStatus'] = 0;
                    $api_info['baseInfo']['starred'] = 0;
                    $api_info['baseInfo']['apiRequestRaw'] = $item['request']['body']['raw'];
                    $api_info['baseInfo']['apiUpdateTime'] = date("Y-m-d H:i:s", time());

                    
                    if ($item['request']['body']['mode'] == 'raw') {
                        $api_info['baseInfo']['apiRequestParamType'] = 1;
                    } else {
                        $api_info['baseInfo']['apiRequestParamType'] = 0;
                    }

                    switch ($item['request']['method']) {
                        case 'POST' :
                            $api_info['baseInfo']['apiRequestType'] = 0;
                            break;
                        case 'GET' :
                            $api_info['baseInfo']['apiRequestType'] = 1;
                            break;
                        case 'PUT' :
                            $api_info['baseInfo']['apiRequestType'] = 2;
                            break;
                        case 'DELETE' :
                            $api_info['baseInfo']['apiRequestType'] = 3;
                            break;
                        case 'HEAD' :
                            $api_info['baseInfo']['apiRequestType'] = 4;
                            break;
                        case 'OPTIONS' :
                            $api_info['baseInfo']['apiRequestType'] = 5;
                            break;
                        case 'PATCH' :
                            $api_info['baseInfo']['apiRequestType'] = 6;
                            break;
                    }

                    $headerInfo = array();
                    foreach ($item['request']['header'] as $header) {
                        $headerInfo[] = array(
                            'headerName' => $header['key'],
                            'headerValue' => $header['value']
                        );
                    }
                    $api_info['headerInfo'] = $headerInfo;
                    unset($headerInfo);

                    $api_info_request_param = array();
                    if ($item['request']['body']['mode'] == 'formdata') {
                        $parameters = $item['request']['body']['formdata'];
                        foreach ($parameters as $parameter) {
                            $param = array();
                            $param['paramKey'] = $parameter['key'];
                            $param['paramValue'] = $parameter['value'];
                            $param['paramType'] = ($parameter['type'] == 'text') ? 0 : 1;
                            $param['paramNotNull'] = $parameter['enabled'] ? 0 : 1;
                            $param['paramName'] = '';
                            $param['paramLimit'] = '';
                            $param['paramValueList'] = array();
                            $api_info_request_param[] = $param;
                            unset($param);
                        }
                    }
                    if ($item['request']['method'] == 'GET' && !empty($item['request']['url']['raw'])) {
                        $parameters = $item['request']['url']['query'];
                        foreach ($parameters as $parameter) {
                            $param = array();
                            $param['paramKey'] = $parameter['key'];
                            $param['paramValue'] = $parameter['value'];
                            $param['paramType'] = 0;
                            $param['paramNotNull'] = $parameter['equals'] ? 0 : 1;
                            $param['paramName'] = '';
                            $param['paramLimit'] = '';
                            $param['paramValueList'] = array();
                            $api_info_request_param[] = $param;
                            unset($param);
                        }
                    }
                    $api_info['requestInfo'] = $api_info_request_param;
                    unset($api_info_request_param);

                    $api_info['resultInfo'] = array();

                    $groups[0]['apiList'][] = $api_info;

                    unset($api_info);
                } else {
                    $groups[$group_count]['groupName'] = $item['name'];
                    $groups[$group_count]['apiList'] = array();

                    foreach ($item['item'] as $api) {
                        $api_info = array();
                        $api_info['baseInfo']['apiName'] = $api['name'];
                        if (empty($api_info['baseInfo']['apiName'])) {
                            $api_info['baseInfo']['apiName'] = 'empty_name';
                        }
                        if (!empty($api['request']['url']['raw'])) {
                            $api_info['baseInfo']['apiURI'] = explode('?', $api['request']['url']['raw'])[0];
                        } else {
                            $api_info['baseInfo']['apiURI'] = $api['request']['url'];
                        }
                        if (empty($api_info['baseInfo']['apiURI'])) {
                            $api_info['baseInfo']['apiURI'] = 'empty_uri';
                        }
                        if (is_array($api['request']['url'])) {
                            $api_info['baseInfo']['apiProtocol'] = (strpos($api['request']['url']['raw'], 'https') !== 0) ? 0 : 1;
                        } else {
                            $api_info['baseInfo']['apiProtocol'] = (strpos($api['request']['url'], 'https') !== 0) ? 0 : 1;
                        }
                        $api_info['baseInfo']['apiStatus'] = 0;
                        $api_info['baseInfo']['starred'] = 0;
                        $api_info['baseInfo']['apiRequestRaw'] = $api['request']['body']['raw'];
                        $api_info['baseInfo']['apiUpdateTime'] = date("Y-m-d H:i:s", time());

                        
                        if ($api['request']['body']['mode'] == 'raw') {
                            $api_info['baseInfo']['apiRequestParamType'] = 1;
                        } else {
                            $api_info['baseInfo']['apiRequestParamType'] = 0;
                        }

                        switch ($api['request']['method']) {
                            case 'POST' :
                                $api_info['baseInfo']['apiRequestType'] = 0;
                                break;
                            case 'GET' :
                                $api_info['baseInfo']['apiRequestType'] = 1;
                                break;
                            case 'PUT' :
                                $api_info['baseInfo']['apiRequestType'] = 2;
                                break;
                            case 'DELETE' :
                                $api_info['baseInfo']['apiRequestType'] = 3;
                                break;
                            case 'HEAD' :
                                $api_info['baseInfo']['apiRequestType'] = 4;
                                break;
                            case 'OPTIONS' :
                                $api_info['baseInfo']['apiRequestType'] = 5;
                                break;
                            case 'PATCH' :
                                $api_info['baseInfo']['apiRequestType'] = 6;
                                break;
                        }

                        $headerInfo = array();
                        foreach ($api['request']['header'] as $header) {
                            $headerInfo[] = array(
                                'headerName' => $header['key'],
                                'headerValue' => $header['value']
                            );
                        }
                        $api_info['headerInfo'] = $headerInfo;
                        unset($headerInfo);

                        $api_info_request_param = array();
                        if ($api['request']['body']['mode'] == 'formdata') {
                            $parameters = $api['request']['body']['formdata'];
                            foreach ($parameters as $parameter) {
                                $param['paramKey'] = $parameter['key'];
                                $param['paramValue'] = $parameter['value'];
                                $param['paramType'] = ($parameter['type'] == 'text') ? 0 : 1;
                                $param['paramNotNull'] = $parameter['enabled'] ? 0 : 1;
                                $param['paramName'] = '';
                                $param['paramLimit'] = '';
                                $param['paramValueList'] = array();
                                $api_info_request_param[] = $param;
                                unset($param);
                            }
                        }
                        $api_info['requestInfo'] = $api_info_request_param;
                        unset($api_info_request_param);

                        $api_info['resultInfo'] = array();

                        $groups[$group_count]['apiList'][] = $api_info;

                        unset($api_info);
                    }
                    $group_count++;
                }
            }

            $dao = new ImportDao();
            return $dao->importOther($project_info, $groups, $_SESSION['userID']);
        } catch (\PDOException $e) {
            return FALSE;
        }
    }

    /**
     * Import Swaager
     * @param string $content 
     * @return bool
     */
    public function importSwagger(&$content)
    {
        $user_id = $_SESSION['userID'];
        $swagger = json_decode($content, TRUE);
        $project_info = $swagger['info'];
        
        $project_type = '0';
        
        $group_info_list[] = array('groupName' => 'Default Group');
        $request_type = array(
            'POST' => '0',
            'GET' => '1',
            'PUT' => '2',
            'DELETE' => '3',
            'HEAD' => '4',
            'OPTIONS' => '5',
            'PATCH' => '6'
        );
      
        $protocol = array(
            'HTTP' => 0,
            'HTTPS' => 1
        );
        
        $param_type = array(
            'string' => '0',
            'file' => '1',
            'json' => '2',
            'int' => '3',
            'float' => '4',
            'double' => '5',
            'date' => '6',
            'datetime' => '7',
            'boolean' => '8',
            'byte' => '9',
            'short' => '10',
            'long' => '11',
            'array' => '12',
            'object' => '13',
            'number' => '14'
        );
      
        $api_protocol = $protocol[strtoupper($swagger['schemes'][0])];
        if (empty($api_protocol)) {
            $api_protocol = 1;
        }
       
        if (empty($project_info['description'])) {
            $project_info['description'] = $project_info['title'];
        }
        
        $project_info = array(
            'projectName' => $project_info['title'],
            'projectType' => $project_type,
            'projectVersion' => $project_info['version'],
            'projectDesc' => $project_info['description']
        );
        $apiList = $swagger['paths'];
        $api_list = array();
        $group_name_list = array();
        foreach ($apiList as $api_uri => $api_info_list) {
            
            foreach ($api_info_list as $api_request_type => $api_info) {
                $group_name = $api_info['tags'][0];
                if (in_array($group_name, $group_name_list)) {
                    continue;
                }
                $group_info_list[] = array('groupName' => $group_name);
                $group_name_list[] = $group_name;
            }
        }
        if (is_array($group_info_list)) {
            foreach ($group_info_list as &$group_info) {
                if (is_array($apiList)) {
                   
                    foreach ($apiList as $api_uri => $api_info_list) {
                        
                        foreach ($api_info_list as $api_request_type => $api_info) {
                            if ($api_info['tags'][0] != $group_info['groupName']) {
                                continue;
                            }
                            if (empty($api_info['summary'])) {
                               
                                $api_info['summary'] = $api_info['operationId'];
                            }
                            
                            $apiInfo['baseInfo']['apiName'] = $api_info['summary'];
                           
                            // if(strpos($uri, '{'))
                            // {
                            // $api_uri = preg_replace('/\{.*\}/', $api_info['operationId'], $uri);
                            // }
                            // else
                            // {
                            // $api_uri = $uri;
                            // }
                            
                            $apiInfo['baseInfo']['apiURI'] = $api_uri;
                            
                            $apiInfo['baseInfo']['apiStatus'] = 0;
                            
                            $apiInfo['baseInfo']['apiRequestParamType'] = 0;
                            
                            $apiInfo['baseInfo']['starred'] = 0;
                            
                            $apiInfo['baseInfo']['apiNoteType'] = 0;
                            
                            $apiInfo['baseInfo']['apiRequestType'] = $request_type[strtoupper($api_request_type)];
                            
                            $apiInfo['headerInfo'] = array();
                            if ($api_info['consumes']) {
                                for ($i = 0; $i < count($api_info['consumes']); $i++) {
                                    $apiInfo['headerInfo'][$i] = array(
                                        'headerName' => 'Content-Type',
                                        'headerValue' => $api_info['consumes'][$i]
                                    );
                                }
                            }
                            if ($api_info['produces']) {
                                for ($i = 0; $i < count($api_info['produces']); $i++) {
                                    $apiInfo['headerInfo'][] = array(
                                        'headerName' => 'Accept',
                                        'headerValue' => $api_info['produces'][$i]
                                    );
                                }
                            }
                           
                            $apiInfo['requestInfo'] = array();
                            if ($api_info['parameters']) {
                                $i = 0;
                                foreach ($api_info['parameters'] as $param) {
                                    
                                    $apiInfo['requestInfo'][$i]['paramKey'] = $param['name'];
                                    
                                    switch ($param['type']) {
                                        case "integer" :
                                            $apiInfo['requestInfo'][$i]['paramType'] = $param_type['int'];
                                            break;
                                        case "string" :
                                            $apiInfo['requestInfo'][$i]['paramType'] = $param_type['string'];
                                            break;
                                        case 'long' :
                                            $apiInfo['requestInfo'][$i]['paramType'] = $param_type['long'];
                                            break;
                                        case 'float' :
                                            $apiInfo['requestInfo'][$i]['paramType'] = $param_type['float'];
                                            break;
                                        case 'double' :
                                            $apiInfo['requestInfo'][$i]['paramType'] = $param_type['double'];
                                            break;
                                        case 'byte' :
                                            $apiInfo['requestInfo'][$i]['paramType'] = $param_type['byte'];
                                            break;
                                        case 'file' :
                                            $apiInfo['requestInfo'][$i]['paramType'] = $param_type['file'];
                                            break;
                                        case 'date' :
                                            $apiInfo['requestInfo'][$i]['paramType'] = $param_type['date'];
                                            break;
                                        case 'dateTime' :
                                            $apiInfo['requestInfo'][$i]['paramType'] = $param_type['dateTime'];
                                            break;
                                        case 'boolean' :
                                            $apiInfo['requestInfo'][$i]['paramType'] = $param_type['boolean'];
                                            break;
                                        case 'array' :
                                            $apiInfo['requestInfo'][$i]['paramType'] = $param_type['array'];
                                            break;
                                        case 'json' :
                                            $apiInfo['requestInfo'][$i]['paramType'] = $param_type['json'];
                                            break;
                                        case 'object' :
                                            $apiInfo['requestInfo'][$i]['paramType'] = $param_type['object'];
                                            break;
                                        case 'number' :
                                            $apiInfo['requestInfo'][$i]['paramType'] = $param_type['number'];
                                            break;
                                        default :
                                            $apiInfo['requestInfo'][$i]['paramType'] = $param_type['string'];
                                    }
                                    
                                    $apiInfo['requestInfo'][$i]['paramName'] = $param['description'];
                                    
                                    $apiInfo['requestInfo'][$i]['paramNotNull'] = $param['required'] ? 0 : 1;
                                    
                                    $apiInfo['requestInfo'][$i]['paramValue'] = '';
                                    ++$i;
                                }
                            }

                            
                            $apiInfo['resultInfo'] = array();
                            if ($api_info['responses']) {
                                $k = 0;
                                foreach ($api_info['responses'] as $paramKey => $respon) {
                                    $apiInfo['resultInfo'][$k]['paramType'] = '';
                                    
                                    switch ($respon['schema']['type']) {
                                        case "integer" :
                                            $apiInfo['resultInfo'][$k]['paramType'] = $param_type['int'];
                                            break;
                                        case "string" :
                                            $apiInfo['resultInfo'][$k]['paramType'] = $param_type['string'];
                                            break;
                                        case 'long' :
                                            $apiInfo['resultInfo'][$k]['paramType'] = $param_type['long'];
                                            break;
                                        case 'float' :
                                            $apiInfo['resultInfo'][$k]['paramType'] = $param_type['float'];
                                            break;
                                        case 'double' :
                                            $apiInfo['resultInfo'][$k]['paramType'] = $param_type['double'];
                                            break;
                                        case 'byte' :
                                            $apiInfo['resultInfo'][$k]['paramType'] = $param_type['byte'];
                                            break;
                                        case 'file' :
                                            $apiInfo['resultInfo'][$k]['paramType'] = $param_type['file'];
                                            break;
                                        case 'date' :
                                            $apiInfo['resultInfo'][$k]['paramType'] = $param_type['date'];
                                            break;
                                        case 'dateTime' :
                                            $apiInfo['resultInfo'][$k]['paramType'] = $param_type['dateTime'];
                                            break;
                                        case 'boolean' :
                                            $apiInfo['resultInfo'][$k]['paramType'] = $param_type['boolean'];
                                            break;
                                        case 'array' :
                                            $apiInfo['resultInfo'][$k]['paramType'] = $param_type['array'];
                                            break;
                                        case 'json' :
                                            $apiInfo['resultInfo'][$k]['paramType'] = $param_type['json'];
                                            break;
                                        case 'object' :
                                            $apiInfo['resultInfo'][$k]['paramType'] = $param_type['object'];
                                            break;
                                        case 'number' :
                                            $apiInfo['resultInfo'][$k]['paramType'] = $param_type['number'];
                                            break;
                                        default :
                                            $apiInfo['resultInfo'][$k]['paramType'] = $param_type['string'];
                                    }
                                    
                                    $apiInfo['resultInfo'][$k]['paramKey'] = $paramKey;
                                   
                                    $apiInfo['resultInfo'][$k]['paramName'] = $respon['description'];
                                    
                                    $apiInfo['resultInfo'][$k]['paramNotNull'] = '0';
                                    ++$k;
                                }
                            }

                            $apiInfo['baseInfo']['apiRequestRaw'] = '';
                            $apiInfo['baseInfo']['apiProtocol'] = $api_protocol;
                            $apiInfo['baseInfo']['apiUpdateTime'] = date('Y-m-d H:i:s', time());

                            $api_list[] = $apiInfo;
                            unset($apiInfo);
                        }
                    }
                }
                $group_info['apiList'] = $api_list;
                unset($api_list);
            }
        }

        $dao = new ImportDao;
        $result = $dao->importOther($project_info, $group_info_list, $user_id);
        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Import RAP
     * @param $data
     * @return bool
     */
    public function importRAP(&$data)
    {
        $user_id = $_SESSION['userID'];
        $param_type = array('string' => '0', 'file' => '1', 'json' => '2', 'int' => '3', 'float' => '4', 'double' => '5', 'date' => '6', 'datetime' => '7', 'boolean' => '8', 'byte' => '9', 'short' => '10', 'long' => '11', 'array' => '12', 'object' => '13', 'number' => '14');
        try {
            $project_info = array(
                'projectName' => $data['name'],
                'projectType' => 0,
                'projectVersion' => 1.0
            );

            $group_info_list = array();

            foreach ($data['moduleList'] as $module) {
                $group_info = array(
                    'groupName' => $module['name'],
                    'apiList' => array()
                );

                foreach ($module['pageList'] as $pageList) {
                    $child_group_info = null;
                    if ($pageList['name']) {
                        $child_group_info = array(
                            'groupName' => $pageList['name'],
                            'apiList' => array()
                        );
                    }
                    $api_list = array();
                    foreach ($pageList['actionList'] as $action) {
                        $api_info = array();
                        $api_info['baseInfo']['apiName'] = $action['name'];
                        $api_info['baseInfo']['apiURI'] = stripslashes($action['requestUrl']);
                        $api_info['baseInfo']['apiProtocol'] = 1;
                        $api_info['baseInfo']['apiStatus'] = 0;
                        $api_info['baseInfo']['starred'] = 0;
                        $api_info['baseInfo']['apiRequestParamType'] = 0;
                        $api_info['baseInfo']['apiRequestRaw'] = '';
                        $api_info['baseInfo']['apiUpdateTime'] = date("Y-m-d H:i:s", time());
                        switch ($action['requestType']) {
                            case '1' :
                                $api_info['baseInfo']['apiRequestType'] = 1;
                                //GET
                                break;
                            case '2' :
                                $api_info['baseInfo']['apiRequestType'] = 0;
                                //POST
                                break;
                            case '3' :
                                $api_info['baseInfo']['apiRequestType'] = 2;
                                //PUT
                                break;
                            case '4' :
                                $api_info['baseInfo']['apiRequestType'] = 3;
                                //DELETE
                                break;
                            default :
                                $api_info['baseInfo']['apiRequestType'] = 1;
                                
                                break;
                        }

                        $api_info['headerInfo'] = array();

                        $api_request_param = array();
                        foreach ($action['requestParameterList'] as $parameter) {
                            $param['paramKey'] = $parameter['identifier'];
                            $param['paramValue'] = $parameter['remark'];
                            $param['paramNotNull'] = 0;
                            $param['paramName'] = $parameter['name'];
                            $param['paramLimit'] = $parameter['dataType'];
                            $param['paramValueList'] = array();
                            
                            $param['paramType'] = $this->getDataType($parameter['dataType']);
                            $api_request_param[] = $param;
                            if (!empty($parameter['parameterList'])) {
                                foreach ($parameter['parameterList'] as $parameter1) {
                                    $param1['paramKey'] = $param['paramKey'] . '>>' . $parameter1['identifier'];
                                    $param1['paramValue'] = $parameter1['remark'];
                                    $param1['paramLimit'] = $parameter1['dataType'];
                                    $param1['paramNotNull'] = 0;
                                    $param1['paramName'] = $parameter1['name'];
                                    $param1['paramValueList'] = array();
                                    
                                    $param1['paramType'] = $this->getDataType($parameter1['dataType']);
                                    $api_request_param[] = $param1;
                                    if (!empty($parameter1['parameterList'])) {
                                        foreach ($parameter1['parameterList'] as $parameter2) {
                                            $param2['paramKey'] = $param1['paramKey'] . '>>' . $parameter2['identifier'];
                                            $param2['paramValue'] = $parameter2['remark'];
                                            $param2['paramLimit'] = $parameter2['dataType'];
                                            $param2['paramNotNull'] = 0;
                                            $param2['paramName'] = $parameter2['name'];
                                            $param2['paramValueList'] = array();
                                            
                                            $param2['paramType'] = $this->getDataType($parameter2['dataType']);
                                            $api_request_param[] = $param2;
                                            if (!empty($parameter2['parameterList'])) {
                                                foreach ($parameter2['parameterList'] as $parameter3) {
                                                    $param3['paramKey'] = $param2['paramKey'] . '>>' . $parameter3['identifier'];
                                                    $param3['paramValue'] = $parameter3['remark'];
                                                    $param3['paramLimit'] = $parameter3['dataType'];
                                                    $param3['paramNotNull'] = 0;
                                                    $param3['paramName'] = $parameter3['name'];
                                                    $param3['paramValueList'] = array();
                                                    
                                                    $param3['paramType'] = $this->getDataType($parameter3['dataType']);
                                                    $api_request_param[] = $param3;
                                                    if (!empty($parameter3['parameterList'])) {
                                                        foreach ($parameter3['parameterList'] as $parameter4) {
                                                            $param4['paramKey'] = $param3['paramKey'] . '>>' . $parameter4['identifier'];
                                                            $param4['paramValue'] = $parameter4['remark'];
                                                            $param4['paramLimit'] = $parameter4['dataType'];
                                                            $param4['paramNotNull'] = 0;
                                                            $param4['paramName'] = $parameter4['name'];
                                                            $param4['paramValueList'] = array();
                                                            
                                                            $param4['paramType'] = $this->getDataType($parameter4['dataType']);
                                                            $api_request_param[] = $param4;
                                                            if (!empty($parameter4['parameterList'])) {
                                                                foreach ($parameter4['parameterList'] as $parameter5) {
                                                                    $param5['paramKey'] = $param4['paramKey'] . '>>' . $parameter5['identifier'];
                                                                    $param5['paramValue'] = $parameter5['remark'];
                                                                    $param5['paramLimit'] = $parameter5['dataType'];
                                                                    $param5['paramNotNull'] = 0;
                                                                    $param5['paramName'] = $parameter5['name'];
                                                                    $param5['paramValueList'] = array();
                                                                    
                                                                    $param5['paramType'] = $this->getDataType($parameter5['dataType']);
                                                                    $api_request_param[] = $param5;
                                                                    if (!empty($parameter5['parameterList'])) {
                                                                        foreach ($parameter5['parameterList'] as $parameter6) {
                                                                            $param6['paramKey'] = $param5['paramKey'] . '>>' . $parameter6['identifier'];
                                                                            $param6['paramValue'] = $parameter6['remark'];
                                                                            $param6['paramLimit'] = $parameter6['dataType'];
                                                                            $param6['paramNotNull'] = 0;
                                                                            $param6['paramName'] = $parameter6['name'];
                                                                            $param6['paramValueList'] = array();
                                                                            
                                                                            $param6['paramType'] = $this->getDataType($parameter6['dataType']);
                                                                            $api_request_param[] = $param6;
                                                                            unset($param6);
                                                                        }
                                                                    }
                                                                    unset($param5);
                                                                }
                                                            }
                                                            unset($param4);
                                                        }
                                                    }
                                                    unset($param3);
                                                }
                                            }
                                            unset($param2);
                                        }
                                    }
                                    unset($param1);
                                }
                            }
                            unset($param);
                        }
                        $api_info['requestInfo'] = $api_request_param;
                        unset($api_request_param);

                        $api_result_param = array();
                        foreach ($action['responseParameterList'] as $parameter) {
                            $param['paramKey'] = $parameter['identifier'];
                            $param['paramNotNull'] = 0;
                            $param['paramName'] = $parameter['name'];
                            $param['paramValueList'] = array();
                            
                            $param['paramType'] = $this->getDataType($parameter['dataType']);
                            $api_result_param[] = $param;
                            if (!empty($parameter['parameterList'])) {
                                foreach ($parameter['parameterList'] as $parameter1) {
                                    $param1['paramKey'] = $param['paramKey'] . '>>' . $parameter1['identifier'];
                                    $param1['paramNotNull'] = 0;
                                    $param1['paramName'] = $parameter1['name'];
                                    $param1['paramValueList'] = array();
                                    
                                    $param1['paramType'] = $this->getDataType($parameter1['dataType']);
                                    $api_result_param[] = $param1;
                                    if (!empty($parameter1['parameterList'])) {
                                        foreach ($parameter1['parameterList'] as $parameter2) {
                                            $param2['paramKey'] = $param1['paramKey'] . '>>' . $parameter2['identifier'];
                                            $param2['paramNotNull'] = 0;
                                            $param2['paramName'] = $parameter2['name'];
                                            $param2['paramValueList'] = array();
                                            
                                            $param2['paramType'] = $this->getDataType($parameter2['dataType']);
                                            $api_result_param[] = $param2;
                                            if (!empty($parameter2['parameterList'])) {
                                                foreach ($parameter2['parameterList'] as $parameter3) {
                                                    $param3['paramKey'] = $param2['paramKey'] . '>>' . $parameter3['identifier'];
                                                    $param3['paramNotNull'] = 0;
                                                    $param3['paramName'] = $parameter3['name'];
                                                    $param3['paramValueList'] = array();
                                                    
                                                    $param3['paramType'] = $this->getDataType($parameter3['dataType']);
                                                    $api_result_param[] = $param3;
                                                    if (!empty($parameter3['parameterList'])) {
                                                        foreach ($parameter3['parameterList'] as $parameter4) {
                                                            $param4['paramKey'] = $param3['paramKey'] . '>>' . $parameter4['identifier'];
                                                            $param4['paramNotNull'] = 0;
                                                            $param4['paramName'] = $parameter4['name'];
                                                            $param4['paramValueList'] = array();
                                                            
                                                            $param4['paramType'] = $this->getDataType($parameter4['dataType']);
                                                            $api_result_param[] = $param4;
                                                            if (!empty($parameter4['parameterList'])) {
                                                                foreach ($parameter4['parameterList'] as $parameter5) {
                                                                    $param5['paramKey'] = $param4['paramKey'] . '>>' . $parameter5['identifier'];
                                                                    $param5['paramNotNull'] = 0;
                                                                    $param5['paramName'] = $parameter5['name'];
                                                                    $param5['paramValueList'] = array();
                                                                   
                                                                    $param5['paramType'] = $this->getDataType($parameter5['dataType']);
                                                                    $api_result_param[] = $param5;
                                                                    if (!empty($parameter5['parameterList'])) {
                                                                        foreach ($parameter5['parameterList'] as $parameter6) {
                                                                            $param6['paramKey'] = $param5['paramKey'] . '>>' . $parameter6['identifier'];
                                                                            $param6['paramNotNull'] = 0;
                                                                            $param6['paramName'] = $parameter6['name'];
                                                                            $param6['paramValueList'] = array();
                                                                            
                                                                            $param6['paramType'] = $this->getDataType($parameter6['dataType']);
                                                                            $api_result_param[] = $param6;
                                                                            unset($param6);
                                                                        }
                                                                    }
                                                                    unset($param5);
                                                                }
                                                            }
                                                            unset($param4);
                                                        }
                                                    }
                                                    unset($param3);
                                                }
                                            }
                                            unset($param2);
                                        }
                                    }
                                    unset($param1);
                                }
                            }
                            unset($param);
                        }
                        $api_info['resultInfo'] = $api_result_param;
                        unset($api_result_param);

                        $api_list[] = $api_info;
                        unset($api_info);
                    }
                    if ($child_group_info) {
                        $child_group_info['apiList'] = $api_list;
                        $group_info['childGroupList'][] = $child_group_info;
                    } else {
                        $group_info['apiList'] = array_merge($group_info['apiList'], $api_list);
                    }
                    unset($api_list);
                }
                $group_info_list[] = $group_info;
                unset($group_info);
            }
            $dao = new ImportDao();
            return $dao->importOther($project_info, $group_info_list, $user_id);
        } catch (\PDOException $e) {
            return FALSE;
        }
    }

    /**
     * Get Data Type
     * @param $data_type
     * @return mixed|string
     */
    private function getDataType(&$data_type)
    {
        $param_type = array('string' => '0', 'file' => '1', 'json' => '2', 'int' => '3', 'float' => '4', 'double' => '5', 'date' => '6', 'datetime' => '7', 'boolean' => '8', 'byte' => '9', 'short' => '10', 'long' => '11', 'array' => '12', 'object' => '13', 'number' => '14');
        $type = 'array';
        switch ($data_type) {
            case "integer":
                $type = $param_type['int'];
                break;
            case "string":
                $type = $param_type['string'];
                break;
            case 'long':
                $type = $param_type['long'];
                break;
            case 'float':
                $type = $param_type['float'];
                break;
            case 'double':
                $type = $param_type['double'];
                break;
            case 'byte':
                $type = $param_type['byte'];
                break;
            case 'file':
                $type = $param_type['file'];
                break;
            case 'date':
                $type = $param_type['date'];
                break;
            case 'dateTime':
                $type = $param_type['dateTime'];
                break;
            case 'boolean':
                $type = $param_type['boolean'];
                break;
            case 'array':
                $type = $param_type['array'];
                break;
            case 'json':
                $type = $param_type['json'];
                break;
            case 'object':
                $type = $param_type['object'];
                break;
            case 'number':
                $type = $param_type['number'];
                break;
            default:
                $type = $param_type['array'];
        }
        return $type;
    }
}

?>
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
class ApiController
{
    // return an json object
    private $returnJson = array('type' => 'api');

    /**
     * Checkout login status
     */
    public function __construct()
    {
        // identity authentication
 
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * Add apii
     */
    public function addApi()
    {
        $groupID = securelyInput('groupID');
        //check user permission
        $module = new GroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $apiName = securelyInput('apiName');
        $apiURI = securelyInput('apiURI');
        $apiProtocol = securelyInput('apiProtocol');
        $apiRequestType = securelyInput('apiRequestType');
        $apiSuccessMock = quickInput('apiSuccessMock');
        $apiFailureMock = quickInput('apiFailureMock');
        $apiStatus = securelyInput('apiStatus');
        $starred = securelyInput('starred');
        $apiNoteType = securelyInput('apiNoteType');
        $apiNoteRaw = securelyInput('apiNoteRaw');
        $apiNote = securelyInput('apiNote');
        $apiRequestParamType = securelyInput('apiRequestParamType');
        $apiRequestRaw = securelyInput('apiRequestRaw');
        $apiHeader = json_decode($_POST['apiHeader'], TRUE);
        $apiRequestParam = json_decode($_POST['apiRequestParam'], TRUE);
        $apiResultParam = json_decode($_POST['apiResultParam'], TRUE);
		$apiUrlParam = json_decode($_POST['apiUrlParam'], TRUE);
        $mockRule = json_decode(quickInput('mockRule'), TRUE);
        //restful参数
        $apiRestfulParam = json_decode($_POST['apiRestfulParam'], TRUE);          
        $mockResult = securelyInput('mockResult');
        $mockConfig = quickInput('mockConfig');
        $failure_status_code = securelyInput('apiFailureStatusCode', '200');
        $success_status_code = securelyInput('apiSuccessStatusCode', '200');
       
        // 返回头部
        $response_header = json_decode($_POST["responseHeader"],TRUE);
        $service = new ApiModule;
        $result = $service->addApi($apiName, $apiURI, $apiProtocol, $apiSuccessMock, $apiFailureMock, $apiRequestType, $apiStatus, $groupID, $apiHeader, $apiRequestParam, $apiResultParam, $apiUrlParam, $starred, $apiNoteType, $apiNoteRaw, $apiNote, $apiRequestParamType, $apiRequestRaw, $mockRule, $mockResult, $mockConfig, $success_status_code, $failure_status_code, $before_inject, $after_inject,$response_header,$apiRestfulParam);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['apiID'] = $result['apiID'];
            $this->returnJson['groupID'] = $result['groupID'];
        } else {
            $this->returnJson['statusCode'] = '160000';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Edit api
     */
    public function editApi()
    {
        $apiID = securelyInput('apiID');
        $module = new ApiModule();
        //check user permission
        $userType = $module->getUserType($apiID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $apiName = securelyInput('apiName');
        $apiURI = securelyInput('apiURI');
        $apiProtocol = securelyInput('apiProtocol');
        $apiRequestType = securelyInput('apiRequestType');
        $apiSuccessMock = quickInput('apiSuccessMock');
        $apiFailureMock = quickInput('apiFailureMock');
        $apiStatus = securelyInput('apiStatus');
        $starred = securelyInput('starred');
        $apiNoteType = securelyInput('apiNoteType');
        $apiNoteRaw = securelyInput('apiNoteRaw');
        $apiNote = securelyInput('apiNote');
        $apiRequestParamType = securelyInput('apiRequestParamType');
        $apiRequestRaw = securelyInput('apiRequestRaw');
        $groupID = securelyInput('groupID');
        $apiHeader = json_decode($_POST['apiHeader'], TRUE);
        $apiRequestParam = json_decode($_POST['apiRequestParam'], TRUE);
        $apiResultParam = json_decode($_POST['apiResultParam'], TRUE);
        $apiUrlParam = json_decode($_POST['apiUrlParam'], TRUE);
        $update_desc = securelyInput('updateDesc');
        $mockRule = json_decode(quickInput('mockRule'), TRUE);
        $mockResult = securelyInput('mockResult');
        $mockConfig = quickInput('mockConfig');
        //restful参数
        $apiRestfulParam = json_decode($_POST['apiRestfulParam'], TRUE);     
        $failure_status_code = securelyInput('apiFailureStatusCode', '200');
        $success_status_code = securelyInput('apiSuccessStatusCode', '200');
      
        $response_header = json_decode($_POST["responseHeader"],TRUE);
        $service = new ApiModule;
        $result = $service->editApi($apiID, $apiName, $apiURI, $apiProtocol, $apiSuccessMock, $apiFailureMock, $apiRequestType, $apiStatus, $groupID, $apiHeader, $apiRequestParam, $apiResultParam, $apiUrlParam, $starred, $apiNoteType, $apiNoteRaw, $apiNote, $apiRequestParamType, $apiRequestRaw, $update_desc, $mockRule, $mockResult, $mockConfig, $success_status_code, $failure_status_code, $before_inject, $after_inject,$response_header,$apiRestfulParam);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['apiID'] = $result['apiID'];
            $this->returnJson['groupID'] = $result['groupID'];
        } else {
            $this->returnJson['statusCode'] = '160000';
        }
        exitOutput($this->returnJson);
    }


    /**
     * Delete apis in batches and move them into recycling station
     */
    public function removeApi()
    {
        //Interface ID
        $ids = quickInput('apiID');
        $projectID = securelyInput('projectID');
        //check user permission
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $arr = json_decode($ids);
        $arr = preg_grep('/^[0-9]{1,11}$/', $arr);
        if (empty($arr)) {
            $this->returnJson['statusCode'] = '160001';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '160002';
        } else {
            $api_ids = implode(',', $arr);
            $api_module = new ApiModule;
            $result = $api_module->removeApis($projectID, $api_ids);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Recover api
     */
    public function recoverApi()
    {
        //interface ID
        $ids = securelyInput('apiID');
        $groupID = securelyInput('groupID');
        $module = new GroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $arr = json_decode($ids);
        $arr = preg_grep('/^[0-9]{1,11}$/', $arr);
        if (empty($arr)) {
            $this->returnJson['statusCode'] = '160001';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $groupID)) {
            $this->returnJson['statusCode'] = '160002';
        } else {
            $api_ids = implode(',', $arr);
            $api_module = new ApiModule;
            $result = $api_module->recoverApis($groupID, $api_ids);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Remove apis in batches from recycling station
     */
    public function deleteApi()
    {
        $ids = securelyInput('apiID');
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $arr = json_decode($ids);
        $arr = preg_grep('/^[0-9]{1,11}$/', $arr);
        if (empty($arr)) {
            $this->returnJson['statusCode'] = '160001';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '160002';
        } else {
            $api_ids = implode(',', $arr);
            $api_module = new ApiModule;
            $result = $api_module->deleteApis($projectID, $api_ids);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Clean up the recycling station
     */
    public function cleanRecyclingStation()
    {
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '160002';
        } else {
            $service = new ApiModule;
            $result = $service->cleanRecyclingStation($projectID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '160011';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get api list from recycling station
     */
    public function getRecyclingStationApiList()
    {
        $projectID = securelyInput('projectID');
        $orderBy = securelyInput('orderBy', 0);
        $asc = securelyInput('asc', 0);
        if (preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $service = new ApiModule;
            switch ($orderBy) {
                case 0 :
                    {
                        $result = $service->getRecyclingStationApiListOrderByName($projectID, $asc);
                        break;
                    }
                case 1 :
                    {
                        $result = $service->getRecyclingStationApiListOrderByRemoveTime($projectID, $asc);
                        break;

                    }
                case 2 :
                    {
                        $result = $service->getRecyclingStationApiListOrderByStarred($projectID, $asc);
                        break;
                    }
                case 3 :
                    {
                        $result = $service->getRecyclingStationApiListOrderByCreateTime($projectID, $asc);
                    }
            }

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['apiList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '160007';
            }
        } else {
            $this->returnJson['statusCode'] = '160002';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get api list by group
     */
    public function getApiList()
    {
        $groupID = securelyInput('groupID');
        $orderBy = securelyInput('orderBy', 0);
        $asc = securelyInput('asc', 0);
        if(preg_match('/^[0-9]{1,11}$/', $groupID)) {
            $service = new ApiModule;
            switch ($orderBy) {
                case 0 :
                    {
                        $result = $service->getApiListOrderByName($groupID, $asc);
                        break;
                    }
                case 1 :
                    {
                        $result = $service->getApiListOrderByTime($groupID, $asc);
                        break;
                    }
                case 2 :
                    {
                        $asc = 1;
                        $result = $service->getApiListOrderByStarred($groupID, $asc);
                        break;
                    }
                case 3 :
                    {
                        $result = $service->getApiListOrderByCreateTime($groupID, $asc);
                        break;
                    }
            }

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['apiList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        } else {
            $this->returnJson['statusCode'] = '160002';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get api detail
     */
    public function getApi()
    {
        $apiID = securelyInput('apiID');
        $projectID = securelyInput('projectID');
        if (preg_match('/^[0-9]{1,11}$/', $apiID)) {
            $service = new ApiModule;
            $result = $service->getApi($apiID,$projectID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['apiInfo'] = $result;
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        } else {
            $this->returnJson['statusCode'] = '160001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get all api list by project
     */
    public function getAllApiList()
    {
        $projectID = securelyInput('projectID');
        $orderBy = securelyInput('orderBy', 0);
        $asc = securelyInput('asc', 0);
        if (preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $service = new ApiModule;

            switch ($orderBy) {
                case 0 :
                    {
                        $result = $service->getAllApiListOrderByName($projectID, $asc);
                        break;
                    }
                case 1 :
                    {
                        $result = $service->getAllApiListOrderByTime($projectID, $asc);
                        break;
                    }
                case 2 :
                    {
                        $asc = 1;
                        $result = $service->getAllApiListOrderByStarred($projectID, $asc);
                        break;
                    }
                case 3 :
                    {
                        $result = $service->getAllApiListOrderByCreateTime($projectID, $asc);
                    }
            }

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['apiList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        } else {
            $this->returnJson['statusCode'] = '160003';
        }
        exitOutput($this->returnJson);
    }

    /**
     * search api
     */
    public function searchApi()
    {
        $tipsLen = mb_strlen(quickInput('tips'), 'utf8');
        $tips = securelyInput('tips');
        $projectID = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '160003';
        } else if ($tipsLen > 255 || $tipsLen == 0) {
            $this->returnJson['statusCode'] = '160004';
        } else {
            $service = new ApiModule;
            $result = $service->searchApi($tips, $projectID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['apiList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * add star to an api
     */
    public function addStar()
    {
        $apiID = securelyInput('apiID');
        if (preg_match('/^[0-9]{1,11}$/', $apiID)) {
            $service = new ApiModule;
            $result = $service->addStar($apiID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        } else {
            $this->returnJson['statusCode'] = '160001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * remove star from an api
     */
    public function removeStar()
    {
        $apiID = securelyInput('apiID');
        if (preg_match('/^[0-9]{1,11}$/', $apiID)) {
            $service = new ApiModule;
            $result = $service->removeStar($apiID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        } else {
            $this->returnJson['statusCode'] = '160001';
        }
        exitOutput($this->returnJson);
    }

    /**
     *get history list
     */
    public function getApiHistoryList()
    {
        $api_id = securelyInput('apiID');
        if (!preg_match('/^[0-9]{1,11}$/', $api_id)) {
            $this->returnJson['statusCode'] = '160001';
        } else {
            $api_module = new ApiModule();
            $result = $api_module->getApiHistoryList($api_id);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson = array_merge($this->returnJson, $result);
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     *delete history record
     */
    public function deleteApiHistory()
    {
        $api_history_id = securelyInput('apiHistoryID');
        $api_id = securelyInput('apiID');
        $api_module = new ApiModule();
        $userType = $api_module->getUserType($api_id);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        if (!preg_match('/^[0-9]{1,11}$/', $api_id)) {
            $this->returnJson['statusCode'] = '160001';
        }
        elseif (!preg_match('/^[0-9]{1,11}$/', $api_history_id)) {
            $this->returnJson['statusCode'] = '160004';
        } else {
            $result = $api_module->deleteApiHistory($api_id, $api_history_id);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * switch version
     */
    public function toggleApiHistory()
    {
        $api_history_id = securelyInput('apiHistoryID');
        $api_id = securelyInput('apiID');
       
        $api_module = new ApiModule();
        $userType = $api_module->getUserType($api_id);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
   
        if (!preg_match('/^[0-9]{1,11}$/', $api_id)) {
            $this->returnJson['statusCode'] = '160001';
        } 
        else if (!preg_match('/^[0-9]{1,11}$/', $api_history_id)) {
            $this->returnJson['statusCode'] = '160004';
        } else {
            $result = $api_module->toggleApiHistory($api_id, $api_history_id);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

   
    /**
     * batch change api group
     */
    public function changeApiGroup()
    {
        $ids = securelyInput('apiID');
        $group_id = securelyInput('groupID');
        $module = new GroupModule();
        $userType = $module->getUserType($group_id);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $arr = json_decode($ids);
        $arr = preg_grep('/^[0-9]{1,11}$/', $arr);
        if (empty($arr)) {
            $this->returnJson['statusCode'] = '160001';
        } else if (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            $this->returnJson['statusCode'] = '160002';
        } else {
            $api_ids = implode(',', $arr);
            $api_module = new ApiModule;
            $result = $api_module->changeApiGroup($api_ids, $group_id);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * batch export data
     */
    public function exportApi()
    {
        $project_id = securelyInput('projectID');
        $ids = quickInput('apiID');
        $arr = json_decode($ids);
        $arr = preg_grep('/^[0-9]{1,11}$/', $arr); 
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '160003';
        }
        else if (empty($arr)) {
            $this->returnJson['statusCode'] = '160001';
        } else {
            $project_module = new ProjectModule();
            $user_type = $project_module->getUserType($project_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $api_ids = implode(',', $arr);
                $api_module = new ApiModule();
                $result = $api_module->exportApi($project_id, $api_ids);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['fileName'] = $result;
                } else {
                    $this->returnJson['statusCode'] = '160000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * batch import api
     */
    public function importApi()
    {
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        $group_id = securelyInput('groupID');
        if (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            $this->returnJson['statusCode'] = '160005';
        } 
        elseif (empty($data)) {
            $this->returnJson['statusCode'] = '160006';
            exitOutput($this->returnJson);
        } else {
            $group_module = new GroupModule();
            $user_type = $group_module->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $server = new ApiModule();
                $result = $server->importApi($group_id, $data);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                } else {
                    $this->returnJson['statusCode'] = '160000';
                }
            }
        }
        exitOutput($this->returnJson);
    }
    /**
     * 获取接口列表
     */
    public function getApiListByCondition()
    {
    	$project_id = securelyInput('projectID');
    	// 接口分组的字符串ID/hashKey
    	$group_id = securelyInput('groupID', -1);
    	
    	$condition = securelyInput('condition');
    	
    	// 排序依据,[0/1/2/3]=>[接口名称/更新时间/星标/创建时间]
    	$order_by = securelyInput('orderBy', 0);
    	
    	// 排序方式[0/1]=>[正序/倒序]
    	$asc = securelyInput('asc', 0);
    	
    	// 接口状态
    	$api_status = securelyInput("apiStatus", 0);
    	//关联ID
    	$conn_id = json_decode(securelyInput("connID"));
    	
    	if(($condition == 2 || $condition == 3) && empty($conn_id))
    	{
    		$this->returnJson['statusCode'] = '160007';
    		exitOutput($this->returnJson);
    	}
    	if($conn_id)
    	{
    		$conn_ids = implode(",", $conn_id);
    	}
    	$service = new ApiModule;
    	$result = $service -> getApiListByCondition($project_id, $group_id, $condition, $order_by, $asc,$conn_ids,$api_status);
    	$this->returnJson['statusCode'] = '000000';
    	$this->returnJson['apiList'] = $result;
    	exitOutput($this->returnJson);
    }
    /**
     * 修改接口状态
     */
    public function updateApiStatus()
    {
    	$projectID = securelyInput('projectID');
    	$ids = securelyInput('apiID');
    	$api_status = intval(securelyInput('apiStatus',0));
    	$arr = json_decode($ids);
    	$arr = preg_grep('/^[0-9]{1,11}$/', $arr); // 去掉数组中不是数字的ID、
    	if(empty($arr))
    	{
    		// apiID格式不合法
    		$this->return_json ['statusCode'] = '160001';
    	}
    	else
    	{
    		$service = new ApiModule;
    		$api_ids = implode(',', $arr);
    		$result = $service->updateApiStatus($projectID,$api_ids,$api_status);   	
    		if ($result) {
    			$this->returnJson['statusCode'] = '000000';
    		} else {
    			$this->returnJson['statusCode'] = '160000';
    		}
    	}
    	exitOutput($this->returnJson);
    }
    /**
     * 保存简易mock
     */
    public function saveSimpleMock()
    {
    	$projectID = securelyInput('projectID');
    	// 接口ID
    	$api_id = securelyInput('apiID');
    	$mock_type = securelyInput("mockType", 0);
    	$mock_data = $_POST["mockData"];
    	$status_code = securelyInput('statusCode', '200');
    	// 判断接口ID是否合法
    	if(! preg_match('/^[0-9]{1,11}$/', $api_id))
    	{
    		$this->returnJson['statusCode'] = '160001';
    	}
    	else
    	{
    			$service = new ApiModule;
    			$result = $service -> saveSimpleMock($projectID, $api_id, $mock_type, $mock_data, $status_code);
    			
    			// 验证结果
    			if($result)
    			{
    				$this->returnJson['statusCode'] = '000000';
    			}
    			else
    			{
    				$this->returnJson['statusCode'] = '160000';
    			}
    		}

    	exitOutput($this->returnJson);
    }
}

?>
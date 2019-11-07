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
class ApiModule
{

    public function __construct()
    {
        @session_start();
    }

    /**
     * get userType by apiID
     *
     * @param $apiID int
     * @return bool
     */
    public function getUserType(&$apiID)
    {
        $apiDao = new ApiDao();
        $projectID = $apiDao->checkApiPermission($apiID, $_SESSION['userID']);
        if (empty($projectID)) {
            return -1;
        }
        $dao = new AuthorizationDao();
        $result = $dao->getProjectUserType($_SESSION['userID'], $projectID);
        if ($result === FALSE) {
            return -1;
        } else {
            return $result;
        }
    }

    /**
     * add api
     *
     * @param $apiName string
     * @param $apiURI string
     * @param $apiProtocol int
     * @param $apiSuccessMock string
     * @param $apiFailureMock string
     * @param $apiRequestType int
     * @param $apiStatus int
     * @param $groupID int
     * @param $apiHeader string
     * @param $apiRequestParam string
     * @param $apiResultParam string
     * @param $starred int
     * @param $apiNoteType int
     * @param $apiNoteRaw string
     * @param $apiNote string
     * @param $apiRequestParamType int
     * @param $apiRequestRaw string
     * @param $mockRule array
     * @param $mockResult string
     * @param $mockConfig array
     * @param $success_status_code
     * @param $failure_status_code
     * @param $before_inject
     * @param $after_inject
     * @return int|bool
     */
    public function addApi(&$apiName, &$apiURI, &$apiProtocol, &$apiSuccessMock, &$apiFailureMock, &$apiRequestType, &$apiStatus, &$groupID, &$apiHeader, &$apiRequestParam, &$apiResultParam, &$apiUrlParam, &$starred, &$apiNoteType, &$apiNoteRaw, &$apiNote, &$apiRequestParamType, &$apiRequestRaw, &$mockRule, &$mockResult, &$mockConfig, &$success_status_code, &$failure_status_code, &$before_inject, &$after_inject, &$response_header,&$apiRestfulParam)
    {
        // if the request params were null, then assign an empty string to them
        if (empty($apiSuccessMock)) {
            $apiSuccessMock = '';
        }
        if (empty($apiFailureMock)) {
            $apiFailureMock = '';
        }
        if (empty($apiRequestRaw)) {
            $apiRequestRaw = '';
        }

        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        $projectDao = new ProjectDao();
        if ($projectID = $groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            // make up a cache json data about the api
            $cacheJson['baseInfo']['apiName'] = $apiName;
            $cacheJson['baseInfo']['apiURI'] = $apiURI;
            $cacheJson['baseInfo']['apiProtocol'] = intval($apiProtocol);
            $cacheJson['baseInfo']['apiRequestType'] = intval($apiRequestType);
            $cacheJson['baseInfo']['apiSuccessMock'] = $apiSuccessMock;
            $cacheJson['baseInfo']['apiFailureMock'] = $apiFailureMock;
            $cacheJson['baseInfo']['apiStatus'] = intval($apiStatus);
            $cacheJson['baseInfo']['starred'] = intval($starred);
            $cacheJson['baseInfo']['apiRequestParamType'] = intval($apiRequestParamType);
            $cacheJson['baseInfo']['apiRequestRaw'] = $apiRequestRaw;
            $cacheJson['restfulParam'] = $apiRestfulParam;
            $updateTime = date("Y-m-d H:i:s", time());
            $cacheJson['responseHeader'] = $response_header;
            $cacheJson['baseInfo']['apiUpdateTime'] = $updateTime;
            $cacheJson['baseInfo']['apiFailureStatusCode'] = $failure_status_code;
            $cacheJson['baseInfo']['apiSuccessStatusCode'] = $success_status_code;
            $cacheJson['baseInfo']['apiNoteType'] = $apiNoteType;
            $cacheJson['baseInfo']['apiNote'] = $apiNote;
            $cacheJson['baseInfo']['apiNoteRaw'] = $apiNoteRaw;

            $cacheJson['headerInfo'] = $apiHeader;
            $cacheJson['mockInfo']['mockRule'] = $mockRule;
            $cacheJson['mockInfo']['mockResult'] = $mockResult;
            $cacheJson['mockInfo']['mockConfig'] = json_decode($mockConfig, TRUE);
            // sort the request params
            // if (is_array($apiRequestParam))
            // {
            // $sortKey = array();
            // foreach ($apiRequestParam as &$param)
            // {
            // $sortKey[] = $param['paramKey'];
            // $param['paramNotNull'] = intval($param['paramNotNull']);
            // $param['paramType'] = intval($param['paramType']);
            // }
            // array_multisort($sortKey, SORT_ASC, $apiRequestParam);
            // }
            $cacheJson['requestInfo'] = $apiRequestParam;
            // sort the result params
            // if (is_array($apiResultParam))
            // {
            // $sortKey = array();
            // foreach ($apiResultParam as &$param)
            // {
            // $sortKey[] = $param['paramKey'];
            // $param['paramNotNull'] = intval($param['paramNotNull']);
            // }
            // array_multisort($sortKey, SORT_ASC, $apiResultParam);
            // }
            $cacheJson['resultInfo'] = $apiResultParam;
			$cacheJson['urlParam'] = $apiUrlParam;
            $cacheJson = json_encode($cacheJson);

            $result = $apiDao->addApi($apiName, $apiURI, $apiProtocol, $apiSuccessMock, $apiFailureMock, $apiRequestType, $apiStatus, $groupID, $apiHeader, $apiRequestParam, $apiResultParam, $starred, $apiNoteType, $apiNoteRaw, $apiNote, $projectID, $apiRequestParamType, $apiRequestRaw, $cacheJson, $updateTime, $_SESSION['userID'], $mockRule, $mockResult, $mockConfig, $success_status_code, $failure_status_code, $before_inject, $after_inject);

            if ($result) {
                $apiDao->addApiHistory($projectID, $groupID, $result['apiID'], $cacheJson, 'Create API', $_SESSION['userID'], $updateTime);
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $result['apiID'], ProjectLogDao::$OP_TYPE_ADD, "Add New API:'{$apiName}'", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return $result;
            }
        } else
            return FALSE;
    }

    /**
     * edit api
     *
     * @param $apiID int
     * @param $apiName string
     * @param $apiURI string
     * @param $apiProtocol int
     * @param $apiSuccessMock string
     * @param $apiFailureMock string
     * @param $apiRequestType int
     * @param $apiStatus int
     * @param $groupID int
     * @param $apiHeader string
     * @param $apiRequestParam string
     * @param $apiResultParam string
     * @param $starred int
     * @param $apiNoteType string
     * @param $apiNoteRaw string
     * @param $apiNote string
     * @param $apiRequestParamType int
     * @param $apiRequestRaw string
     * @param $update_desc string
     * @param $mockRule array
     * @param $mockResult string
     * @param $mockConfig string
     * @param $success_status_code
     * @param $failure_status_code
     * @param $before_inject
     * @param $after_inject
     * @return bool
     */
    public function editApi(&$apiID, &$apiName, &$apiURI, &$apiProtocol, &$apiSuccessMock, &$apiFailureMock, &$apiRequestType, &$apiStatus, &$groupID, &$apiHeader, &$apiRequestParam, &$apiResultParam, &$apiUrlParam, &$starred, &$apiNoteType, &$apiNoteRaw, &$apiNote, &$apiRequestParamType, &$apiRequestRaw, &$update_desc = NULL, &$mockRule, &$mockResult, &$mockConfig, &$success_status_code, &$failure_status_code, &$before_inject, &$after_inject,&$response_header,&$apiRestfulParam)
    {
        // if the request params were null, then assign an empty string to them
        if (empty($apiSuccessMock)) {
            $apiSuccessMock = '';
        }
        if (empty($apiFailureMock)) {
            $apiFailureMock = '';
        }
        if (empty($apiRequestRaw)) {
            $apiRequestRaw = '';
        }

        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        $projectDao = new ProjectDao();
        if ($apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            if ($projectID = $groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
                $projectDao->updateProjectUpdateTime($projectID);
                // make up a cache json data about the api
                $cacheJson['baseInfo']['apiName'] = $apiName;
                $cacheJson['baseInfo']['apiURI'] = $apiURI;
                $cacheJson['baseInfo']['apiProtocol'] = intval($apiProtocol);
                $cacheJson['baseInfo']['apiStatus'] = intval($apiStatus);
                $cacheJson['baseInfo']['apiSuccessMock'] = $apiSuccessMock;
                $cacheJson['baseInfo']['apiFailureMock'] = $apiFailureMock;
                $cacheJson['baseInfo']['apiRequestType'] = intval($apiRequestType);
                $cacheJson['baseInfo']['starred'] = intval($starred);
                $cacheJson['baseInfo']['apiRequestParamType'] = intval($apiRequestParamType);
                $cacheJson['baseInfo']['apiRequestRaw'] = $apiRequestRaw;
                $cacheJson['responseHeader'] = $response_header;
                $cacheJson['restfulParam'] = $apiRestfulParam;
                $updateTime = date("Y-m-d H:i:s", time());
                $cacheJson['baseInfo']['apiUpdateTime'] = $updateTime;
                $cacheJson['baseInfo']['apiFailureStatusCode'] = $failure_status_code;
                $cacheJson['baseInfo']['apiSuccessStatusCode'] = $success_status_code;
                $cacheJson['baseInfo']['apiNoteType'] = $apiNoteType;
                $cacheJson['baseInfo']['apiNote'] = $apiNote;
                $cacheJson['baseInfo']['apiNoteRaw'] = $apiNoteRaw;

                $cacheJson['headerInfo'] = $apiHeader;
                $cacheJson['mockInfo']['mockRule'] = $mockRule;
                $cacheJson['mockInfo']['mockResult'] = $mockResult;
                $cacheJson['mockInfo']['mockConfig'] = json_decode($mockConfig, TRUE);
                // if (is_array($apiRequestParam))
                // {
                // $sortKey = array();
                // foreach ($apiRequestParam as &$param)
                // {
                // $sortKey[] = $param['paramKey'];
                // $param['paramNotNull'] = intval($param['paramNotNull']);
                // $param['paramType'] = intval($param['paramType']);
                // }
                // array_multisort($sortKey, SORT_ASC, $apiRequestParam);
                // }
                $cacheJson['requestInfo'] = $apiRequestParam;
                // if (is_array($apiResultParam))
                // {
                // $sortKey = array();
                // foreach ($apiResultParam as &$param)
                // {
                // $sortKey[] = $param['paramKey'];
                // $param['paramNotNull'] = intval($param['paramNotNull']);
                // }
                // array_multisort($sortKey, SORT_ASC, $apiResultParam);
                // }
                $cacheJson['resultInfo'] = $apiResultParam;
                $cacheJson['urlParam'] = $apiUrlParam;
                $cacheJson = json_encode($cacheJson);

                $result = $apiDao->editApi($apiID, $apiName, $apiURI, $apiProtocol, $apiSuccessMock, $apiFailureMock, $apiRequestType, $apiStatus, $groupID, $apiHeader, $apiRequestParam, $apiResultParam, $starred, $apiNoteType, $apiNoteRaw, $apiNote, $apiRequestParamType, $apiRequestRaw, $cacheJson, $updateTime, $_SESSION['userID'], $mockRule, $mockResult, $mockConfig, $success_status_code, $failure_status_code, $before_inject, $after_inject);

                if ($result) {
                    $desc = $update_desc ? $update_desc : '[Quick Save]Edit API';

                    $apiDao->addApiHistory($projectID, $groupID, $apiID, $cacheJson, $desc, $_SESSION['userID'], $updateTime);
                    $update_desc = $update_desc ? "Edit:'{$apiName}',Update Description：" . $update_desc : "Edit API:'{$apiName}'";

                    $log_dao = new ProjectLogDao();
                    $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $apiID, ProjectLogDao::$OP_TYPE_UPDATE, $update_desc, date("Y-m-d H:i:s", time()));
                    return $result;
                } else {
                    return FALSE;
                }
            } else
                return FALSE;
        } else
            return FALSE;
    }

    /**
     * Delete apis in batches and move them into recycling station
     *
     * @param $apiID int
     * @return bool
     */
    public function removeApi(&$apiID)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectID = $apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $apiDao->removeApi($apiID);
            if ($result) {
                $apiName = $apiDao->getApiName($apiID);
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $apiID, ProjectLogDao::$OP_TYPE_DELETE, "Move:'$apiName'to Recycle Bin", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * recover api
     *
     * @param $apiID int
     * @return bool
     */
    public function recoverApi(&$apiID)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectID = $apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $apiDao->recoverApi($apiID);
            if ($result) {
                $apiName = $apiDao->getApiName($apiID);
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $apiID, ProjectLogDao::$OP_TYPE_OTHERS, "Recover:'$apiName'From Recycle Bin", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * delete api
     *
     * @param $apiID int
     * @return bool
     */
    public function deleteApi(&$apiID)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectID = $apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $apiDao->deleteApi($apiID);
            if ($result) {
                $apiName = $apiDao->getApiName($apiID);
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $apiID, ProjectLogDao::$OP_TYPE_DELETE, "Completely Remove:'$apiName'", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * clean up recycling station
     *
     * @param $projectID int
     * @return bool
     */
    public function cleanRecyclingStation(&$projectID)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $apiDao->cleanRecyclingStation($projectID);
            if ($result) {
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $projectID, ProjectLogDao::$OP_TYPE_DELETE, "Clear Recycle Bin", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * get api list by group and order by apiName
     *
     * @param $groupID int
     * @param $asc int sort order
     * @return bool|array
     */
    public function getApiListOrderByName(&$groupID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        if ($groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getApiListOrderByName($groupID, $asc);
        } else
            return FALSE;
    }

    /**
     * get api list by group and order by update time
     *
     * @param $groupID int
     * @param $asc int
     * @return bool|array
     */
    public function getApiListOrderByTime(&$groupID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        if ($groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getApiListOrderByTime($groupID, $asc);
        } else
            return FALSE;
    }
    /**
     * 获取全部列表
     */
    public function getApiListByCondition(&$project_id, &$group_id, &$condition, &$order_by, &$asc, $conn_ids,$api_status)
    {
    	$asc = $asc ==0 ? 'ASC' : 'DESC';

    	// 判断排序方式
    	switch($order_by)
    	{
    		// 名称排序
    		case 0:
    			$order_by = 'eo_ams_api.apiName ' .$asc;
    			break;
    		case 1:
    			$order_by = 'eo_ams_api.apiUpdateTime ' .$asc;
    			break;
    		case 2:
    			$order_by = 'eo_ams_api.starred ' .$asc;
    			break;
    		case 3:
    			$order_by = 'eo_ams_api.apiID ' .$asc;
    			break;
    	}
    	// 条件
    	switch($condition)
    	{
    		// 无条件
    		case 0:
    			$conditions = '';
    			break;
    			// 有星标
    		case 1:
    			$conditions = ' AND eo_ams_api.starred = 1 ';
    			break;
    		case 4:
    			$conditions = " AND eo_ams_api.apiStatus = {$api_status} ";
    			break;
    		default:
    			$conditions = '';
    			break;
    	}
    	if($conn_ids)
    	{
    		$str = '';
    		$projectDao = new ProjectDao();
    		$user_list = $projectDao->getUserID($project_id, $conn_ids);
    		if($condition ==2 &&! empty($user_list))
    		{
    			for($i = 0; $i <count($user_list); $i ++)
    			{
    				$str .= "eo_ams_api.updateUserID = {$user_list[$i]['userID']}" ." OR ";
    			}
    		}
    		elseif($condition ==3 &&! empty($user_list))
    		{
    			for($i = 0; $i <count($user_list); $i ++)
    			{
    				$str .= "eo_ams_api.createUserID = {$user_list[$i]['userID']}" ." OR ";
    			}
    		}
    		$str = rtrim($str, "OR ");
    		if($str)
    		{
    			$conditions .= " AND (" .$str .")";
    		}
    	}

    	$api_dao = new ApiDao();
    	$api_list = $api_dao->getApiListByCondition($project_id, $group_id, $conditions, $order_by);
    	if($api_list)
    	{
    		$projectDao = new ProjectDao();
    		return $projectDao->handleUserName($api_list, 3);
    	}
    	else
    		return array();
    }
    /**
     * get api list by group and order by starred
     *
     * @param $groupID int
     * @param $asc int
     * @return bool|array
     */
    public function getApiListOrderByStarred(&$groupID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        if ($groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getApiListOrderByStarred($groupID, $asc);
        } else
            return FALSE;
    }

    /**
     * get api list by group and order by URI
     *
     * @param $groupID int
     * @param $asc int
     * @return bool|array
     */
    public function getApiListOrderByUri(&$groupID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        if ($groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getApiListOrderByUri($groupID, $asc);
        } else
            return FALSE;
    }

    /**
     * get api list by group and order by create time
     *
     * @param $groupID int
     * @param $asc int
     * @return bool|array
     */
    public function getApiListOrderByCreateTime(&$groupID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        if ($groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getApiListOrderByCreateTime($groupID, $asc);
        } else
            return FALSE;
    }

    /**
     * get recycling station api list by project and order by apiName
     *
     * @param $projectID int
     * @param $asc int
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByName(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getRecyclingStationApiListOrderByName($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get recycling station api list by project and order by remove time
     *
     * @param $projectID int
     * @param $asc int
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByRemoveTime(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getRecyclingStationApiListOrderByRemoveTime($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get recycling station api list by project and order by starred
     *
     * @param $projectID int
     * @param $asc int
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByStarred(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getRecyclingStationApiListOrderByStarred($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get recycling station api list by project and order by URI
     *
     * @param $projectID int
     * @param $asc int
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByUri(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getRecyclingStationApiListOrderByUri($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get recycling station api list by project and order by create time
     *
     * @param $projectID int
     * @param $asc int
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByCreateTime(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getRecyclingStationApiListOrderByCreateTime($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get api detail
     *
     * @param $apiID int
     * @return array|bool
     */
    public function getApi(&$apiID,&$projectID)
    {
        $apiDao = new ApiDao();
        if ($apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
        	$result = $apiDao->getApi($apiID,$projectID);
            foreach ($result['testHistory'] as &$history) {
                $history['requestInfo'] = json_decode($history['requestInfo'], TRUE);
                $history['resultInfo'] = json_decode($history['resultInfo'], TRUE);
            }
            $result['baseInfo']['apiSuccessMock'] = htmlspecialchars($result['baseInfo']['apiSuccessMock']);
            $result['baseInfo']['apiFailureMock'] = htmlspecialchars($result['baseInfo']['apiFailureMock']);
            return $result;
        } else
            return FALSE;
    }

    /**
     * get all api list by project and order by apiName
     *
     * @param $projectID int
     * @param $asc int
     * @return bool|array
     */
    public function getAllApiListOrderByName(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getAllApiListOrderByName($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get all api list by project and order by apiName
     *
     * @param $projectID int
     * @param $asc int
     * @return bool|array
     */
    public function getAllApiListOrderByTime(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getAllApiListOrderByTime($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get all api list by project and order by URI
     *
     * @param $projectID int
     * @param $asc int
     * @return bool|array
     */
    public function getAllApiListOrderByUri(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getAllApiListOrderByUri($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get all api list by project and order by create time
     *
     * @param $projectID int
     * @param $asc int
     * @return bool|array
     */
    public function getAllApiListOrderByCreateTime(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getAllApiListOrderByCreateTime($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get all api list by project and order by starred
     *
     * @param $projectID int
     * @param $asc int
     * @return bool|array
     */
    public function getAllApiListOrderByStarred(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getAllApiListOrderByStarred($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * search apii
     *
     * @param $tips string
     * @param $projectID int
     * @return bool|array
     */
    public function searchApi(&$tips, &$projectID)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return $apiDao->searchApi($tips, $projectID);
        } else
            return FALSE;
    }

    /**
     * add star
     *
     * @param $apiID int
     * @return bool
     */
    public function addStar(&$apiID)
    {
        $apiDao = new ApiDao();
        if ($projectID = $apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            $projectDao = new ProjectDao();
            $projectDao->updateProjectUpdateTime($projectID);
            return $apiDao->addStar($apiID);
        } else
            return FALSE;
    }

    /**
     * remove star
     *
     * @param $apiID int
     * @return bool
     */
    public function removeStar(&$apiID)
    {
        $apiDao = new ApiDao();
        if ($projectID = $apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            $projectDao = new ProjectDao();
            $projectDao->updateProjectUpdateTime($projectID);
            return $apiDao->removeStar($apiID);
        } else
            return FALSE;
    }

    /**
     * Remove apis in batches from recycling station
     *
     * @param $projectID int
     * @param $apiIDs string
     * @return bool
     */
    public function deleteApis(&$projectID, &$apiIDs)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $apiName = $apiDao->getApiName($apiIDs);
            $result = $apiDao->deleteApis($projectID, $apiIDs);
            if ($result) {
                $projectDao->updateProjectUpdateTime($projectID);
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $apiIDs, ProjectLogDao::$OP_TYPE_DELETE, "Completely Remove API:'$apiName'", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * Delete apis in batches and move them into recycling station
     *
     * @param $projectID int
     * @param $apiIDs string
     * @return bool
     */
    public function removeApis(&$projectID, &$apiIDs)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $apiDao->removeApis($projectID, $apiIDs);
            if ($result) {
                $apiName = $apiDao->getApiName($apiIDs);
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $apiIDs, ProjectLogDao::$OP_TYPE_DELETE, "Move:'$apiName'to Recycle Bin", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * Recover api in batches
     *
     * @param $groupID int
     * @param $apiIDs string
     * @return bool
     */
    public function recoverApis(&$groupID, &$apiIDs)
    {
        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        $projectDao = new ProjectDao();
        if ($projectID = $groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $apiDao->recoverApis($groupID, $apiIDs);
            if ($result) {
                $apiName = $apiDao->getApiName($apiIDs);
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $apiIDs, ProjectLogDao::$OP_TYPE_OTHERS, "Recover:'$apiName'From Recycle Bin", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * get API history list
     * @param $api_id
     * @return array|bool
     */
    public function getApiHistoryList(&$api_id)
    {
        $dao = new ApiDao();
        if ($dao->checkApiPermission($api_id, $_SESSION['userID'])) {
            $api_history_list = $dao->getApiHistoryList($api_id, 10);

            $result = array();
            $result['apiHistoryList'] = $api_history_list ? $api_history_list : array();
            $result['apiName'] = $dao->getApiName($api_id);
            return $result;
        } else {
            return FALSE;
        }
    }

    /**
     * delete api history
     * @param $api_id
     * @param $api_history_id
     * @return bool
     */
    public function deleteApiHistory(&$api_id, &$api_history_id)
    {
        $user_id = $_SESSION['userID'];
        $api_dao = new ApiDao();
        if ($project_id = $api_dao->checkApiPermission($api_id, $user_id)) {
            if ($api_dao->deleteApiHistory($api_history_id, $api_id)) {
                $api_name = $api_dao->getApiName($api_id);

                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_API, $api_id, ProjectLogDao::$OP_TYPE_DELETE, "Delete'$api_name'Version", date("Y-m-d H:i:s", time()));

                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * switch history version
     * @param $api_id
     * @param $api_history_id
     * @return bool
     */
    public function toggleApiHistory(&$api_id, &$api_history_id)
    {
        $user_id = $_SESSION['userID'];
        $api_dao = new ApiDao();
        if ($project_id = $api_dao->checkApiPermission($api_id, $user_id)) {
            if ($api_dao->toggleApiHistory($api_id, $api_history_id)) {
                $api_name = $api_dao->getApiName($api_id);
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_API, $api_id, ProjectLogDao::$OP_TYPE_UPDATE, "Switch'$api_name'Version", date("Y-m-d H:i:s", time()));

                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }




    /**
     * change Api group
     * @param $api_ids
     * @param $group_id
     * @return bool
     */
    public function changeApiGroup(&$api_ids, &$group_id)
    {
        $group_dao = new GroupDao();
        if (!($project_id = $group_dao->checkGroupPermission($group_id, $_SESSION['userID']))) {
            return FALSE;
        }
        $dao = new ApiDao();
        return $dao->changeApiGroup($api_ids, $project_id, $group_id);
    }

    /**
     * Batch export API
     * @param $project_id
     * @param $api_ids
     * @return bool|string
     */
    public function exportApi(&$project_id, &$api_ids)
    {
        $dao = new ApiDao();
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $_SESSION['userID'])) {
            return FALSE;
        }
        $result = $dao->getApiData($project_id, $api_ids);
        if ($result) {
            $fileName = 'eoLinker_api_export_' . $_SESSION['userName'] . '_' . time() . '.export';
            if (file_put_contents(realpath('./dump') . DIRECTORY_SEPARATOR . $fileName, json_encode($result))) {
                $api_name = $dao->getApiName($api_ids);
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $project_id, ProjectLogDao::$OP_TYPE_OTHERS, "Batch Export API：$api_name", date("Y-m-d H:i:s", time()));
                return $fileName;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * Batch import API
     * @param $group_id
     * @param $data
     * @return bool
     */
    public function importApi(&$group_id, &$data)
    {
        $group_dao = new GroupDao();
        if (!($project_id = $group_dao->checkGroupPermission($group_id, $_SESSION['userID']))) {
            return FALSE;
        }
        $dao = new ApiDao();
        $result = $dao->importApi($group_id, $project_id, $data, $_SESSION['userID']);
        if ($result) {
            $names = array();
            foreach ($data as $api) {
                $names[] = $api['baseInfo']['apiName'];
            }
            $api_name = implode(",", $names);
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $group_id, ProjectLogDao::$OP_TYPE_OTHERS, "Batch Import API：$api_name", date("Y-m-d H:i:s", time()));
            return $result;
        } else {
            return FALSE;
        }
    }
    /**
     * updateApiStatus
     */
    public function updateApiStatus(&$projectID, &$api_ids, &$api_status)
    {
    	$dao = new ApiDao();
    	$api_name = $dao->getApiNames($api_ids);
    	$result = $dao->updateApiStatusToDB($api_ids, $api_status);
    	if($result)
    	{
    		// 将操作写入日志
    		$log_dao = new ProjectLogDao();
    		$log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $group_id, ProjectLogDao::$OP_TYPE_OTHERS, "Update API Status：$api_name", date("Y-m-d H:i:s", time()));
    		return $result;
    	}
    	else
    		return FALSE;
    }
}

?>
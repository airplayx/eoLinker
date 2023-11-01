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

class GroupDao
{
    /**
     * Add API group
     * @param $projectID int projectID
     * @param $groupName string group name
     * @return bool
     */
    public function addGroup(&$projectID, &$groupName)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_ams_api_group (eo_ams_api_group.groupName,eo_ams_api_group.projectID) VALUES (?,?);', array(
            $groupName,
            $projectID
        ));

        $groupID = $db->getLastInsertID();

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $groupID;

    }

    /**
     * Add child group
     * @param $projectID int projectID
     * @param $groupName string group name
     * @param $parentGroupID int parent groupID
     * @param $isChild
     * @return bool
     */
    public function addChildGroup(&$projectID, &$groupName, &$parentGroupID, &$isChild)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_ams_api_group (eo_ams_api_group.groupName,eo_ams_api_group.projectID,eo_ams_api_group.parentGroupID,eo_ams_api_group.isChild) VALUES (?,?,?,?);', array(
            $groupName,
            $projectID,
            $parentGroupID,
            $isChild
        ));

        $groupID = $db->getLastInsertID();

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $groupID;
    }

    /**
     * Delete group
     * @param $groupID int ID
     * @return bool
     */
    public function deleteGroup(&$groupID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT GROUP_CONCAT(GroupID) AS groups FROM eo_ams_api_group WHERE groupID = ? OR parentGroupID = ? OR parentGroupID IN (SELECT groupID FROM eo_ams_api_group WHERE parentGroupID = ?)', array(
            $groupID,
            $groupID,
            $groupID
        ));
        $groups = $result['groups'];
        $db->prepareExecuteAll("DELETE FROM eo_ams_api_group WHERE eo_ams_api_group.groupID IN ({$groups});", array());
        $result = $db->getAffectRow();
        if ($result > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Get group list
     * @param $projectID int projectID
     * @return bool
     */
    public function getGroupList(&$projectID)
    {
        $db = getDatabase();
        $groupList = $db->prepareExecuteAll('SELECT eo_ams_api_group.groupID,eo_ams_api_group.groupName FROM eo_ams_api_group WHERE projectID = ? AND isChild = 0 ORDER BY eo_ams_api_group.groupID DESC;', array($projectID));

        if (is_array($groupList))
            foreach ($groupList as &$parentGroup) {
                $parentGroup['childGroupList'] = array();
                $childGroupList = $db->prepareExecuteAll('SELECT eo_ams_api_group.groupID,eo_ams_api_group.groupName,eo_ams_api_group.parentGroupID FROM eo_ams_api_group WHERE projectID = ? AND isChild = 1 AND parentGroupID = ? ORDER BY eo_ams_api_group.groupID DESC;', array(
                    $projectID,
                    $parentGroup['groupID']
                ));

                foreach ($childGroupList as &$childGroup) {
                    $secondChildGroupList = $db->prepareExecuteAll('SELECT eo_ams_api_group.groupID,eo_ams_api_group.groupName,eo_ams_api_group.parentGroupID FROM eo_ams_api_group WHERE projectID = ? AND isChild = 2 AND parentGroupID = ? ORDER BY eo_ams_api_group.groupID DESC;', array(
                        $projectID,
                        $childGroup['groupID']
                    ));
                    if (!empty($secondChildGroupList)) {
                        $childGroup['childGroupList'] = $secondChildGroupList;
                    } else {
                        $childGroup['childGroupList'] = array();
                    }
                }
                if (!empty($childGroupList)) {
                    $parentGroup['childGroupList'] = $childGroupList;
                } else {
                    $parentGroup['childGroupList'] = array();
                }
            }

        if (empty($groupList))
            return FALSE;
        else
            return $groupList;
    }

    /**
     * edit API group
     * @param $groupID int groupID
     * @param $groupName string parent group name
     * @param $parentGroupID int parent groupID
     * @param $isChild
     * @return bool
     */
    public function editGroup(&$groupID, &$groupName, &$parentGroupID, &$isChild)
    {
        $db = getDatabase();

        if (!$parentGroupID) {
            $db->prepareExecute('UPDATE eo_ams_api_group SET eo_ams_api_group.groupName = ?,eo_ams_api_group.isChild = 0 WHERE eo_ams_api_group.groupID = ?;', array(
                $groupName,
                $groupID
            ));
        } else {
            $db->prepareExecute('UPDATE eo_ams_api_group SET eo_ams_api_group.groupName = ?,eo_ams_api_group.parentGroupID = ?,eo_ams_api_group.isChild = ? WHERE eo_ams_api_group.groupID = ?;', array(
                $groupName,
                $parentGroupID,
                $isChild,
                $groupID
            ));
        }

        if ($db->getAffectRow() > 0)
            return TRUE;
        else

            return FALSE;

    }

    /**
     * Check Group Permissio
     * @param $groupID int ID
     * @param $userID int ID
     * @return bool
     */
    public function checkGroupPermission(&$groupID, &$userID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_ams_conn_project.projectID FROM eo_ams_conn_project INNER JOIN eo_ams_api_group ON eo_ams_api_group.projectID = eo_ams_conn_project.projectID WHERE userID = ? AND groupID = ?;', array(
            $userID,
            $groupID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }

    /**
     * Update Sort Group
     * @param $projectID int ID
     * @param $orderList string
     * @return bool
     */
    public function sortGroup(&$projectID, &$orderList)
    {
        $db = getDatabase();
        $db->prepareExecute('REPLACE INTO eo_ams_api_group_order(projectID, orderList) VALUES (?,?);', array(
            $projectID,
            $orderList
        ));
        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * get group order
     * @param $projectID int projectID
     * @return bool
     */
    public function getGroupOrderList(&$projectID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_ams_api_group_order.orderList FROM eo_ams_api_group_order WHERE eo_ams_api_group_order.projectID = ?;', array(
            $projectID
        ));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['orderList'];
        }
    }

    /**
     * get group name
     * @param $group_id
     * @return bool
     */
    public function getGroupName(&$group_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_ams_api_group.groupName FROM eo_ams_api_group WHERE eo_ams_api_group.groupID = ?;', array($group_id));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['groupName'];
        }
    }

    /**
     * get related data
     * @param $group_id
     * @return array|bool
     */
    public function getGroupData(&$group_id)
    {
        $db = getDatabase();
        $result = array();
        $group_info = $db->prepareExecute('SELECT eo_ams_api_group.groupName,eo_ams_api_group.isChild FROM eo_ams_api_group WHERE eo_ams_api_group.groupID = ?;', array(
            $group_id
        ));
        $api_list = $db->prepareExecuteAll("SELECT eo_ams_api_cache.apiID,eo_ams_api_cache.apiJson,eo_ams_api_cache.starred FROM eo_ams_api_cache INNER JOIN eo_ams_api ON eo_ams_api.apiID = eo_ams_api_cache.apiID WHERE eo_ams_api_cache.groupID = ? AND eo_ams_api.removed = 0;", array(
            $group_id
        ));
        $result['groupName'] = $group_info['groupName'];
        if (is_array($api_list)) {
            $j = 0;
            foreach ($api_list as $api) {
                $result['apiList'][$j] = json_decode($api['apiJson'], TRUE);
                $result['apiList'][$j]['baseInfo']['starred'] = $api['starred'];
                ++$j;
            }
        }
        if ($group_info['isChild'] <= 1) {
            $child_group_list = $db->prepareExecuteAll('SELECT eo_ams_api_group.groupID,eo_ams_api_group.groupName FROM eo_ams_api_group WHERE eo_ams_api_group.parentGroupID = ?', array(
                $group_id
            ));
            if ($child_group_list) {
                $i = 0;
                foreach ($child_group_list as $group) {
                    $result['childGroupList'][$i]['groupName'] = $group['groupName'];
                    $api_list = $db->prepareExecuteAll("SELECT eo_ams_api_cache.apiID,eo_ams_api_cache.apiJson,eo_ams_api_cache.starred FROM eo_ams_api_cache INNER JOIN eo_ams_api ON eo_ams_api.apiID = eo_ams_api_cache.apiID WHERE eo_ams_api_cache.groupID = ? AND eo_ams_api.removed = 0;", array(
                        $group['groupID']
                    ));
                    if (is_array($api_list)) {
                        $j = 0;
                        foreach ($api_list as $api) {
                            $result['childGroupList'][$i]['apiList'][$j] = json_decode($api['apiJson'], TRUE);
                            $result['childGroupList'][$i]['apiList'][$j]['baseInfo']['starred'] = $api['starred'];
                            ++$j;
                        }
                    }
                    $group_list = $db->prepareExecuteAll('SELECT eo_ams_api_group.groupID,eo_ams_api_group.groupName FROM eo_ams_api_group WHERE eo_ams_api_group.parentGroupID = ?;', array(
                        $group['groupID']
                    ));
                    if ($group_list) {
                        $k = 0;
                        foreach ($group_list as $data) {
                            $result['childGroupList'][$i]['childGroupList'][$k]['groupName'] = $data['groupName'];
                            $api_list = $db->prepareExecuteAll('SELECT eo_ams_api_cache.apiID,eo_ams_api_cache.apiJson,eo_ams_api_cache.starred FROM eo_ams_api_cache INNER JOIN eo_ams_api ON eo_ams_api.apiID = eo_ams_api_cache.apiID WHERE eo_ams_api_cache.groupID = ? AND eo_ams_api.removed = 0;', array(
                                $data['groupID']
                            ));
                            if (is_array($api_list)) {
                                $l = 0;
                                foreach ($api_list as $api) {
                                    $result['childGroupList'][$i]['childGroupList'][$k]['apiList'][$l] = json_decode($api['apiJson'], TRUE);
                                    $result['childGroupList'][$i]['childGroupList'][$k]['apiList'][$l]['baseInfo']['starred'] = $api['starred'];
                                    $l++;
                                }
                            }
                            $k++;
                        }

                    }
                    $i++;
                }
            }
        }
        if ($result)
            return $result;
        else
            return FALSE;
    }

    /**
     * import API group
     * @param $project_id
     * @param $user_id
     * @param $data
     * @return bool
     */
    public function importGroup(&$project_id, &$user_id, &$data)
    {
        $db = getDatabase();
        try {
            $db->beginTransaction();
            $db->prepareExecute('INSERT INTO eo_ams_api_group (eo_ams_api_group.groupName,eo_ams_api_group.projectID) VALUES (?,?);', array(
                $data['groupName'],
                $project_id
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("addGroup error");

            $group_id = $db->getLastInsertID();
            if ($data['apiList']) {
                foreach ($data['apiList'] as $api) {
                    
                    $db->prepareExecute('INSERT INTO eo_ams_api (eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiProtocol,eo_ams_api.apiSuccessMock,eo_ams_api.apiFailureMock,eo_ams_api.apiRequestType,eo_ams_api.apiStatus,eo_ams_api.groupID,eo_ams_api.projectID,eo_ams_api.starred,eo_ams_api.apiNoteType,eo_ams_api.apiNoteRaw,eo_ams_api.apiNote,eo_ams_api.apiRequestParamType,eo_ams_api.apiRequestRaw,eo_ams_api.apiUpdateTime,eo_ams_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                        $api['baseInfo']['apiName'],
                        $api['baseInfo']['apiURI'],
                        $api['baseInfo']['apiProtocol'],
                        $api['baseInfo']['apiSuccessMock'],
                        $api['baseInfo']['apiFailureMock'],
                        $api['baseInfo']['apiRequestType'],
                        $api['baseInfo']['apiStatus'],
                        $group_id,
                        $project_id,
                        $api['baseInfo']['starred'],
                        $api['baseInfo']['apiNoteType'],
                        $api['baseInfo']['apiNoteRaw'],
                        $api['baseInfo']['apiNote'],
                        $api['baseInfo']['apiRequestParamType'],
                        $api['baseInfo']['apiRequestRaw'],
                        $api['baseInfo']['apiUpdateTime'],
                        $user_id
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addApi error");

                    $api_id = $db->getLastInsertID();

                    
                    foreach ($api['headerInfo'] as $header) {
                        $db->prepareExecute('INSERT INTO eo_ams_api_header (eo_ams_api_header.headerName,eo_ams_api_header.headerValue,eo_ams_api_header.apiID) VALUES (?,?,?);', array(
                            $header['headerName'],
                            $header['headerValue'],
                            $api_id
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addHeader error");
                    }

                    
                    foreach ($api['requestInfo'] as $request) {
                        $db->prepareExecute('INSERT INTO eo_ams_api_request_param (eo_ams_api_request_param.apiID,eo_ams_api_request_param.paramName,eo_ams_api_request_param.paramKey,eo_ams_api_request_param.paramValue,eo_ams_api_request_param.paramLimit,eo_ams_api_request_param.paramNotNull,eo_ams_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                            $api_id,
                            $request['paramName'],
                            $request['paramKey'],
                            $request['paramValue'],
                            $request['paramLimit'],
                            $request['paramNotNull'],
                            $request['paramType']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addRequestParam error");

                        $param_id = $db->getLastInsertID();

                        foreach ($request['paramValueList'] as $value) {
                            $db->prepareExecute('INSERT INTO eo_ams_api_request_value (eo_ams_api_request_value.paramID,eo_ams_api_request_value.`value`,eo_ams_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                $param_id,
                                $value['value'],
                                $value['valueDescription']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addApi error");
                        };
                    };

                    
                    foreach ($api['resultInfo'] as $result) {
                        $db->prepareExecute('INSERT INTO eo_ams_api_result_param (eo_ams_api_result_param.apiID,eo_ams_api_result_param.paramName,eo_ams_api_result_param.paramKey,eo_ams_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                            $api_id,
                            $result['paramName'],
                            $result['paramKey'],
                            $result['paramNotNull']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addResultParam error");

                        $param_id = $db->getLastInsertID();

                        foreach ($result['paramValueList'] as $value) {
                            $db->prepareExecute('INSERT INTO eo_ams_api_result_value (eo_ams_api_result_value.paramID,eo_ams_api_result_value.`value`,eo_ams_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                $param_id,
                                $value['value'],
                                $value['valueDescription']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addApi error");
                        };
                    };

                    
                    $db->prepareExecute("INSERT INTO eo_ams_api_cache (eo_ams_api_cache.projectID,eo_ams_api_cache.groupID,eo_ams_api_cache.apiID,eo_ams_api_cache.apiJson,eo_ams_api_cache.starred) VALUES (?,?,?,?,?);", array(
                        $project_id,
                        $group_id,
                        $api_id,
                        json_encode($api),
                        $api['baseInfo']['starred']
                    ));

                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("addApiCache error");
                    }
                }
            }
            
            if ($data['childGroupList']) {
                $group_parent_id = $group_id;
                foreach ($data['childGroupList'] as $api_group_child) {
                    $db->prepareExecute('INSERT INTO eo_ams_api_group (eo_ams_api_group.groupName,eo_ams_api_group.projectID,eo_ams_api_group.parentGroupID, eo_ams_api_group.isChild) VALUES (?,?,?,?);', array(
                        $api_group_child['groupName'],
                        $project_id,
                        $group_parent_id,
                        1
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addChildGroup error");

                    $group_id = $db->getLastInsertID();

                    
                    if (empty($api_group_child['apiList']))
                        continue;

                    foreach ($api_group_child['apiList'] as $api) {
                        
                        $db->prepareExecute('INSERT INTO eo_ams_api (eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiProtocol,eo_ams_api.apiSuccessMock,eo_ams_api.apiFailureMock,eo_ams_api.apiRequestType,eo_ams_api.apiStatus,eo_ams_api.groupID,eo_ams_api.projectID,eo_ams_api.starred,eo_ams_api.apiNoteType,eo_ams_api.apiNoteRaw,eo_ams_api.apiNote,eo_ams_api.apiRequestParamType,eo_ams_api.apiRequestRaw,eo_ams_api.apiUpdateTime,eo_ams_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                            $api['baseInfo']['apiName'],
                            $api['baseInfo']['apiURI'],
                            $api['baseInfo']['apiProtocol'],
                            $api['baseInfo']['apiSuccessMock'],
                            $api['baseInfo']['apiFailureMock'],
                            $api['baseInfo']['apiRequestType'],
                            $api['baseInfo']['apiStatus'],
                            $group_id,
                            $project_id,
                            $api['baseInfo']['starred'],
                            $api['baseInfo']['apiNoteType'],
                            $api['baseInfo']['apiNoteRaw'],
                            $api['baseInfo']['apiNote'],
                            $api['baseInfo']['apiRequestParamType'],
                            $api['baseInfo']['apiRequestRaw'],
                            $api['baseInfo']['apiUpdateTime'],
                            $user_id
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addChildApi error");

                        $api_id = $db->getLastInsertID();

                        
                        foreach ($api['headerInfo'] as $header) {
                            $db->prepareExecute('INSERT INTO eo_ams_api_header (eo_ams_api_header.headerName,eo_ams_api_header.headerValue,eo_ams_api_header.apiID) VALUES (?,?,?);', array(
                                $header['headerName'],
                                $header['headerValue'],
                                $api_id
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addChildHeader error");
                        }

                        
                        foreach ($api['requestInfo'] as $request) {
                            $db->prepareExecute('INSERT INTO eo_ams_api_request_param (eo_ams_api_request_param.apiID,eo_ams_api_request_param.paramName,eo_ams_api_request_param.paramKey,eo_ams_api_request_param.paramValue,eo_ams_api_request_param.paramLimit,eo_ams_api_request_param.paramNotNull,eo_ams_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                                $api_id,
                                $request['paramName'],
                                $request['paramKey'],
                                $request['paramValue'],
                                $request['paramLimit'],
                                $request['paramNotNull'],
                                $request['paramType']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addChildRequestParam error");

                            $param_id = $db->getLastInsertID();
                            if ($request['paramValueList']) {
                                foreach ($request['paramValueList'] as $value) {
                                    $db->prepareExecute('INSERT INTO eo_ams_api_request_value (eo_ams_api_request_value.paramID,eo_ams_api_request_value.`value`,eo_ams_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                        $param_id,
                                        $value['value'],
                                        $value['valueDescription']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildApi error");
                                };
                            }
                        };

                        
                        foreach ($api['resultInfo'] as $result) {
                            $db->prepareExecute('INSERT INTO eo_ams_api_result_param (eo_ams_api_result_param.apiID,eo_ams_api_result_param.paramName,eo_ams_api_result_param.paramKey,eo_ams_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                                $api_id,
                                $result['paramName'],
                                $result['paramKey'],
                                $result['paramNotNull']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addChildResultParam error");

                            $param_id = $db->getLastInsertID();
                            if ($result['paramValueList']) {
                                foreach ($result['paramValueList'] as $value) {
                                    $db->prepareExecute('INSERT INTO eo_ams_api_result_value (eo_ams_api_result_value.paramID,eo_ams_api_result_value.`value`,eo_ams_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                        $param_id,
                                        $value['value'],
                                        $value['valueDescription']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildParamValue error");
                                };
                            }
                        };

                        
                        $db->prepareExecute("INSERT INTO eo_ams_api_cache (eo_ams_api_cache.projectID,eo_ams_api_cache.groupID,eo_ams_api_cache.apiID,eo_ams_api_cache.apiJson,eo_ams_api_cache.starred) VALUES (?,?,?,?,?);", array(
                            $project_id,
                            $group_id,
                            $api_id,
                            json_encode($api),
                            $api['baseInfo']['starred']
                        ));

                        if ($db->getAffectRow() < 1) {
                            throw new \PDOException("addChildApiCache error");
                        }
                    }
                    if ($api_group_child['childGroupList']) {
                        $parent_id = $group_id;
                        foreach ($api_group_child['childGroupList'] as $group) {
                            $db->prepareExecute('INSERT INTO eo_ams_api_group (eo_ams_api_group.groupName,eo_ams_api_group.projectID,eo_ams_api_group.parentGroupID, eo_ams_api_group.isChild) VALUES (?,?,?,?);', array(
                                $group['groupName'],
                                $project_id,
                                $parent_id,
                                2
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addChildGroup error");

                            $group_id = $db->getLastInsertID();

                            
                            if (empty($group['apiList']))
                                continue;

                            foreach ($group['apiList'] as $api) {
                                
                                $db->prepareExecute('INSERT INTO eo_ams_api (eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiProtocol,eo_ams_api.apiSuccessMock,eo_ams_api.apiFailureMock,eo_ams_api.apiRequestType,eo_ams_api.apiStatus,eo_ams_api.groupID,eo_ams_api.projectID,eo_ams_api.starred,eo_ams_api.apiNoteType,eo_ams_api.apiNoteRaw,eo_ams_api.apiNote,eo_ams_api.apiRequestParamType,eo_ams_api.apiRequestRaw,eo_ams_api.apiUpdateTime,eo_ams_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                                    $api['baseInfo']['apiName'],
                                    $api['baseInfo']['apiURI'],
                                    $api['baseInfo']['apiProtocol'],
                                    $api['baseInfo']['apiSuccessMock'],
                                    $api['baseInfo']['apiFailureMock'],
                                    $api['baseInfo']['apiRequestType'],
                                    $api['baseInfo']['apiStatus'],
                                    $group_id,
                                    $project_id,
                                    $api['baseInfo']['starred'],
                                    $api['baseInfo']['apiNoteType'],
                                    $api['baseInfo']['apiNoteRaw'],
                                    $api['baseInfo']['apiNote'],
                                    $api['baseInfo']['apiRequestParamType'],
                                    $api['baseInfo']['apiRequestRaw'],
                                    $api['baseInfo']['apiUpdateTime'],
                                    $user_id
                                ));

                                if ($db->getAffectRow() < 1)
                                    throw new \PDOException("addChildApi error");

                                $api_id = $db->getLastInsertID();

                               
                                foreach ($api['headerInfo'] as $header) {
                                    $db->prepareExecute('INSERT INTO eo_ams_api_header (eo_ams_api_header.headerName,eo_ams_api_header.headerValue,eo_ams_api_header.apiID) VALUES (?,?,?);', array(
                                        $header['headerName'],
                                        $header['headerValue'],
                                        $api_id
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildHeader error");
                                }

                                
                                foreach ($api['requestInfo'] as $request) {
                                    $db->prepareExecute('INSERT INTO eo_ams_api_request_param (eo_ams_api_request_param.apiID,eo_ams_api_request_param.paramName,eo_ams_api_request_param.paramKey,eo_ams_api_request_param.paramValue,eo_ams_api_request_param.paramLimit,eo_ams_api_request_param.paramNotNull,eo_ams_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                                        $api_id,
                                        $request['paramName'],
                                        $request['paramKey'],
                                        $request['paramValue'],
                                        $request['paramLimit'],
                                        $request['paramNotNull'],
                                        $request['paramType']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildRequestParam error");

                                    $param_id = $db->getLastInsertID();
                                    if ($request['paramValueList']) {
                                        foreach ($request['paramValueList'] as $value) {
                                            $db->prepareExecute('INSERT INTO eo_ams_api_request_value (eo_ams_api_request_value.paramID,eo_ams_api_request_value.`value`,eo_ams_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                                $param_id,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildApi error");
                                        };
                                    }
                                };

                               
                                foreach ($api['resultInfo'] as $result) {
                                    $db->prepareExecute('INSERT INTO eo_ams_api_result_param (eo_ams_api_result_param.apiID,eo_ams_api_result_param.paramName,eo_ams_api_result_param.paramKey,eo_ams_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                                        $api_id,
                                        $result['paramName'],
                                        $result['paramKey'],
                                        $result['paramNotNull']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildResultParam error");

                                    $param_id = $db->getLastInsertID();
                                    if ($result['paramValueList']) {
                                        foreach ($result['paramValueList'] as $value) {
                                            $db->prepareExecute('INSERT INTO eo_ams_api_result_value (eo_ams_api_result_value.paramID,eo_ams_api_result_value.`value`,eo_ams_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                                $param_id,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildParamValue error");
                                        };
                                    }
                                };

                               
                                $db->prepareExecute("INSERT INTO eo_ams_api_cache (eo_ams_api_cache.projectID,eo_ams_api_cache.groupID,eo_ams_api_cache.apiID,eo_ams_api_cache.apiJson,eo_ams_api_cache.starred) VALUES (?,?,?,?,?);", array(
                                    $project_id,
                                    $group_id,
                                    $api_id,
                                    json_encode($api),
                                    $api['baseInfo']['starred']
                                ));

                                if ($db->getAffectRow() < 1) {
                                    throw new \PDOException("addChildApiCache error");
                                }
                            }
                        }
                    }
                }
            }
            $db->commit();
            return TRUE;
        } catch (\Exception $e) {
            $db->rollback();
            return FALSE;
        }
    }
}

?>
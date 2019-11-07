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
class GroupDao
{
    /**
     * 添加项目api分组
     * @param $projectID int 项目ID
     * @param $groupName string 分组名称
     * @return bool
     */
    public function addGroup(&$projectID, &$groupName)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID) VALUES (?,?);', array(
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
     * 添加子分组
     * @param $projectID int 项目ID
     * @param $groupName string 分组名称
     * @param $parentGroupID int 父分组ID
     * @return bool
     */
    public function addChildGroup(&$projectID, &$groupName, &$parentGroupID)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID,eo_api_group.parentGroupID,eo_api_group.isChild) VALUES (?,?,?,1);', array(
            $groupName,
            $projectID,
            $parentGroupID
        ));

        $groupID = $db->getLastInsertID();

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $groupID;
    }

    /**
     * 删除项目api分组
     * @param $groupID int 项目ID
     * @return bool
     */
    public function deleteGroup(&$groupID)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_api_group WHERE (eo_api_group.groupID = ? OR eo_api_group.parentGroupID = ?);', array($groupID, $groupID));
        $result = $db->getAffectRow();
        $db->prepareExecute('UPDATE eo_api SET eo_api.removed = 1 WHERE eo_api.groupID = ?;', array($groupID));

        if ($result > 0)
            return TRUE;
        else
            return FALSE;

    }

    /**
     * 获取项目api分组
     * @param $projectID int 项目ID
     * @return bool
     */
    public function getGroupList(&$projectID)
    {
        $db = getDatabase();
        $groupList = $db->prepareExecuteAll('SELECT eo_api_group.groupID,eo_api_group.groupName FROM eo_api_group WHERE projectID = ? AND isChild = 0 ORDER BY eo_api_group.groupID DESC;', array($projectID));

        if (is_array($groupList))
            foreach ($groupList as &$parentGroup) {
                $parentGroup['childGroupList'] = array();
                $childGroup = $db->prepareExecuteAll('SELECT eo_api_group.groupID,eo_api_group.groupName,eo_api_group.parentGroupID FROM eo_api_group WHERE projectID = ? AND isChild = 1 AND parentGroupID = ? ORDER BY eo_api_group.groupID DESC;', array(
                    $projectID,
                    $parentGroup['groupID']
                ));
                
                //判断是否有子分组
                if (!empty($childGroup))
                    $parentGroup['childGroupList'] = $childGroup;
            }

        if (empty($groupList))
            return FALSE;
        else
            return $groupList;
    }

    /**
     * 修改项目api分组
     * @param $groupID int 分组ID
     * @param $groupName string 分组名称
     * @param $parentGroupID int 父分组ID
     * @return bool
     */
    public function editGroup(&$groupID, &$groupName, &$parentGroupID)
    {
        $db = getDatabase();

        if (!$parentGroupID) {
            $db->prepareExecute('UPDATE eo_api_group SET eo_api_group.groupName = ?,eo_api_group.isChild = 0 WHERE eo_api_group.groupID = ?;', array(
                $groupName,
                $groupID
            ));
        } else {
            $db->prepareExecute('UPDATE eo_api_group SET eo_api_group.groupName = ?,eo_api_group.parentGroupID = ?,eo_api_group.isChild = 1 WHERE eo_api_group.groupID = ?;', array(
                $groupName,
                $parentGroupID,
                $groupID
            ));
        }

        if ($db->getAffectRow() > 0)
            return TRUE;
        else

            return FALSE;

    }

    /**
     * 判断分组和用户是否匹配
     * @param $groupID int 分组ID
     * @param $userID int 用户ID
     * @return bool
     */
    public function checkGroupPermission(&$groupID, &$userID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_conn_project.projectID FROM eo_conn_project INNER JOIN eo_api_group ON eo_api_group.projectID = eo_conn_project.projectID WHERE userID = ? AND groupID = ?;', array(
            $userID,
            $groupID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }

    /**
     * 更新分组排序
     * @param $projectID int 项目ID
     * @param $orderList string 排序列表
     * @return bool
     */
    public function sortGroup(&$projectID, &$orderList)
    {
        $db = getDatabase();
        $db->prepareExecute('REPLACE INTO eo_api_group_order(projectID, orderList) VALUES (?,?);', array(
            $projectID,
            $orderList
        ));
        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取分组排序列表
     * @param $projectID int 项目ID
     * @return bool
     */
    public function getGroupOrderList(&$projectID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_api_group_order.orderList FROM eo_api_group_order WHERE eo_api_group_order.projectID = ?;', array(
            $projectID
        ));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['orderList'];
        }
    }

    /**
     * 获取分组名称
     * @param $group_id
     * @return bool
     */
    public function getGroupName(&$group_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_api_group.groupName FROM eo_api_group WHERE eo_api_group.groupID = ?;', array($group_id));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['groupName'];
        }
    }

    /**
     * 获取分组相关数据
     * @param $group_id
     * @return array|bool
     */
    public function getGroupData(&$group_id)
    {
        $db = getDatabase();
        $result = array();
        $group = $db->prepareExecute('SELECT eo_api_group.groupName,eo_api_group.isChild FROM eo_api_group WHERE eo_api_group.groupID = ?;', array(
            $group_id
        ));
        $api_list = $db->prepareExecuteAll("SELECT eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred FROM eo_api_cache INNER JOIN eo_api ON eo_api.apiID = eo_api_cache.apiID WHERE eo_api_cache.groupID = ? AND eo_api.removed = 0;", array(
            $group_id
        ));
        $result['groupName'] = $group['groupName'];
        if (is_array($api_list)) {
            $j = 0;
            foreach ($api_list as $api) {
                $result['apiList'][$j] = json_decode($api['apiJson'], TRUE);
                $result['apiList'][$j]['baseInfo']['starred'] = $api['starred'];
                ++$j;
            }
        }
        if ($group['isChild'] == 0) {
            $child_group_list = $db->prepareExecuteAll('SELECT eo_api_group.groupID,eo_api_group.groupName FROM eo_api_group WHERE eo_api_group.parentGroupID = ?', array(
                $group_id
            ));
            if ($child_group_list) {
                $i = 0;
                foreach ($child_group_list as $group) {
                    $result['childGroupList'][$i]['groupName'] = $group['groupName'];
                    $api_list = $db->prepareExecuteAll("SELECT eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred FROM eo_api_cache INNER JOIN eo_api ON eo_api.apiID = eo_api_cache.apiID WHERE eo_api_cache.groupID = ? AND eo_api.removed = 0;", array(
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
     * 导入接口分组
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
            $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID) VALUES (?,?);', array(
                $data['groupName'],
                $project_id
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("addGroup error");

            $group_id = $db->getLastInsertID();
            if ($data['apiList']) {
                foreach ($data['apiList'] as $api) {
                    // 插入api基本信息
                    $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime,eo_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
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

                    // 插入header信息
                    foreach ($api['headerInfo'] as $header) {
                        $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                            $header['headerName'],
                            $header['headerValue'],
                            $api_id
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addHeader error");
                    }

                    // 插入api请求值信息
                    foreach ($api['requestInfo'] as $request) {
                        $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
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
                            $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                $param_id,
                                $value['value'],
                                $value['valueDescription']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addApi error");
                        };
                    };

                    // 插入api返回值信息
                    foreach ($api['resultInfo'] as $result) {
                        $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                            $api_id,
                            $result['paramName'],
                            $result['paramKey'],
                            $result['paramNotNull']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addResultParam error");

                        $param_id = $db->getLastInsertID();

                        foreach ($result['paramValueList'] as $value) {
                            $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                $param_id,
                                $value['value'],
                                $value['valueDescription']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addApi error");
                        };
                    };

                    // 插入api缓存数据用于导出
                    $db->prepareExecute("INSERT INTO eo_api_cache (eo_api_cache.projectID,eo_api_cache.groupID,eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred) VALUES (?,?,?,?,?);", array(
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
            // 二级分组代码
            if ($data['childGroupList']) {
                $group_parent_id = $group_id;
                foreach ($data['childGroupList'] as $api_group_child) {
                    $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID,eo_api_group.parentGroupID, eo_api_group.isChild) VALUES (?,?,?,?);', array(
                        $api_group_child['groupName'],
                        $project_id,
                        $group_parent_id,
                        1
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addChildGroup error");

                    $group_id = $db->getLastInsertID();

                    // 如果当前分组没有接口，则跳过到下一分组
                    if (empty($api_group_child['apiList']))
                        continue;

                    foreach ($api_group_child['apiList'] as $api) {
                        // 插入api基本信息
                        $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime,eo_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
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

                        // 插入header信息
                        foreach ($api['headerInfo'] as $header) {
                            $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                                $header['headerName'],
                                $header['headerValue'],
                                $api_id
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addChildHeader error");
                        }

                        // 插入api请求值信息
                        foreach ($api['requestInfo'] as $request) {
                            $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
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
                                    $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                        $param_id,
                                        $value['value'],
                                        $value['valueDescription']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildApi error");
                                };
                            }
                        };

                        // 插入api返回值信息
                        foreach ($api['resultInfo'] as $result) {
                            $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
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
                                    $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                        $param_id,
                                        $value['value'],
                                        $value['valueDescription']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildParamValue error");
                                };
                            }
                        };

                        // 插入api缓存数据用于导出
                        $db->prepareExecute("INSERT INTO eo_api_cache (eo_api_cache.projectID,eo_api_cache.groupID,eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred) VALUES (?,?,?,?,?);", array(
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
            $db->commit();
            return TRUE;
        } catch (\Exception $e) {
            $db->rollback();
            return FALSE;
        }
    }
}

?>
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
class ApiDao
{

    /**
     * add api
     *
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
     * @param $apiNote stringL
     * @param $apiRequestParamType int
     * @param $apiRequestRaw string
     * @param $cacheJson string
     * @param $updateTime string
     * @param $updateUserID int 
     * @param $mockRule array 
     * @param $mockResult string 
     * @param $mockConfig array 
     * @param $success_status_code
     * @param $failure_status_code
     * @param $before_inject
     * @param $after_inject
     * @return bool|array
     */
    public function addApi(&$apiName, &$apiURI, &$apiProtocol, &$apiSuccessMock = '', &$apiFailureMock = '', &$apiRequestType, &$apiStatus, &$groupID, &$apiHeader, &$apiRequestParam, &$apiResultParam, &$starred, &$apiNoteType, &$apiNoteRaw, &$apiNote, &$projectID, &$apiRequestParamType, &$apiRequestRaw, &$cacheJson, &$updateTime, &$updateUserID, &$mockRule, &$mockResult, &$mockConfig, &$success_status_code, &$failure_status_code, &$before_inject, &$after_inject)
    {
        $db = getDatabase();
        try {
            // begin transaction
            $db->beginTransaction();
            // insert api base info
            $db->prepareExecute('INSERT INTO eo_ams_api (eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiProtocol,eo_ams_api.apiSuccessMock,eo_ams_api.apiFailureMock,eo_ams_api.apiRequestType,eo_ams_api.apiStatus,eo_ams_api.groupID,eo_ams_api.projectID,eo_ams_api.starred,eo_ams_api.apiNoteType,eo_ams_api.apiNoteRaw,eo_ams_api.apiNote,eo_ams_api.apiRequestParamType,eo_ams_api.apiRequestRaw,eo_ams_api.apiUpdateTime,eo_ams_api.createUserID,eo_ams_api.mockRule,eo_ams_api.mockResult,eo_ams_api.mockConfig,apiSuccessStatusCode,apiFailureStatusCode,beforeInject,afterInject) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                $apiName,
                $apiURI,
                $apiProtocol,
                $apiSuccessMock,
                $apiFailureMock,
                $apiRequestType,
                $apiStatus,
                $groupID,
                $projectID,
                $starred,
                $apiNoteType,
                $apiNoteRaw,
                $apiNote,
                $apiRequestParamType,
                $apiRequestRaw,
                $updateTime,
                $updateUserID,
                json_encode($mockRule),
                $mockResult,
                $mockConfig,
                $success_status_code,
                $failure_status_code,
                $before_inject,
                $after_inject
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("addApi error");

            if ($db->getAffectRow() > 0) {
                $apiID = $db->getLastInsertID();
                // insert api header info
                foreach ($apiHeader as $param) {
                    $db->prepareExecute('INSERT INTO eo_ams_api_header (eo_ams_api_header.headerName,eo_ams_api_header.headerValue,eo_ams_api_header.apiID) VALUES (?,?,?);', array(
                        $param['headerName'],
                        $param['headerValue'],
                        $apiID
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addHeader error");
                }
                // insert api request param info
                foreach ($apiRequestParam as $param) {
                    $db->prepareExecute('INSERT INTO eo_ams_api_request_param (eo_ams_api_request_param.apiID,eo_ams_api_request_param.paramName,eo_ams_api_request_param.paramKey,eo_ams_api_request_param.paramValue,eo_ams_api_request_param.paramLimit,eo_ams_api_request_param.paramNotNull,eo_ams_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                        $apiID,
                        $param['paramName'],
                        $param['paramKey'],
                        $param['paramValue'],
                        $param['paramLimit'],
                        $param['paramNotNull'],
                        $param['paramType']
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addRequestParam error");

                    $paramID = $db->getLastInsertID();

                    foreach ($param['paramValueList'] as $value) {
                        $db->prepareExecute('INSERT INTO eo_ams_api_request_value (eo_ams_api_request_value.paramID,eo_ams_api_request_value.`value`,eo_ams_api_request_value.valueDescription) VALUES (?,?,?);', array(
                            $paramID,
                            $value['value'],
                            $value['valueDescription']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addApi error");
                    };
                };
                // insert api result param info
                foreach ($apiResultParam as $param) {
                    $db->prepareExecute('INSERT INTO eo_ams_api_result_param (eo_ams_api_result_param.apiID,eo_ams_api_result_param.paramName,eo_ams_api_result_param.paramKey,eo_ams_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                        $apiID,
                        $param['paramName'],
                        $param['paramKey'],
                        $param['paramNotNull']
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addResultParam error");

                    $paramID = $db->getLastInsertID();

                    foreach ($param['paramValueList'] as $value) {
                        $db->prepareExecute('INSERT INTO eo_ams_api_result_value (eo_ams_api_result_value.paramID,eo_ams_api_result_value.`value`,eo_ams_api_result_value.valueDescription) VALUES (?,?,?);', array(
                            $paramID,
                            $value['value'],
                            $value['valueDescription']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addApi error");
                    };
                };
                // insert api cache json which used for exportation
                $db->prepareExecute("INSERT INTO eo_ams_api_cache (eo_ams_api_cache.projectID,eo_ams_api_cache.groupID,eo_ams_api_cache.apiID,eo_ams_api_cache.apiJson,eo_ams_api_cache.starred,eo_ams_api_cache.updateUserID) VALUES (?,?,?,?,?,?);", array(
                    $projectID,
                    $groupID,
                    $apiID,
                    $cacheJson,
                    $starred,
                    $updateUserID
                ));

                if ($db->getAffectRow() < 1) {
                    throw new \PDOException("addApiCache error");
                }

                $db->commit();
                $result['apiID'] = $apiID;
                $result['groupID'] = $groupID;
                return $result;
            } else {
                throw new \PDOException("addApi error");
            }
        } catch (\PDOException $e) {
            var_dump($e->getMessage());
            $db->rollBack();
            return FALSE;
        }
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
     * @param $apiNoteType int
     * @param $apiNoteRaw string
     * @param $apiNote string
     * @param $apiRequestParamType int
     * @param $apiRequestRaw string
     * @param $cacheJson string
     * @param $updateTime string
     * @param $updateUserID int 
     * @param $mockRule array 
     * @param $mockResult string 
     * @param $mockConfig array 
     * @param $success_status_code
     * @param $failure_status_code
     * @param $before_inject
     * @param $after_inject
     * @return bool
     */
    public function editApi(&$apiID, &$apiName, &$apiURI, &$apiProtocol, &$apiSuccessMock, &$apiFailureMock, &$apiRequestType, &$apiStatus, &$groupID, &$apiHeader, &$apiRequestParam, &$apiResultParam, &$starred, &$apiNoteType, &$apiNoteRaw, &$apiNote, &$apiRequestParamType, &$apiRequestRaw, &$cacheJson, &$updateTime, &$updateUserID, &$mockRule, &$mockResult, &$mockConfig, &$success_status_code, &$failure_status_code, &$before_inject, &$after_inject)
    {
        $db = getDatabase();
        try {
            $db->beginTransaction();
            $db->prepareExecute('UPDATE eo_ams_api SET eo_ams_api.apiName = ?,eo_ams_api.apiURI = ?,eo_ams_api.apiProtocol = ?,eo_ams_api.apiSuccessMock = ?,eo_ams_api.apiFailureMock = ?,eo_ams_api.apiRequestType = ?,eo_ams_api.apiStatus = ?,eo_ams_api.starred = ?,eo_ams_api.groupID = ?,eo_ams_api.apiNoteType = ?,eo_ams_api.apiNoteRaw = ?,eo_ams_api.apiNote = ?,eo_ams_api.apiUpdateTime = ?,eo_ams_api.apiRequestParamType = ?,eo_ams_api.apiRequestRaw = ?,eo_ams_api.updateUserID = ?,eo_ams_api.mockRule = ?,eo_ams_api.mockResult = ?,eo_ams_api.mockConfig = ?,eo_ams_api.apiSuccessStatusCode = ?,eo_ams_api.apiFailureStatusCode = ?,eo_ams_api.beforeInject = ?,eo_ams_api.afterInject = ? WHERE eo_ams_api.apiID = ?;', array(
                $apiName,
                $apiURI,
                $apiProtocol,
                $apiSuccessMock,
                $apiFailureMock,
                $apiRequestType,
                $apiStatus,
                $starred,
                $groupID,
                $apiNoteType,
                $apiNoteRaw,
                $apiNote,
                $updateTime,
                $apiRequestParamType,
                $apiRequestRaw,
                $updateUserID,
                json_encode($mockRule),
                $mockResult,
                $mockConfig,
                $success_status_code,
                $failure_status_code,
                $before_inject,
                $after_inject,
                $apiID
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("edit Api error");

            $db->prepareExecute('DELETE FROM eo_ams_api_header WHERE eo_ams_api_header.apiID = ?;', array(
                $apiID
            ));
            $db->prepareExecute('DELETE FROM eo_ams_api_request_param WHERE eo_ams_api_request_param.apiID = ?;', array(
                $apiID
            ));
            $db->prepareExecute('DELETE FROM eo_ams_api_result_param WHERE eo_ams_api_result_param.apiID = ?;', array(
                $apiID
            ));
            // insert api header info
            foreach ($apiHeader as $param) {
                $db->prepareExecute('INSERT INTO eo_ams_api_header (eo_ams_api_header.headerName,eo_ams_api_header.headerValue,eo_ams_api_header.apiID) VALUES (?,?,?);', array(
                    $param['headerName'],
                    $param['headerValue'],
                    $apiID
                ));

                if ($db->getAffectRow() < 1)
                    throw new \PDOException("addApi error");
            };
            // insert api request param info
            foreach ($apiRequestParam as $param) {
                $db->prepareExecute('INSERT INTO eo_ams_api_request_param (eo_ams_api_request_param.apiID,eo_ams_api_request_param.paramName,eo_ams_api_request_param.paramKey,eo_ams_api_request_param.paramValue,eo_ams_api_request_param.paramLimit,eo_ams_api_request_param.paramNotNull,eo_ams_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                    $apiID,
                    $param['paramName'],
                    $param['paramKey'],
                    $param['paramValue'],
                    $param['paramLimit'],
                    $param['paramNotNull'],
                    $param['paramType']
                ));

                if ($db->getAffectRow() < 1)
                    throw new \PDOException("addApi error");

                $paramID = $db->getLastInsertID();
                if (is_array($param['paramValueList'])) {
                    foreach ($param['paramValueList'] as $value) {
                        $db->prepareExecute('INSERT INTO eo_ams_api_request_value (eo_ams_api_request_value.paramID,eo_ams_api_request_value.`value`,eo_ams_api_request_value.valueDescription) VALUES (?,?,?);', array(
                            $paramID,
                            $value['value'],
                            $value['valueDescription']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addApi error");
                    };
                }
            };
            // insert api result param info
            foreach ($apiResultParam as $param) {
                $db->prepareExecute('INSERT INTO eo_ams_api_result_param (eo_ams_api_result_param.apiID,eo_ams_api_result_param.paramName,eo_ams_api_result_param.paramKey,eo_ams_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                    $apiID,
                    $param['paramName'],
                    $param['paramKey'],
                    $param['paramNotNull']
                ));

                if ($db->getAffectRow() < 1)
                    throw new \PDOException("addApi error");

                $paramID = $db->getLastInsertID();
                if (is_array($param['paramValueList'])) {
                    foreach ($param['paramValueList'] as $value) {
                        $db->prepareExecute('INSERT INTO eo_ams_api_result_value (eo_ams_api_result_value.paramID,eo_ams_api_result_value.`value`,eo_ams_api_result_value.valueDescription) VALUES (?,?,?);', array(
                            $paramID,
                            $value['value'],
                            $value['valueDescription']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addApi error");
                    };
                }
            };
            // update api cache json
            $db->prepareExecute("UPDATE eo_ams_api_cache SET eo_ams_api_cache.apiJson = ?,eo_ams_api_cache.groupID = ?,eo_ams_api_cache.starred = ?,eo_ams_api_cache.updateUserID = ? WHERE eo_ams_api_cache.apiID = ?;", array(
                $cacheJson,
                $groupID,
                $starred,
                $updateUserID,
                $apiID
            ));

            if ($db->getAffectRow() < 1) {
                throw new \PDOException("updateApiCache error");
            }

            $db->commit();
            $result['apiID'] = $apiID;
            $result['groupID'] = $groupID;
            return $result;
        } catch (\PDOException $e) {
            $db->rollBack();
            return FALSE;
        }
    }

    /**
     * delete api and move the api into recycling station
     *
     * @param $apiID int
     * @return bool
     */
    public function removeApi(&$apiID)
    {
        $db = getDatabase();
        $db->beginTransaction();

        $db->prepareExecute('UPDATE eo_ams_api SET eo_ams_api.removed = 1 ,eo_ams_api.removeTime = ? WHERE eo_ams_api.apiID = ?;', array(
            date("Y-m-d H:i:s", time()),
            $apiID
        ));

        if ($db->getAffectRow() > 0) {
            $db->commit();
            return TRUE;
        } else {
            $db->rollback();
            return FALSE;
        }
    }

    /**
     * recover api
     *
     * @param $apiID int
     * @return bool
     */
    public function recoverApi(&$apiID)
    {
        $db = getDatabase();
        $db->beginTransaction();

        $db->prepareExecute('UPDATE eo_ams_api SET eo_ams_api.removed = 0 WHERE eo_ams_api.apiID = ?;', array(
            $apiID
        ));

        if ($db->getAffectRow() > 0) {
            $db->commit();
            return TRUE;
        } else {
            $db->rollback();
            return FALSE;
        }
    }

    /**
     * remove apii
     *
     * @param $apiID int
     * @return bool
     */
    public function deleteApi(&$apiID)
    {
        $db = getDatabase();
        try {
            $db->beginTransaction();

            $db->prepareExecute('DELETE FROM eo_ams_api WHERE eo_ams_api.apiID = ? AND eo_ams_api.removed = 1;', array(
                $apiID
            ));
            if ($db->getAffectRow() < 1)
                throw new \PDOException("deleteApi error");

            $db->prepareExecute('DELETE FROM eo_ams_api_cache WHERE eo_ams_api_cache.apiID = ?;', array(
                $apiID
            ));
            $db->prepareExecute('DELETE FROM eo_ams_api_header WHERE eo_ams_api_header.apiID = ?;', array(
                $apiID
            ));
            $db->prepareExecute('DELETE FROM eo_ams_api_request_value WHERE eo_ams_api_request_value.paramID IN (SELECT eo_ams_api_request_param.paramID FROM eo_ams_api_request_param WHERE eo_ams_api_request_param.apiID = ?);', array(
                $apiID
            ));
            $db->prepareExecute('DELETE FROM eo_ams_api_request_param WHERE eo_ams_api_request_param.apiID = ?;', array(
                $apiID
            ));
            $db->prepareExecute('DELETE FROM eo_ams_api_result_value WHERE eo_ams_api_result_value.paramID IN (SELECT eo_ams_api_result_param.paramID FROM eo_ams_api_result_param WHERE eo_ams_api_result_param.apiID = ?);', array(
                $apiID
            ));
            $db->prepareExecute('DELETE FROM eo_ams_api_result_param WHERE eo_ams_api_result_param.apiID = ?;', array(
                $apiID
            ));

            $db->commit();
            return TRUE;
        } catch (\PDOException $e) {
            $db->rollBack();
            return FALSE;
        }
    }

    /**
     * clean up recycling station
     *
     * @param $projectID int
     * @return bool
     */
    public function cleanRecyclingStation(&$projectID)
    {
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_ams_api WHERE eo_ams_api.projectID= ? AND eo_ams_api.removed = 1;', array(
            $projectID
        ));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * get api list by group and order by apiName
     *
     * @param $groupID int
     * @param $asc string
     * @return bool|array
     */
    public function getApiListOrderByName(&$groupID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.starred,eo_ams_api_group.groupID,eo_ams_api_group.parentGroupID,eo_ams_api_group.groupName,eo_ams_api.updateUserID,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_ams_api LEFT JOIN eo_ams_api_group ON eo_ams_api.groupID = eo_ams_api_group.groupID LEFT JOIN eo_ams_conn_project ON eo_ams_api.updateUserID = eo_ams_conn_project.userID AND eo_ams_api.projectID = eo_ams_conn_project.projectID LEFT JOIN eo_user ON eo_ams_api.updateUserID = eo_user.userID WHERE (eo_ams_api_group.groupID = ? OR eo_ams_api_group.parentGroupID = ? OR eo_ams_api.groupID IN (SELECT eo_ams_api_group.groupID FROM eo_ams_api_group WHERE eo_ams_api_group.parentGroupID IN (SELECT eo_ams_api_group.groupID FROM eo_ams_api_group WHERE eo_ams_api_group.parentGroupID = ?))) AND eo_ams_api.removed = 0 ORDER BY eo_ams_api.apiName $asc;", array(
            $groupID,
            $groupID,
            $groupID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list by group and order by upodate time
     *
     * @param $groupID int
     * @param $asc string
     * @return bool|array
     */
    public function getApiListOrderByTime(&$groupID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.starred,eo_ams_api_group.groupID,eo_ams_api_group.parentGroupID,eo_ams_api_group.groupName,eo_ams_api.updateUserID,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_ams_api LEFT JOIN eo_ams_api_group ON eo_ams_api.groupID = eo_ams_api_group.groupID LEFT JOIN eo_ams_conn_project ON eo_ams_api.updateUserID = eo_ams_conn_project.userID AND eo_ams_api.projectID = eo_ams_conn_project.projectID LEFT JOIN eo_user ON eo_ams_api.updateUserID = eo_user.userID WHERE (eo_ams_api_group.groupID = ? OR eo_ams_api_group.parentGroupID = ? OR eo_ams_api.groupID IN (SELECT eo_ams_api_group.groupID FROM eo_ams_api_group WHERE eo_ams_api_group.parentGroupID IN (SELECT eo_ams_api_group.groupID FROM eo_ams_api_group WHERE eo_ams_api_group.parentGroupID = ?))) AND eo_ams_api.removed = 0 ORDER BY eo_ams_api.apiUpdateTime $asc;", array(
            $groupID,
            $groupID,
            $groupID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list by group and order by starred
     *
     * @param $groupID int
     * @param $asc string
     * @return bool|array
     */
    public function getApiListOrderByStarred(&$groupID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.starred,eo_ams_api_group.groupID,eo_ams_api_group.parentGroupID,eo_ams_api_group.groupName,eo_ams_api.updateUserID,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_ams_api LEFT JOIN eo_ams_api_group ON eo_ams_api.groupID = eo_ams_api_group.groupID LEFT JOIN eo_ams_conn_project ON eo_ams_api.updateUserID = eo_ams_conn_project.userID AND eo_ams_api.projectID = eo_ams_conn_project.projectID LEFT JOIN eo_user ON eo_ams_api.updateUserID = eo_user.userID WHERE (eo_ams_api_group.groupID = ? OR eo_ams_api_group.parentGroupID = ? OR eo_ams_api.groupID IN (SELECT eo_ams_api_group.groupID FROM eo_ams_api_group WHERE eo_ams_api_group.parentGroupID IN (SELECT eo_ams_api_group.groupID FROM eo_ams_api_group WHERE eo_ams_api_group.parentGroupID = ?))) AND eo_ams_api.removed = 0 ORDER BY eo_ams_api.starred $asc;", array(
            $groupID,
            $groupID,
            $groupID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list by group and order by starred
     *
     * @param $groupID int
     * @param $asc string
     * @return bool|array
     */
    public function getApiListOrderByUri(&$groupID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.starred,eo_ams_api_group.groupID,eo_ams_api_group.parentGroupID,eo_ams_api_group.groupName,eo_ams_api.updateUserID,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_ams_api LEFT JOIN eo_ams_api_group ON eo_ams_api.groupID = eo_ams_api_group.groupID LEFT JOIN eo_ams_conn_project ON eo_ams_api.updateUserID = eo_ams_conn_project.userID AND eo_ams_api.projectID = eo_ams_conn_project.projectID LEFT JOIN eo_user ON eo_ams_api.updateUserID = eo_user.userID WHERE (eo_ams_api_group.groupID = ? OR eo_ams_api_group.parentGroupID = ? OR eo_ams_api.groupID IN (SELECT eo_ams_api_group.groupID FROM eo_ams_api_group WHERE eo_ams_api_group.parentGroupID IN (SELECT eo_ams_api_group.groupID FROM eo_ams_api_group WHERE eo_ams_api_group.parentGroupID = ?))) AND eo_ams_api.removed = 0 ORDER BY eo_ams_api.apiURI $asc;", array(
            $groupID,
            $groupID,
            $groupID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list by group and order by create time
     *
     * @param $groupID int
     * @param $asc string
     * @return bool|array
     */
    public function getApiListOrderByCreateTime(&$groupID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.starred,eo_ams_api_group.groupID,eo_ams_api_group.parentGroupID,eo_ams_api_group.groupName,eo_ams_api.updateUserID,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_ams_api LEFT JOIN eo_ams_api_group ON eo_ams_api.groupID = eo_ams_api_group.groupID LEFT JOIN eo_ams_conn_project ON eo_ams_api.updateUserID = eo_ams_conn_project.userID AND eo_ams_api.projectID = eo_ams_conn_project.projectID LEFT JOIN eo_user ON eo_ams_api.updateUserID = eo_user.userID WHERE (eo_ams_api_group.groupID = ? OR eo_ams_api_group.parentGroupID = ? OR eo_ams_api.groupID IN (SELECT eo_ams_api_group.groupID FROM eo_ams_api_group WHERE eo_ams_api_group.parentGroupID IN (SELECT eo_ams_api_group.groupID FROM eo_ams_api_group WHERE eo_ams_api_group.parentGroupID = ?))) AND eo_ams_api.removed = 0 ORDER BY eo_ams_api.apiID $asc;", array(
            $groupID,
            $groupID,
            $groupID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api detail
     *
     * @param $apiID int
     * @return array|bool
     */
    public function getApi(&$apiID,$projectID)
    {
        $db = getDatabase();
        $apiInfo = $db->prepareExecute('SELECT eo_ams_api_cache.*,eo_ams_api_group.parentGroupID FROM eo_ams_api_cache LEFT JOIN eo_ams_api_group ON eo_ams_api_cache.groupID = eo_ams_api_group.groupID WHERE eo_ams_api_cache.apiID = ?;', array(
            $apiID
        ));
        $apiJson = json_decode($apiInfo['apiJson'], TRUE);
        if(! isset($apiJson['urlParam']))
        {
        	$apiJson['urlParam'] = array();
        }
        if(! isset($apiJson['restfulParam']))
        {
        	$apiJson['restfulParam'] = array();
        }
        if(! isset($apiJson['responseHeader']))
        {
        	$apiJson['responseHeader'] = array();
        }
        $apiJson['baseInfo']['starred'] = $apiInfo['starred'];
        $apiJson['baseInfo']['groupID'] = $apiInfo['groupID'];
        $apiJson['baseInfo']['mockCode'] = "&projectID={$apiInfo['projectID']}&uri={$apiJson['baseInfo']['apiURI']}";
        $apiJson['baseInfo']['successMockURL'] = (is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?g=Web&c=Mock&o=simple' . $apiJson['baseInfo']['mockCode'];
        $apiJson['baseInfo']['failureMockURL'] = (is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?g=Web&c=Mock&o=simple&resultType=failure' . $apiJson['baseInfo']['mockCode'];
        $apiJson['baseInfo']['parentGroupID'] = $apiInfo['parentGroupID'];
        $apiJson['baseInfo']['projectID'] = $apiInfo['projectID'];
        $apiJson['baseInfo']['apiID'] = $apiInfo['apiID'];
        $topParentGroupID = $db->prepareExecute('SELECT eo_ams_api_group.parentGroupID FROM eo_ams_api_group WHERE eo_ams_api_group.groupID = ? AND eo_ams_api_group.isChild = 1;', array(
            $apiInfo['parentGroupID']
        ));
        
        $apiJson['baseInfo']['topParentGroupID'] = $topParentGroupID['parentGroupID'] ? $topParentGroupID['parentGroupID'] : $apiInfo['parentGroupID'];
        
        $apiJson['baseInfo']['mockURL'] = (is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?g=Web&c=Mock&o=mock' . $apiJson['baseInfo']['mockCode'];
        $test_history = $db->prepareExecuteAll('SELECT eo_ams_api_test_history.testID,eo_ams_api_test_history.requestInfo,eo_ams_api_test_history.resultInfo,eo_ams_api_test_history.testTime FROM eo_ams_api_test_history WHERE eo_ams_api_test_history.apiID = ? ORDER BY eo_ams_api_test_history.testTime DESC LIMIT 10;', array(
            $apiID
        ));
        if(! isset($apiJson['authInfo']))
        {
        	$apiJson['authInfo'] = new \stdClass();;
        }
        $author_info = $db->prepareExecute("SELECT eo_ams_conn_project.partnerNickName as updater,eo_user.userNickName as uUserNickName,
			            tb1.partnerNickName AS creator,tb2.userNickName as cUserNickName
						FROM eo_ams_api 
						LEFT JOIN eo_ams_conn_project ON eo_ams_conn_project.projectID = eo_ams_api.projectID AND eo_ams_conn_project.userID = eo_ams_api.updateUserID
						LEFT JOIN eo_user ON eo_user.userID = eo_ams_api.updateUserID
			      LEFT JOIN eo_ams_conn_project AS tb1 ON tb1.projectID = eo_ams_conn_project.projectID AND tb1.userID = eo_ams_api.createUserID
						LEFT JOIN eo_user AS tb2 ON tb2.userID = eo_ams_api.createUserID WHERE  eo_ams_api.projectID =? AND eo_ams_api.apiID = ?;", array(
								$projectID,
								$apiID
						));
        if($author_info['updater'])
        {
        	$apiJson['baseInfo']['updater'] = $author_info['updater'];
        }
        else
        {
        	$apiJson['baseInfo']['updater'] = $author_info['uUserNickName'];
        }
        if($author_info['creator'])
        {
        	$apiJson['baseInfo']['creator'] = $author_info['creator'];
        }
        else
        {
        	$apiJson['baseInfo']['creator'] = $author_info['cUserNickName'];
        }
        $apiJson['testHistory'] = $test_history;
       
        return $apiJson;
    }

    /**
     * get all api list by project and order by apiName
     *
     * @param $projectID int
     * @param $asc string
     * @return bool|array
     */
    public function getAllApiListOrderByName(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api_group.groupID,eo_ams_api_group.parentGroupID,eo_ams_api_group.groupName,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.starred,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_ams_api LEFT JOIN eo_ams_api_group ON eo_ams_api.groupID = eo_ams_api_group.groupID LEFT JOIN eo_ams_conn_project ON eo_ams_api.updateUserID = eo_ams_conn_project.userID AND eo_ams_api.projectID = eo_ams_conn_project.projectID LEFT JOIN eo_user ON eo_ams_api.updateUserID = eo_user.userID WHERE eo_ams_api_group.projectID = ? AND eo_ams_api.removed = 0 ORDER BY eo_ams_api.apiName $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get all api list by project and order by URI
     *
     * @param $projectID int
     * @param $asc string
     * @return bool|array
     */
    public function getAllApiListOrderByUri(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api_group.groupID,eo_ams_api_group.parentGroupID,eo_ams_api_group.groupName,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.starred,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_ams_api LEFT JOIN eo_ams_api_group ON eo_ams_api.groupID = eo_ams_api_group.groupID LEFT JOIN eo_ams_conn_project ON eo_ams_api.updateUserID = eo_ams_conn_project.userID AND eo_ams_api.projectID = eo_ams_conn_project.projectID LEFT JOIN eo_user ON eo_ams_api.updateUserID = eo_user.userID WHERE eo_ams_api_group.projectID = ? AND eo_ams_api.removed = 0 ORDER BY eo_ams_api.apiURI $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get all api list by project and order by create time
     *
     * @param $projectID int
     * @param $asc string
     * @return bool|array
     */
    public function getAllApiListOrderByCreateTime(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api_group.groupID,eo_ams_api_group.parentGroupID,eo_ams_api_group.groupName,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.starred,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_ams_api LEFT JOIN eo_ams_api_group ON eo_ams_api.groupID = eo_ams_api_group.groupID LEFT JOIN eo_ams_conn_project ON eo_ams_api.updateUserID = eo_ams_conn_project.userID AND eo_ams_api.projectID = eo_ams_conn_project.projectID LEFT JOIN eo_user ON eo_ams_api.updateUserID = eo_user.userID WHERE eo_ams_api_group.projectID = ? AND eo_ams_api.removed = 0 ORDER BY eo_ams_api.apiID $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get all api list by project and order by update time
     *
     * @param $projectID int
     * @param $asc string
     * @return bool|array
     */
    public function getAllApiListOrderByTime(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api_group.groupID,eo_ams_api_group.parentGroupID,eo_ams_api_group.groupName,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.starred,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_ams_api LEFT JOIN eo_ams_api_group ON eo_ams_api.groupID = eo_ams_api_group.groupID LEFT JOIN eo_ams_conn_project ON eo_ams_api.updateUserID = eo_ams_conn_project.userID AND eo_ams_api.projectID = eo_ams_conn_project.projectID LEFT JOIN eo_user ON eo_ams_api.updateUserID = eo_user.userID WHERE eo_ams_api_group.projectID = ? AND eo_ams_api.removed = 0 ORDER BY eo_ams_api.apiUpdateTime $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get all api list by project and order by starred
     *
     * @param $projectID int
     * @param $asc string
     * @return bool|array
     */
    public function getAllApiListOrderByStarred(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api_group.groupID,eo_ams_api_group.parentGroupID,eo_ams_api_group.groupName,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.starred,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_ams_api LEFT JOIN eo_ams_api_group ON eo_ams_api.groupID = eo_ams_api_group.groupID LEFT JOIN eo_ams_conn_project ON eo_ams_api.updateUserID = eo_ams_conn_project.userID AND eo_ams_api.projectID = eo_ams_conn_project.projectID LEFT JOIN eo_user ON eo_ams_api.updateUserID = eo_user.userID WHERE eo_ams_api_group.projectID = ? AND eo_ams_api.removed = 0 ORDER BY eo_ams_api.starred $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list from recycling station and order by apiName
     *
     * @param $projectID int
     * @param $asc string
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByName(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.removeTime,eo_ams_api.starred,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_ams_api LEFT JOIN eo_ams_conn_project ON eo_ams_api.updateUserID = eo_ams_conn_project.userID AND eo_ams_api.projectID = eo_ams_conn_project.projectID LEFT JOIN eo_user ON eo_ams_api.updateUserID = eo_user.userID WHERE eo_ams_api.projectID = ? AND eo_ams_api.removed = 1 ORDER BY eo_ams_api.apiName $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list from recycling station and order by URI
     *
     * @param $projectID int
     * @param $asc string
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByUri(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.removeTime,eo_ams_api.starred,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_ams_api LEFT JOIN eo_ams_conn_project ON eo_ams_api.updateUserID = eo_ams_conn_project.userID AND eo_ams_api.projectID = eo_ams_conn_project.projectID LEFT JOIN eo_user ON eo_ams_api.updateUserID = eo_user.userID WHERE eo_ams_api.projectID = ? AND eo_ams_api.removed = 1 ORDER BY eo_ams_api.apiURI $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list from recycling station and order by create time
     *
     * @param $projectID int
     * @param $asc string
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByCreateTime(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.removeTime,eo_ams_api.starred,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_ams_api LEFT JOIN eo_ams_conn_project ON eo_ams_api.updateUserID = eo_ams_conn_project.userID AND eo_ams_api.projectID = eo_ams_conn_project.projectID LEFT JOIN eo_user ON eo_ams_api.updateUserID = eo_user.userID WHERE eo_ams_api.projectID = ? AND eo_ams_api.removed = 1 ORDER BY eo_ams_api.apiID $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list from recycling station and order by remove time
     *
     * @param $projectID int
     * @param $asc string
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByRemoveTime(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.removeTime,eo_ams_api.starred,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_ams_api LEFT JOIN eo_ams_conn_project ON eo_ams_api.updateUserID = eo_ams_conn_project.userID AND eo_ams_api.projectID = eo_ams_conn_project.projectID LEFT JOIN eo_user ON eo_ams_api.updateUserID = eo_user.userID WHERE eo_ams_api.projectID = ? AND eo_ams_api.removed = 1 ORDER BY eo_ams_api.removeTime $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list from recycling station and order by starree
     *
     * @param $projectID int
     * @param $asc string
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByStarred(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.removeTime,eo_ams_api.starred,eo_ams_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_ams_api LEFT JOIN eo_ams_conn_project ON eo_ams_api.updateUserID = eo_ams_conn_project.userID AND eo_ams_api.projectID = eo_ams_conn_project.projectID LEFT JOIN eo_user ON eo_ams_api.updateUserID = eo_user.userID WHERE eo_ams_api.projectID = ? AND eo_ams_api.removed = 1 ORDER BY eo_ams_api.starred $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * search api
     *
     * @param $tips string
     * @param $projectID int
     * @return bool|array
     */
    public function searchApi(&$tips, &$projectID)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT DISTINCT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api_group.groupID,eo_ams_api_group.parentGroupID,eo_ams_api_group.groupName,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime FROM eo_ams_api LEFT JOIN eo_ams_api_group ON eo_ams_api.groupID = eo_ams_api_group.groupID WHERE eo_ams_api_group.projectID = ? AND eo_ams_api.removed = 0 AND (eo_ams_api.apiName LIKE ? OR eo_ams_api.apiURI LIKE ?)ORDER BY eo_ams_api.apiName;', array(
            $projectID,
            '%' . $tips . '%',
            '%' . $tips . '%'
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * check api permission
     *
     * @param $apiID int
     * @param $userID int
     * @return bool|int
     */
    public function checkApiPermission(&$apiID, &$userID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_ams_conn_project.projectID FROM eo_ams_api LEFT JOIN eo_ams_api_group ON eo_ams_api.groupID = eo_ams_api_group.groupID LEFT JOIN eo_ams_conn_project ON eo_ams_conn_project.projectID = eo_ams_api.projectID WHERE eo_ams_conn_project.userID = ? AND eo_ams_api.apiID = ?;', array(
            $userID,
            $apiID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }

    /**
     * add star
     *
     * @param $apiID int
     * @return bool
     */
    public function addStar(&$apiID)
    {
        $db = getDatabase();
        $db->prepareExecute("UPDATE eo_ams_api SET eo_ams_api.starred = 1 WHERE eo_ams_api.apiID = ?", array($apiID));
        $db->prepareExecute("UPDATE eo_ams_api_cache SET eo_ams_api_cache.starred = 1 WHERE eo_ams_api_cache.apiID = ?;", array($apiID));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
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
        $db = getDatabase();
        $db->prepareExecute("UPDATE eo_ams_api SET eo_ams_api.starred = 0 WHERE eo_ams_api.apiID = ?", array($apiID));
        $db->prepareExecute("UPDATE eo_ams_api_cache SET eo_ams_api_cache.starred = 0 WHERE eo_ams_api_cache.apiID = ?", array($apiID));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
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
        $db = getDatabase();
        $db->prepareExecuteAll("UPDATE eo_ams_api SET eo_ams_api.removed = 1, eo_ams_api.removeTime = ? WHERE eo_ams_api.apiID IN ($apiIDs) AND projectID = ?;", array(
            date("Y-m-d H:i:s", time()),
            $projectID
        ));
        if ($db->getAffectRow() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
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
        $db = getDatabase();
        $db->beginTransaction();
        $db->prepareExecuteAll("DELETE FROM eo_ams_api WHERE apiID IN ($apiIDs) AND projectID = ?;", array(
            $projectID
        ));
        if ($db->getAffectRow() > 0) {
            $db->prepareExecute("DELETE FROM eo_ams_api_cache WHERE eo_ams_api_cache.apiID IN ($apiIDs);", array());
            $db->prepareExecute("DELETE FROM eo_ams_api_header WHERE eo_ams_api_header.apiID IN ($apiIDs);", array());
            $db->prepareExecute("DELETE FROM eo_ams_api_request_value WHERE eo_ams_api_request_value.paramID IN (SELECT eo_ams_api_request_param.paramID FROM eo_ams_api_request_param WHERE eo_ams_api_request_param.apiID IN ($apiIDs));", array());
            $db->prepareExecute("DELETE FROM eo_ams_api_request_param WHERE eo_ams_api_request_param.apiID IN ($apiIDs);", array());
            $db->prepareExecute("DELETE FROM eo_ams_api_result_value WHERE eo_ams_api_result_value.paramID IN (SELECT eo_ams_api_result_param.paramID FROM eo_ams_api_result_param WHERE eo_ams_api_result_param.apiID IN ($apiIDs));", array());
            $db->prepareExecute("DELETE FROM eo_ams_api_result_param WHERE eo_ams_api_result_param.apiID IN ($apiIDs);", array());
            $db->commit();
            return TRUE;
        } else {
            $db->rollback();
            return FALSE;
        }
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
        $db = getDatabase();
        $db->prepareExecuteAll("UPDATE eo_ams_api SET eo_ams_api.removed = 0, eo_ams_api.groupID = ? WHERE eo_ams_api.apiID IN ($apiIDs);", array(
            $groupID
        ));
        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Get Api Name
     *
     * @param string $apiIDs
     * @return boolean|mixed
     */
    public function getApiName(&$apiIDs)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT GROUP_CONCAT(DISTINCT eo_ams_api.apiName) AS apiName FROM eo_ams_api WHERE eo_ams_api.apiID IN ($apiIDs);", array());
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['apiName'];
        }
    }

    /**
     * @param $project_id
     * @param $group_id
     * @param $api_id
     * @param $history_json
     * @param $update_desc
     * @param $update_user_id
     * @param $update_time
     * @return bool
     */
    public function addApiHistory(&$project_id, &$group_id, &$api_id, &$history_json, $update_desc, &$update_user_id, &$update_time)
    {
        $db = getDatabase();
        $db->beginTransaction();
        $db->prepareExecute("UPDATE eo_ams_api_history SET eo_ams_api_history.isNow = 0 WHERE eo_ams_api_history.apiID = ?;", array($api_id));
        $db->prepareExecute("INSERT INTO eo_ams_api_history (eo_ams_api_history.projectID,eo_ams_api_history.groupID,eo_ams_api_history.apiID,eo_ams_api_history.historyJson,eo_ams_api_history.updateDesc,eo_ams_api_history.updateUserID,eo_ams_api_history.updateTime,eo_ams_api_history.isNow) VALUES (?,?,?,?,?,?,?,1);", array(
            $project_id,
            $group_id,
            $api_id,
            $history_json,
            $update_desc,
            $update_user_id,
            $update_time
        ));
        if ($db->getAffectRow() > 0) {
            $db->commit();
            return TRUE;
        } else {
            $db->rollback();
            return FALSE;
        }
    }

    /**
     * @param $api_history_id
     * @param $api_id
     * @return bool
     */
    public function deleteApiHistory(&$api_history_id, &$api_id)
    {
        $db = getDatabase();
        $db->prepareExecute("DELETE FROM eo_ams_api_history WHERE eo_ams_api_history.historyID = ? AND eo_ams_api_history.isNow = 0 AND eo_ams_api_history.apiID = ?;", array($api_history_id, $api_id));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * @param $api_id
     * @param $num_limit
     * @return bool
     */
    public function getApiHistoryList(&$api_id, $num_limit)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT eo_ams_api_history.historyID,eo_ams_api_history.apiID,eo_ams_api_history.groupID,eo_ams_api_history.projectID,eo_ams_api_history.updateDesc,eo_user.userNickName as operator,eo_ams_api_history.updateTime,eo_ams_api_history.isNow FROM eo_ams_api_history INNER JOIN eo_user ON eo_ams_api_history.updateUserID = eo_user.userID WHERE eo_ams_api_history.apiID = ? ORDER BY eo_ams_api_history.updateTime DESC LIMIT ?;', array(
            $api_id,
            $num_limit
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * @param $api_id
     * @param $api_history_id
     * @return bool
     */
    public function toggleApiHistory(&$api_id, &$api_history_id)
    {
        $db = getDatabase();
        try {
          
            $db->beginTransaction();
            $result = $db->prepareExecute('SELECT eo_ams_api_history.projectID FROM eo_ams_api_history WHERE eo_ams_api_history.apiID = ? AND eo_ams_api_history.historyID = ?;', array(
                $api_id,
                $api_history_id
            ));
            if (empty($result)) {
                $db->rollback();
                return FALSE;
            }

            $db->prepareExecute("UPDATE eo_ams_api_history SET eo_ams_api_history.isNow = 0 WHERE eo_ams_api_history.apiID = ?;", array($api_id));
            $db->prepareExecute("UPDATE eo_ams_api_history SET eo_ams_api_history.isNow = 1 WHERE eo_ams_api_history.historyID = ?;", array($api_history_id));

            
            $api_info = $db->prepareExecute('SELECT eo_ams_api_history.historyJson,eo_ams_api_history.groupID,eo_ams_api_history.updateUserID FROM eo_ams_api_history WHERE eo_ams_api_history.historyID = ?;', array($api_history_id));

            $group_id = $api_info['groupID'];
            $update_user_id = $api_info['updateUserID'];

            $db->prepareExecute('UPDATE eo_ams_api_cache SET eo_ams_api_cache.groupID = ?, eo_ams_api_cache.apiJson = ?,eo_ams_api_cache.updateUserID = ? WHERE eo_ams_api_cache.apiID = ?;', array(
                $group_id,
                $api_info['historyJson'],
                $update_user_id,
                $api_id
            ));

            $api_info = json_decode($api_info['historyJson'], TRUE);

           
            $db->prepareExecute('DELETE FROM eo_ams_api_header WHERE eo_ams_api_header.apiID = ?;', array($api_id));
            $db->prepareExecute('DELETE FROM eo_ams_api_request_param WHERE eo_ams_api_request_param.apiID = ?;', array($api_id));
            $db->prepareExecute('DELETE FROM eo_ams_api_result_param WHERE eo_ams_api_result_param.apiID = ?;', array($api_id));

            $db->prepareExecute('UPDATE eo_ams_api SET eo_ams_api.apiName = ?,eo_ams_api.apiURI = ?,eo_ams_api.apiProtocol = ?,eo_ams_api.apiSuccessMock = ?,eo_ams_api.apiFailureMock = ?,eo_ams_api.apiRequestType = ?,eo_ams_api.apiStatus = ?,eo_ams_api.starred = ?,eo_ams_api.groupID = ?,eo_ams_api.apiNoteType = ?,eo_ams_api.apiNoteRaw = ?,eo_ams_api.apiNote = ?,eo_ams_api.apiUpdateTime = ?,eo_ams_api.apiRequestParamType = ?,eo_ams_api.apiRequestRaw = ?,eo_ams_api.updateUserID = ? WHERE eo_ams_api.apiID = ?;', array(
                $api_info['baseInfo']['apiName'],
                $api_info['baseInfo']['apiURI'],
                $api_info['baseInfo']['apiProtocol'],
                $api_info['baseInfo']['apiSuccessMock'],
                $api_info['baseInfo']['apiFailureMock'],
                $api_info['baseInfo']['apiRequestType'],
                $api_info['baseInfo']['apiStatus'],
                $api_info['baseInfo']['starred'],
                $group_id,
                $api_info['baseInfo']['apiNoteType'],
                $api_info['baseInfo']['apiNoteRaw'],
                $api_info['baseInfo']['apiNote'],
                $api_info['baseInfo']['apiUpdateTime'],
                $api_info['baseInfo']['apiRequestParamType'],
                $api_info['baseInfo']['apiRequestRaw'],
                $update_user_id,
                $api_id
            ));

           
            foreach ($api_info['headerInfo'] as $param) {
                $db->prepareExecute('INSERT INTO eo_ams_api_header (eo_ams_api_header.headerName,eo_ams_api_header.headerValue,eo_ams_api_header.apiID) VALUES (?,?,?);', array(
                    $param['headerName'],
                    $param['headerValue'],
                    $api_id
                ));

                if ($db->getAffectRow() < 1)
                    throw new \PDOException("toggleApiHistory error");
            };

           
            foreach ($api_info['requestInfo'] as $param) {
                $db->prepareExecute('INSERT INTO eo_ams_api_request_param (eo_ams_api_request_param.apiID,eo_ams_api_request_param.paramName,eo_ams_api_request_param.paramKey,eo_ams_api_request_param.paramValue,eo_ams_api_request_param.paramLimit,eo_ams_api_request_param.paramNotNull,eo_ams_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                    $api_id,
                    $param['paramName'],
                    $param['paramKey'],
                    $param['paramValue'],
                    $param['paramLimit'],
                    $param['paramNotNull'],
                    $param['paramType']
                ));

                if ($db->getAffectRow() < 1)
                    throw new \PDOException("toggleApiHistory error");

                $param_id = $db->getLastInsertID();

                foreach ($param['paramValueList'] as $value) {
                    $db->prepareExecute('INSERT INTO eo_ams_api_request_value (eo_ams_api_request_value.paramID,eo_ams_api_request_value.`value`,eo_ams_api_request_value.valueDescription) VALUES (?,?,?);', array(
                        $param_id,
                        $value['value'],
                        $value['valueDescription']
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("toggleApiHistory error");
                };
            };

            
            foreach ($api_info['resultInfo'] as $param) {
                $db->prepareExecute('INSERT INTO eo_ams_api_result_param (eo_ams_api_result_param.apiID,eo_ams_api_result_param.paramName,eo_ams_api_result_param.paramKey,eo_ams_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                    $api_id,
                    $param['paramName'],
                    $param['paramKey'],
                    $param['paramNotNull']
                ));

                if ($db->getAffectRow() < 1)
                    throw new \PDOException("toggleApiHistory error");

                $param_id = $db->getLastInsertID();

                foreach ($param['paramValueList'] as $value) {
                    $db->prepareExecute('INSERT INTO eo_ams_api_result_value (eo_ams_api_result_value.paramID,eo_ams_api_result_value.`value`,eo_ams_api_result_value.valueDescription) VALUES (?,?,?);', array(
                        $param_id,
                        $value['value'],
                        $value['valueDescription']
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("toggleApiHistory error");
                };
            }

            $db->commit();
            return TRUE;
        } catch (\PDOException $e) {
            $db->rollBack();
            return FALSE;
        }
    }

    /**
     * 获取接口列表
     *
     * @param int $space_id 空间ID
     * @param int $project_id 项目ID
     * @param int $group_id 分组ID
     * @param int $starred 星标
     */
    public function getApiListByCondition(&$project_id, &$group_id, &$conditions, &$order_by)
    {
    	$db = getDatabase();
    	if($group_id >=0)
    	{
    		
    		// 获取多级分组列表
    		$group_id_list = $db->prepareExecuteAll("SELECT eo_ams_api_group.groupID FROM eo_ams_api_group WHERE eo_ams_api_group.parentGroupID = ?;", array(
    				$group_id
    		));
    		
    		$group_sql = $group_id;
    		// 如果存在子分组,则拼接搜索的范围
    		if(is_array($group_id_list))
    		{
    			foreach($group_id_list as $child_group_id)
    			{
    				$group_sql .= ",{$child_group_id['groupID']}";
    				$child_group_list = $db->prepareExecuteAll("SELECT  eo_ams_api_group.groupID FROM eo_ams_api_group WHERE eo_ams_api_group.parentGroupID = ?", array(
    						$child_group_id['groupID']
    				));
    				if(is_array($child_group_list))
    				{
    					foreach($child_group_list as $group)
    					{
    						$group_sql .= ",{$group['groupID']}";
    					}
    				}
    			}
    		}
    		
    		$result = $db->prepareExecuteAll("SELECT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiStatus,
    				eo_ams_api.apiRequestType,eo_ams_api.apiUpdateTime,eo_ams_api.starred,eo_ams_api_group.groupID,
    				eo_ams_api_group.parentGroupID,eo_ams_api_group.groupName,IFNULL( eo_ams_conn_project.partnerNickName,eo_user.userNickName) as updater,
                       IFNULL( tb1.partnerNickName,tb2.userNickName) as creator   
    				FROM eo_ams_api INNER JOIN eo_ams_api_group ON eo_ams_api.groupID = eo_ams_api_group.groupID
                    LEFT JOIN eo_ams_conn_project ON eo_ams_conn_project.projectID = eo_ams_api.projectID AND eo_ams_conn_project.userID = eo_ams_api.updateUserID
					LEFT JOIN eo_user ON eo_user.userID = eo_ams_api.updateUserID
			        LEFT JOIN eo_ams_conn_project AS tb1 ON tb1.projectID = eo_ams_conn_project.projectID AND tb1.userID = eo_ams_api.createUserID
					LEFT JOIN eo_user AS tb2 ON tb2.userID = eo_ams_api.createUserID
    				WHERE  eo_ams_api.projectID = ? AND eo_ams_api.removed = 0 $conditions AND eo_ams_api.groupID IN ($group_sql) ORDER BY $order_by;", array(
    						$project_id
    				));
    	}
    	else
    	{
    		
    		$result = $db->prepareExecuteAll("SELECT eo_ams_api.apiID,eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api_group.groupID,
    				eo_ams_api_group.parentGroupID,eo_ams_api_group.groupName,eo_ams_api.apiStatus,eo_ams_api.apiRequestType,
    				eo_ams_api.apiUpdateTime,eo_ams_api.starred,IFNULL( eo_ams_conn_project.partnerNickName,eo_user.userNickName) as updater,
                       IFNULL( tb1.partnerNickName,tb2.userNickName) as creator   
                    FROM eo_ams_api INNER JOIN eo_ams_api_group ON eo_ams_api.groupID = eo_ams_api_group.groupID
                    LEFT JOIN eo_ams_conn_project ON eo_ams_conn_project.projectID = eo_ams_api.projectID AND eo_ams_conn_project.userID = eo_ams_api.updateUserID
					LEFT JOIN eo_user ON eo_user.userID = eo_ams_api.updateUserID
			        LEFT JOIN eo_ams_conn_project AS tb1 ON tb1.projectID = eo_ams_conn_project.projectID AND tb1.userID = eo_ams_api.createUserID
					LEFT JOIN eo_user AS tb2 ON tb2.userID = eo_ams_api.createUserID
    				WHERE   eo_ams_api_group.projectID = ? AND eo_ams_api.removed = 0 $conditions ORDER BY $order_by;", array(
    						$project_id
    						
    				));
    	}
    	if(empty($result))
    		return FALSE;
    		else
    			return $result;
    }
    
    /**
     * Batch edit api group
     * @param $api_ids
     * @param $project_id
     * @param $group_id
     * @return bool
     */
    public function changeApiGroup(&$api_ids, &$project_id, &$group_id)
    {
        $db = getDatabase();
        $db->beginTransaction();
        $db->prepareExecuteAll("UPDATE eo_ams_api_cache SET eo_ams_api_cache.groupID = ? WHERE eo_ams_api_cache.apiID IN ($api_ids) AND eo_ams_api_cache.projectID = ?;", array(
            $group_id,
            $project_id
        ));
        if ($db->getAffectRow() < 1) {
            $db->rollback();
            return FALSE;
        }
        $db->prepareExecuteAll("UPDATE eo_ams_api SET eo_ams_api.groupID = ? WHERE eo_ams_api.apiID IN ($api_ids) AND eo_ams_api.projectID = ?;", array(
            $group_id,
            $project_id
        ));
        if ($db->getAffectRow() < 1) {
            $db->rollback();
            return FALSE;
        }
        $db->commit();
        return TRUE;
    }

    /**
     * Get API data
     * @param $project_id
     * @param $api_ids
     * @return array|bool
     */
    public function getApiData(&$project_id, &$api_ids)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT eo_ams_api_cache.apiID,eo_ams_api_cache.apiJson,eo_ams_api_cache.starred FROM eo_ams_api_cache WHERE eo_ams_api_cache.projectID = ? AND eo_ams_api_cache.apiID in ($api_ids);", array(
            $project_id
        ));
        $api_list = array();
        $i = 0;
        foreach ($result as $api) {
            $api_list[$i] = json_decode($api['apiJson'], TRUE);
            $api_list[$i]['baseInfo']['starred'] = $api['starred'];
            ++$i;
        }
        if ($api_list)
            return $api_list;
        else
            return FALSE;
    }

    /**
     * Import API
     * @param $group_id
     * @param $project_id
     * @param $data
     * @param $user_id
     * @return bool
     */
    public function importApi(&$group_id, &$project_id, &$data, &$user_id)
    {
        $db = getDatabase();
        try {
            $db->beginTransaction();
            if (is_array($data)) {
                foreach ($data as $api) {
                    
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
            $db->commit();
            return TRUE;
        } catch (\Exception $e) {
            $db->rollback();
            return FALSE;
        }
    }
    /**
     * updateApiStatusToDB
     * @param Int $api_ids APIID
     * @param Int $api_status APIstauts
     */
    Public Function updateApiStatusToDB(&$api_ids, &$api_status)
    {
    	$db = GetDatabase();
    	Try
    	{
    		$db->beginTransaction();
    		$db->prepareExecute("UPDATE eo_ams_api SET apiStatus  = ?, apiUpdateTime = ? WHERE apiID IN($api_ids)", Array(
    				$api_status,
    				Date("Y-m-d H:i:s", Time())
    				));
    		If($db->getAffectRow() < 1)
    		Throw New \PDOException("updateApi Error");
    		$result = $db->prepareExecuteAll("SELECT cacheID,apiJson FROM eo_ams_api_cache WHERE apiID IN ($api_ids)");
    		If($result)
    		{
    			Foreach($result As $api)
    			{
    				$api_Json = Json_decode($api ['apiJson'], TRUE);
    				$api_Json ['baseInfo'] ['apiStatus'] = $api_status;
    				$api_Json = Json_encode($api_Json);
    				$db->prepareExecute("UPDATE eo_ams_api_cache SET apiJson = ? WHERE cacheID = ?", Array(
    						$api_Json,
    						$api ['cacheID']
    						));
    			}
    		}
    		$db->commit();
    		return TRUE;
    	}catch (\Exception $e) {
    		$db->rollback();
    		return FALSE;
    	}
    }
    /**
     * 获取接口名称
     */
    public function getApiNames(&$api_id)
    {
    	$db = getDatabase();
    	$result = $db->prepareExecute("SELECT GROUP_CONCAT(DISTINCT eo_ams_api.apiName) AS apiName FROM eo_ams_api WHERE eo_ams_api.apiID IN($api_id);");
    	
    	if(empty($result))
    		return FALSE;
    		else
    			return $result['apiName'];
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

?>
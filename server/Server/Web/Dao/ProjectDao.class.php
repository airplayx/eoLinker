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

class ProjectDao
{

    /**
     * Create project
     *
     * @param $projectName string

     * @param $projectType int
     * @param $projectVersion string
     * @param $userID int
     * @return bool|array
     */
    public function addProject(&$projectName, &$projectType, &$projectVersion, &$userID)
    {
        $db = getDatabase();

        $db->beginTransaction();

        $db->prepareExecute('INSERT INTO eo_ams_project(eo_ams_project.projectName,eo_ams_project.projectType,eo_ams_project.projectVersion,eo_ams_project.projectUpdateTime) VALUES (?,?,?,?);', array(
            $projectName,
            $projectType,
            $projectVersion,
            date('Y-m-d H:i:s', time())
        ));

        if ($db->getAffectRow() < 1) {
            $db->rollback();
            return FALSE;
        }

        $projectID = $db->getLastInsertID();

        $db->prepareExecute('INSERT INTO eo_ams_conn_project (eo_ams_conn_project.projectID,eo_ams_conn_project.userID) VALUES (?,?);', array(
            $projectID,
            $userID
        ));
        if ($db->getAffectRow() > 0) {
            $db->commit();

            return array(
                'projectID' => $projectID,
                'projectType' => $projectType,
                'projectUpdateTime' => date("Y-m-d H:i:s", time()),
                'projectVersion' => $projectVersion
            );
        } else {
            $db->rollback();
            return FALSE;
        }
    }

    /**
     * check project permission
     *
     * @param $projectID int
     * @param $userID int
     * @return mixed
     */
    public function checkProjectPermission(&$projectID, &$userID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT projectID FROM `eo_ams_conn_project` WHERE projectID = ? AND userID = ?;', array(
            $projectID,
            $userID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }
    /**
     *  处理更新者，创建者，操作者命名
     */
    public function handleUserName($data, $type)
    {
    	if($type ==0)
    	{
    		// 只有更新者
    		foreach($data as &$k)
    		{
    			if(empty($k['updater']))
    			{
    				$k['updater'] = $k['uUserNickName'];
    			}
    			unset($k['uUserNickName']);
    		}
    	}
    	else if($type ==1)
    	{
    		// 只有创建者
    		foreach($data as &$k)
    		{
    			if(empty($k['creator']))
    			{
    				$k['creator'] = $k['cUserNickName'];
    			}
    			unset($k['cUserNickName']);
    		}
    	}
    	else if($type ==2)
    	{
    		// 只有操作人
    		foreach($data as &$k)
    		{
    			if(empty($k['operator']))
    			{
    				$k['operator'] = $k['oUserNickName'];
    			}
    			unset($k['oUserNickName']);
    		}
    	}
    	else
    	{
    		// 既然更新者又有操作者
    		foreach($data as &$k)
    		{
    			if(empty($k['updater']))
    			{
    				$k['updater'] = $k['uUserNickName'];
    			}
    			unset($k['uUserNickName']);
    			if(empty($k['creator']))
    			{
    				$k['creator'] = $k['cUserNickName'];
    			}
    			unset($k['cUserNickName']);
    		}
    	}
    	return $data;
    }
    /**
     * delete project
     *
     * @param $projectID int
     * @return bool
     */
    public function deleteProject(&$projectID)
    {
        $db = getDatabase();
        $db->beginTransaction();

        $db->prepareExecute('DELETE FROM eo_ams_project WHERE eo_ams_project.projectID = ?', array(
            $projectID
        ));

        if ($db->getAffectRow() < 1) {
            $db->rollback();
            return FALSE;
        }

        $db->prepareExecute('DELETE FROM eo_ams_conn_project WHERE eo_ams_conn_project.projectID = ? AND eo_ams_conn_project.userType = 0;', array(
            $projectID
        ));

        if ($db->getAffectRow() < 1) {
            $db->rollback();
            return FALSE;
        }

        $db->prepareExecuteAll('DELETE FROM eo_ams_api_group WHERE eo_ams_api_group.projectID = ?;', array($projectID));
        $db->prepareExecuteAll('DELETE FROM eo_ams_api_header WHERE eo_ams_api_header.apiID IN (SELECT eo_ams_api.apiID FROM eo_ams_api WHERE eo_ams_api.projectID = ?);', array($projectID));
        $db->prepareExecuteAll('DELETE FROM eo_ams_api_request_value WHERE eo_ams_api_request_value.paramID IN (SELECT eo_ams_api_request_param.paramID FROM eo_ams_api_request_param LEFT JOIN eo_ams_api ON eo_ams_api_request_param.apiID = eo_ams_api.apiID WHERE eo_ams_api.projectID = ?);', array($projectID));
        $db->prepareExecuteAll('DELETE FROM eo_ams_api_request_param WHERE eo_ams_api_request_param.apiID IN (SELECT eo_ams_api.apiID FROM eo_ams_api WHERE eo_ams_api.projectID = ?)', array($projectID));
        $db->prepareExecuteAll('DELETE FROM eo_ams_api_result_value WHERE eo_ams_api_result_value.paramID IN (SELECT eo_ams_api_result_param.paramID FROM eo_ams_api_result_param LEFT JOIN eo_ams_api ON eo_ams_api_result_param.apiID = eo_ams_api.apiID WHERE eo_ams_api.projectID = ?);', array($projectID));
        $db->prepareExecuteAll('DELETE FROM eo_ams_api_result_param WHERE eo_ams_api_result_param.apiID IN (SELECT eo_ams_api.apiID FROM eo_ams_api WHERE eo_ams_api.projectID = ?)', array($projectID));
        $db->prepareExecuteAll('DELETE FROM eo_ams_api_group WHERE eo_ams_api_group.projectID = ?;', array($projectID));
        $db->prepareExecuteAll('DELETE FROM eo_ams_api WHERE eo_ams_api.projectID = ?;', array($projectID));
        $db->prepareExecuteAll('DELETE FROM eo_ams_api_cache WHERE eo_ams_api_cache.projectID = ?;', array($projectID));

        $db->commit();
        return TRUE;
    }

    /**
     * get project list
     *
     * @param $userID int
     * @param $projectType int
     * @return bool|array
     */
    public function getProjectList(&$userID, &$projectType = -1)
    {
        $db = getDatabase();

        if ($projectType < 0) {
            $result = $db->prepareExecuteAll("SELECT eo_ams_project.projectID,eo_ams_project.projectName,eo_ams_project.projectType,eo_ams_project.projectUpdateTime,eo_ams_project.projectVersion,eo_ams_conn_project.userType FROM eo_ams_project INNER JOIN eo_ams_conn_project ON eo_ams_project.projectID = eo_ams_conn_project.projectID WHERE eo_ams_conn_project.userID=? ORDER BY eo_ams_project.projectUpdateTime DESC;", array(
                $userID
            ));
        } else {
            $result = $db->prepareExecuteAll("SELECT eo_ams_project.projectID,eo_ams_project.projectName,eo_ams_project.projectType,eo_ams_project.projectUpdateTime,eo_ams_project.projectVersion,eo_ams_conn_project.userType FROM eo_ams_project INNER JOIN eo_ams_conn_project ON eo_ams_project.projectID = eo_ams_conn_project.projectID WHERE eo_ams_conn_project.userID=? AND eo_ams_project.projectType=? ORDER BY eo_ams_project.projectUpdateTime DESC;", array(
                $userID,
                $projectType
            ));
        }

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * edit project
     *
     * @param $projectID int
     * @param $projectName string
     * @param $projectType int
     * @param $projectVersion string
     * @return bool
     */
    public function editProject(&$projectID, &$projectName, &$projectType, &$projectVersion)
    {
        $db = getDatabase();

        $db->prepareExecute('UPDATE eo_ams_project SET eo_ams_project.projectType = ?,eo_ams_project.projectName = ?, eo_ams_project.projectUpdateTime = ?, eo_ams_project.projectVersion = ? WHERE eo_ams_project.projectID= ?;', array(
            $projectType,
            $projectName,
            date('Y-m-d H:i:s', time()),
            $projectVersion,
            $projectID
        ));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * get project
     *
     * @param $projectID int
     * @param $userID int
     * @return bool|array
     */
    public function getProject(&$projectID, &$userID)
    {
        $db = getDatabase();
        $project_info = array();
        $project_info = $db->prepareExecute("SELECT eo_ams_project.projectID, eo_ams_project.projectName, eo_ams_project.projectType, eo_ams_project.projectUpdateTime,eo_ams_project.projectVersion,eo_ams_conn_project.userType FROM eo_ams_project INNER JOIN eo_ams_conn_project ON eo_ams_project.projectID = eo_ams_conn_project.projectID WHERE eo_ams_project.projectID= ? AND eo_ams_conn_project.userID = ?;", array(
            $projectID,
            $userID
        ));
        $api_count = $db->prepareExecute('SELECT COUNT(eo_ams_api.apiID) AS count FROM eo_ams_api WHERE eo_ams_api.projectID = ? AND eo_ams_api.removed = 0 AND eo_ams_api.groupID IN (SELECT groupID FROM eo_ams_api_group WHERE projectID = ?);', array(
            $projectID,
            $projectID
        ));
        $project_info['apiCount'] = $api_count['count'] ? $api_count['count'] : 0;
        $status_code_count = $db->prepareExecute('SELECT COUNT(eo_ams_project_status_code.codeID) AS count FROM eo_ams_project_status_code LEFT JOIN eo_ams_project_status_code_group ON eo_ams_project_status_code.groupID = eo_ams_project_status_code_group.groupID WHERE eo_ams_project_status_code_group.projectID = ?;', array(
            $projectID
        ));
        $project_info['statusCodeCount'] = $status_code_count['count'] ? $status_code_count['count'] : 0;
        $partner_count = $db->prepareExecute('SELECT COUNT(eo_ams_conn_project.connID) AS count FROM eo_ams_conn_project WHERE eo_ams_conn_project.projectID = ?;', array(
            $projectID
        ));
        $project_info['partnerCount'] = $partner_count['count'] ? $partner_count['count'] : 0;

        $project_info['importURL'] = (is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?g=Web&c=AutoGenerate&o=importApi';

        if (empty($project_info))
            return FALSE;
        else
            return $project_info;
    }

    /**
     * update project time
     *
     * @param $projectID int
     * @return bool
     */
    public function updateProjectUpdateTime(&$projectID)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_ams_project SET eo_ams_project.projectUpdateTime = ? WHERE eo_ams_project.projectID = ?;', array(
            date('Y-m-d H:i:s', time()),
            $projectID
        ));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }



    /**
     *get project name
     *
     * @param $projectID int
     * @return bool|array
     */
    public function getProjectName(&$projectID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT eo_ams_project.projectName FROM eo_ams_project WHERE eo_ams_project.projectID= ?;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }
    /**
     * 获取用户ID
     * @param int $space_id
     * @param int $connID
     * @return multitype:|boolean
     */
    public function getUserID(&$project_id, &$conn_ids)
    {
    	$db = getDatabase();
    	$result = $db->prepareExecuteAll("SELECT eo_ams_conn_project.userID FROM eo_ams_conn_project WHERE eo_ams_conn_project.projectID = ? AND eo_ams_conn_project.connID in(" .$conn_ids .");", array(
    			$project_id
    	));
    	if($result)
    		return $result;
    		else
    			return FALSE;
    }
    /**
     * export project
     *
     * @param $project_id int
     * @return bool|array
     */
    public function dumpProject(&$project_id)
    {
        $db = getDatabase();

        $dumpJson = array();


        $dumpJson['projectInfo'] = $db->prepareExecute("SELECT * FROM eo_ams_project WHERE eo_ams_project.projectID = ?;", array(
            $project_id
        ));

        $dumpJson['apiGroupList'] = array();

        $apiGroupList = $db->prepareExecuteAll("SELECT * FROM eo_ams_api_group WHERE eo_ams_api_group.projectID = ? AND eo_ams_api_group.isChild = 0;", array(
            $project_id
        ));

        $i = 0;
        foreach ($apiGroupList as $apiGroup) {
            $dumpJson['apiGroupList'][$i] = $apiGroup;

            $apiList = $db->prepareExecuteAll("SELECT eo_ams_api_cache.apiJson FROM eo_ams_api_cache WHERE eo_ams_api_cache.projectID = ? AND eo_ams_api_cache.groupID = ?;", array(
                $project_id,
                $apiGroup['groupID']
            ));
            $dumpJson['apiGroupList'][$i]['apiList'] = array();
            $j = 0;
            foreach ($apiList as $api) {
                $dumpJson['apiGroupList'][$i]['apiList'][$j] = json_decode($api['apiJson'], TRUE);
                // $dumpJson['apiGroupList'][$i]['apiList'][$j]['baseInfo']['starred'] = $api['starred'];
                ++$j;
            }

            $dumpJson['apiGroupList'][$i]['apiGroupChildList'] = array();
            $apiGroupChildList = $db->prepareExecuteAll('SELECT * FROM eo_ams_api_group WHERE eo_ams_api_group.projectID = ? AND eo_ams_api_group.parentGroupID = ?', array(
                $project_id,
                $apiGroup['groupID']
            ));
            $k = 0;
            if ($apiGroupChildList) {
                foreach ($apiGroupChildList as $apiChildGroup) {
                    $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k] = $apiChildGroup;
                    $apiList = $db->prepareExecuteAll("SELECT * FROM eo_ams_api_cache WHERE eo_ams_api_cache.projectID = ? AND eo_ams_api_cache.groupID = ?;", array(
                        $project_id,
                        $apiChildGroup['groupID']
                    ));
                    $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k]['apiList'] = array();
                    $l = 0;
                    foreach ($apiList as $api) {
                        $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k]['apiList'][$l] = json_decode($api['apiJson'], TRUE);
                        $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k]['apiList'][$l]['baseInfo']['starred'] = $api['starred'];
                        ++$l;
                    }
                    $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k]['apiGroupChildList'] = array();
                    $groupChildList = $db->prepareExecuteAll('SELECT * FROM eo_ams_api_group WHERE eo_ams_api_group.projectID = ? AND eo_ams_api_group.parentGroupID = ?;', array(
                        $project_id,
                        $apiChildGroup['groupID']
                    ));
                    $o = 0;
                    foreach ($groupChildList as $group) {
                        $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$i]['apiGroupChildList'][$o] = $group;
                        $apiList = $db->prepareExecuteAll("SELECT * FROM eo_ams_api_cache WHERE eo_ams_api_cache.projectID = ? AND eo_ams_api_cache.groupID = ?;", array(
                            $project_id,
                            $group['groupID']
                        ));
                        $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k]['apiGroupChildList'][$o]['apiList'] = array();
                        $p = 0;
                        foreach ($apiList as $api) {
                            $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k]['apiGroupChildList'][$o]['apiList'][$l] = json_decode($api['apiJson'], TRUE);
                            $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k]['apiGroupChildList'][$o]['apiList'][$l]['baseInfo']['starred'] = $api['starred'];
                            ++$p;
                        }
                        ++$o;
                    }
                    ++$k;
                }
            }
            ++$i;
        }

        $dumpJson['statusCodeGroupList'] = array();
        $statusCodeGroupList = $db->prepareExecuteAll("SELECT * FROM eo_ams_project_status_code_group WHERE eo_ams_project_status_code_group.projectID = ? AND isChild = 0;", array(
            $project_id
        ));

        $i = 0;
        foreach ($statusCodeGroupList as $statusCodeGroup) {
            $dumpJson['statusCodeGroupList'][$i] = $statusCodeGroup;

            $statusCodeList = $db->prepareExecuteAll("SELECT * FROM eo_ams_project_status_code WHERE eo_ams_project_status_code.groupID = ?;", array(
                $statusCodeGroup['groupID']
            ));

            $dumpJson['statusCodeGroupList'][$i]['statusCodeList'] = array();
            $j = 0;
            foreach ($statusCodeList as $statusCode) {
                $dumpJson['statusCodeGroupList'][$i]['statusCodeList'][$j] = $statusCode;
                ++$j;
            }
            $statusCodeGroupChildList = $db->prepareExecuteAll("SELECT * FROM eo_ams_project_status_code_group WHERE eo_ams_project_status_code_group.projectID = ? AND parentGroupID = ?;", array(
                $project_id,
                $statusCodeGroup['groupID']
            ));
            $k = 0;
            $dumpJson['statusCodeGroupList'][$i]['statusCodeGroupChildList'] = array();
            if ($statusCodeGroupChildList) {
                foreach ($statusCodeGroupChildList as $statusCodeChildGroup) {
                    $dumpJson['statusCodeGroupList'][$i]['statusCodeGroupChildList'][$k] = $statusCodeChildGroup;

                    $statusCodeList = $db->prepareExecuteAll("SELECT * FROM eo_ams_project_status_code WHERE eo_ams_project_status_code.groupID = ?;", array(
                        $statusCodeChildGroup['groupID']
                    ));

                    $dumpJson['statusCodeGroupList'][$i]['statusCodeGroupChildList'][$k]['statusCodeList'] = array();
                    $l = 0;
                    foreach ($statusCodeList as $statusCode) {
                        $dumpJson['statusCodeGroupList'][$i]['statusCodeGroupChildList'][$k]['statusCodeList'][$l] = $statusCode;
                        ++$l;
                    }

                    $secondStatusCodeGroupChildList = $db->prepareExecuteAll("SELECT * FROM eo_ams_project_status_code_group WHERE eo_ams_project_status_code_group.projectID = ? AND parentGroupID = ?;", array(
                        $project_id,
                        $statusCodeChildGroup['groupID']
                    ));
                    $dumpJson['statusCodeGroupList'][$i]['statusCodeGroupChildList'][$k]['statusCodeGroupChildList'] = array();
                    if ($secondStatusCodeGroupChildList) {
                        $m = 0;
                        foreach ($secondStatusCodeGroupChildList as $secondStatusCodeChildGroup) {
                            $dumpJson['statusCodeGroupList'][$i]['statusCodeGroupChildList'][$k]['statusCodeGroupChildList'][$m] = $secondStatusCodeChildGroup;
                            $statusCodeList = $db->prepareExecuteAll("SELECT * FROM eo_ams_project_status_code WHERE eo_ams_project_status_code.groupID = ?;", array(
                                $statusCodeChildGroup['groupID']
                            ));

                            $dumpJson['statusCodeGroupList'][$i]['statusCodeGroupChildList'][$k]['statusCodeGroupChildList'][$m]['statusCodeList'] = array();
                            $l = 0;
                            foreach ($statusCodeList as $statusCode) {
                                $dumpJson['statusCodeGroupList'][$i]['statusCodeGroupChildList'][$k]['statusCodeGroupChildList'][$m]['statusCodeList'][$l] = $statusCode;
                                ++$l;
                            }
                            ++$m;
                        }
                    }
                    ++$k;
                }
            }
            ++$i;
        }

        $dumpJson['env'] = array();
        $envList = $db->prepareExecuteAll('SELECT eo_ams_api_env.envID,eo_ams_api_env.envName,eo_ams_api_env.frontURI,eo_ams_api_env.envAuth,eo_ams_api_env.envHeader,eo_ams_api_env.globalVariable,eo_ams_api_env.additionalVariable FROM eo_ams_api_env WHERE eo_ams_api_env.projectID = ?;', array($project_id));
        $dumpJson['env'] = $envList ? $envList : array();

        $dumpJson['pageGroupList'] = array();
        $documentGroupList = $db->prepareExecuteAll('SELECT eo_ams_project_document_group.* FROM eo_ams_project_document_group WHERE eo_ams_project_document_group.projectID = ? AND eo_ams_project_document_group.isChild = 0;', array(
            $project_id
        ));
        $i = 0;
        foreach ($documentGroupList as $documentGroup) {
            $dumpJson['pageGroupList'][$i] = $documentGroup;
            $dumpJson['pageGroupList'][$i]['pageList'] = array();
            $documentList = $db->prepareExecuteAll('SELECT eo_ams_project_document.documentID AS pageID,eo_ams_project_document.groupID,eo_ams_project_document.projectID,eo_ams_project_document.contentType,eo_ams_project_document.contentRaw,eo_ams_project_document.content,eo_ams_project_document.title,eo_ams_project_document.updateTime,eo_ams_project_document.userID AS authorID FROM eo_ams_project_document WHERE eo_ams_project_document.groupID = ?;', array($documentGroup['groupID']));

            $j = 0;
            foreach ($documentList as $document) {
                $dumpJson['pageGroupList'][$i]['pageList'][$j] = $document;
                $dumpJson['pageGroupList'][$i]['pageList'][$j]['groupName'] = $documentGroup['groupName'];
                $j++;
            }

            $documentGroupChildList = $db->prepareExecuteAll('SELECT eo_ams_project_document_group.* FROM eo_ams_project_document_group WHERE eo_ams_project_document_group.projectID = ? AND eo_ams_project_document_group.parentGroupID = ? AND eo_ams_project_document_group.isChild = 1;', array(
                $project_id,
                $documentGroup['groupID']
            ));

            $k = 0;
            $dumpJson['pageGroupList'][$i]['pageGroupChildList'] = array();
            if ($documentGroupChildList) {
                foreach ($documentGroupChildList as $documentChildGroup) {
                    $dumpJson['pageGroupList'][$i]['pageGroupChildList'][$k] = $documentChildGroup;
                    $dumpJson['pageGroupList'][$i]['pageGroupChildList'][$k]['pageList'] = array();
                    $documentList = $db->prepareExecuteAll('SELECT eo_ams_project_document.documentID AS pageID,eo_ams_project_document.groupID,eo_ams_project_document.projectID,eo_ams_project_document.contentType,eo_ams_project_document.contentRaw,eo_ams_project_document.content,eo_ams_project_document.title,eo_ams_project_document.updateTime,eo_ams_project_document.userID AS authorID FROM eo_ams_project_document WHERE eo_ams_project_document.groupID = ?;', array($documentChildGroup['groupID']));
                    $l = 0;
                    foreach ($documentList as $document) {
                        $dumpJson['pageGroupList'][$i]['pageGroupChildList'][$k]['pageList'][$l] = $document;
                        $dumpJson['pageGroupList'][$i]['pageGroupChildList'][$k]['pageList'][$l]['groupName'] = $documentChildGroup['groupName'];
                        $l++;
                    }
                    $secondDocumentGroupChildList = $db->prepareExecuteAll('SELECT eo_ams_project_document_group.* FROM eo_ams_project_document_group WHERE eo_ams_project_document_group.projectID = ? AND eo_ams_project_document_group.parentGroupID = ? AND eo_ams_project_document_group.isChild = 2;', array(
                        $project_id,
                        $documentGroup['groupID']
                    ));
                    if ($secondDocumentGroupChildList) {
                        $m = 0;
                        foreach ($secondDocumentGroupChildList as $secondDocumentChildGroup) {
                            $dumpJson['pageGroupList'][$i]['pageGroupChildList'][$k]['pageGroupChildList'][$m] = $secondDocumentChildGroup;
                            $dumpJson['pageGroupList'][$i]['pageGroupChildList'][$k]['pageGroupChildList'][$m]['pageList'] = array();
                            $documentList = $db->prepareExecuteAll('SELECT eo_ams_project_document.documentID AS pageID,eo_ams_project_document.groupID,eo_ams_project_document.projectID,eo_ams_project_document.contentType,eo_ams_project_document.contentRaw,eo_ams_project_document.content,eo_ams_project_document.title,eo_ams_project_document.updateTime,eo_ams_project_document.userID AS authorID FROM eo_ams_project_document WHERE eo_ams_project_document.groupID = ?;', array(
                                $secondDocumentChildGroup['groupID']
                            ));
                            $l = 0;
                            foreach ($documentList as $document) {
                                $dumpJson['pageGroupList'][$i]['pageGroupChildList'][$k]['pageGroupChildList'][$m]['pageList'][$l] = $document;
                                $dumpJson['pageGroupList'][$i]['pageGroupChildList'][$k]['pageGroupChildList'][$m]['pageList'][$l]['groupName'] = $secondDocumentChildGroup['groupName'];
                                $l++;
                            }
                        }
                    }
                    $k++;
                }
            }
            $i++;
        }

        $dumpJson['caseGroupList'] = array();
        $case_group_list = $db->prepareExecuteAll("SELECT * FROM eo_ams_project_test_case_group WHERE eo_ams_project_test_case_group.projectID = ? AND eo_ams_project_test_case_group.isChild = ?;", array(
            $project_id,
            0
        ));
        if ($case_group_list) {
            $i = 0;
            foreach ($case_group_list as $caseGroup) {
                $dumpJson['caseGroupList'][$i] = $caseGroup;
                $case_list = $db->prepareExecuteAll("SELECT eo_ams_project_test_case.caseID,eo_ams_project_test_case.caseName,eo_ams_project_test_case.caseDesc,eo_ams_project_test_case.caseType,eo_ams_project_test_case.caseCode FROM eo_ams_project_test_case WHERE eo_ams_project_test_case.groupID = ? AND eo_ams_project_test_case.projectID = ?", array(
                    $caseGroup['groupID'],
                    $project_id
                ));
                if ($case_list) {
                    $j = 0;
                    $dumpJson['caseGroupList'][$i]['caseList'] = array();
                    foreach ($case_list as $case) {
                        $dumpJson['caseGroupList'][$i]['caseList'][$j] = $case;
                        $dumpJson['caseGroupList'][$i]['caseList'][$j]['groupName'] = $caseGroup['groupName'];
                        $dumpJson['caseGroupList'][$i]['caseList'][$j]['beforeCaseList'] = array();
                        $dumpJson['caseGroupList'][$i]['caseList'][$j]['caseSingleList'] = $db->prepareExecuteAll('SELECT eo_ams_project_test_case_single.connID,eo_ams_project_test_case_single.caseData,eo_ams_project_test_case_single.caseCode,eo_ams_project_test_case_single.statusCode,eo_ams_project_test_case_single.matchType,eo_ams_project_test_case_single.matchRule,eo_ams_project_test_case_single.apiName,eo_ams_project_test_case_single.apiURI,eo_ams_project_test_case_single.apiRequestType,eo_ams_project_test_case_single.orderNumber FROM eo_ams_project_test_case_single WHERE caseID = ?;', array(
                            $case['caseID']
                        ));
                        ++$j;
                    }
                }
                $child_group_list = $db->prepareExecuteAll('SELECT eo_ams_project_test_case_group.groupID,eo_ams_project_test_case_group.groupName FROM eo_ams_project_test_case_group WHERE eo_ams_project_test_case_group.parentGroupID = ? AND eo_ams_project_test_case_group.projectID = ? AND eo_ams_project_test_case_group.isChild = ?;', array(
                    $caseGroup['groupID'],
                    $project_id,
                    1
                ));
                if ($child_group_list) {
                    $k = 0;
                    $dumpJson['caseGroupList'][$i]['caseChildGroupList'] = array();
                    foreach ($child_group_list as $child_group) {
                        $dumpJson['caseGroupList'][$i]['caseChildGroupList'][$k] = $child_group;
                        $case_list = $db->prepareExecuteAll("SELECT eo_ams_project_test_case.caseID,eo_ams_project_test_case.caseName,eo_ams_project_test_case.caseDesc,eo_ams_project_test_case.caseType,eo_ams_project_test_case.caseCode FROM eo_ams_project_test_case WHERE eo_ams_project_test_case.groupID = ? AND eo_ams_project_test_case.projectID = ?", array(
                            $child_group['groupID'],
                            $project_id
                        ));
                        if ($case_list) {
                            $x = 0;
                            $dumpJson['caseGroupList'][$i]['caseChildGroupList'][$k]['caseList'] = array();
                            foreach ($case_list as $case) {
                                $dumpJson['caseGroupList'][$i]['caseChildGroupList'][$k]['caseList'][$x] = $case;
                                $dumpJson['caseGroupList'][$i]['caseChildGroupList'][$k]['caseList'][$x]['groupName'] = $child_group['groupName'];
                                $dumpJson['caseGroupList'][$i]['caseChildGroupList'][$k]['caseList'][$x]['beforeCaseList'] = array();
                                $dumpJson['caseGroupList'][$i]['caseChildGroupList'][$k]['caseList'][$x]['caseSingleList'] = $db->prepareExecuteAll('SELECT eo_ams_project_test_case_single.connID,eo_ams_project_test_case_single.caseData,eo_ams_project_test_case_single.caseCode,eo_ams_project_test_case_single.statusCode,eo_ams_project_test_case_single.matchType,eo_ams_project_test_case_single.matchRule,eo_ams_project_test_case_single.apiName,eo_ams_project_test_case_single.apiURI,eo_ams_project_test_case_single.apiRequestType,eo_ams_project_test_case_single.orderNumber FROM eo_ams_project_test_case_single WHERE caseID = ?;', array(
                                    $case['caseID']
                                ));
                                ++$x;
                            }
                        }
                        $second_child_group_list = $db->prepareExecuteAll('SELECT eo_ams_project_test_case_group.groupID,eo_ams_project_test_case_group.groupName FROM eo_ams_project_test_case_group WHERE eo_ams_project_test_case_group.parentGroupID = ? AND eo_ams_project_test_case_group.projectID = ? AND eo_ams_project_test_case_group.isChild = ?;', array(
                            $child_group['groupID'],
                            $project_id,
                            2
                        ));
                        if ($second_child_group_list) {
                            $m = 0;
                            $dumpJson['caseGroupList'][$i]['caseChildGroupList'] = array();
                            foreach ($second_child_group_list as $second_child_group) {
                                $dumpJson['caseGroupList'][$i]['caseChildGroupList'][$k]['caseChildGroupList'][$m] = $second_child_group;
                                $case_list = $db->prepareExecuteAll("SELECT eo_ams_project_test_case.caseID,eo_ams_project_test_case.caseName,eo_ams_project_test_case.caseDesc,eo_ams_project_test_case.caseType,eo_ams_project_test_case.caseCode FROM eo_ams_project_test_case WHERE eo_ams_project_test_case.groupID = ? AND eo_ams_project_test_case.projectID = ?", array(
                                    $second_child_group['groupID'],
                                    $project_id
                                ));
                                if ($case_list) {
                                    $x = 0;
                                    $dumpJson['caseGroupList'][$i]['caseChildGroupList'][$k]['caseChildGroupList'][$m]['caseList'] = array();
                                    foreach ($case_list as $case) {
                                        $dumpJson['caseGroupList'][$i]['caseChildGroupList'][$k]['caseChildGroupList'][$m]['caseList'][$x] = $case;
                                        $dumpJson['caseGroupList'][$i]['caseChildGroupList'][$k]['caseChildGroupList'][$m]['caseList'][$x]['groupName'] = $second_child_group['groupName'];
                                        $dumpJson['caseGroupList'][$i]['caseChildGroupList'][$k]['caseChildGroupList'][$m]['caseList'][$x]['beforeCaseList'] = array();
                                        $dumpJson['caseGroupList'][$i]['caseChildGroupList'][$k]['caseChildGroupList'][$m]['caseList'][$x]['caseSingleList'] = $db->prepareExecuteAll('SELECT eo_ams_project_test_case_single.connID,eo_ams_project_test_case_single.caseData,eo_ams_project_test_case_single.caseCode,eo_ams_project_test_case_single.statusCode,eo_ams_project_test_case_single.matchType,eo_ams_project_test_case_single.matchRule,eo_ams_project_test_case_single.apiName,eo_ams_project_test_case_single.apiURI,eo_ams_project_test_case_single.apiRequestType,eo_ams_project_test_case_single.orderNumber FROM eo_ams_project_test_case_single WHERE caseID = ?;', array(
                                            $case['caseID']
                                        ));
                                        ++$x;
                                    }
                                }

                                ++$m;
                            }
                        }
                        ++$k;
                    }
                }
                ++$i;
            }
        }

        if (empty($dumpJson))
            return FALSE;
        else
            return $dumpJson;
    }
    /**
     * 获取用户项目权限
     * @param int $space_id 公司ID
     * @param int $user_id 用户ID
     * @param int $project_id 项目ID
     */
    public function getUserProjectPermissionFromDB(&$project_hash_key,&$userID)
    {
    	$db = getDatabase();
    	$result = $db->prepareExecute('SELECT eo_ams_conn_project.userType FROM eo_ams_conn_project WHERE  eo_ams_conn_project.projectID = ? AND eo_ams_conn_project.userID = ?;', array(
    			$project_hash_key,
    			$userID
    	));
    	if($result)
    		return $result;
    		else
    			return FALSE;
    }
    /**
     *get API number
     *
     * @param $projectID int
     * @return bool|array
     */
    public function getApiNum(&$projectID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT COUNT(*) AS num FROM eo_ams_api WHERE eo_ams_api.removed = 0 AND eo_ams_api.groupID IN (SELECT groupID FROM eo_ams_api_group WHERE eo_ams_api.projectID = ?);', array(
            $projectID
        ));

        if (isset($result))
            return $result;
        else
            return FALSE;
    }
}

?>
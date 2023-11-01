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

class StatusCodeDao
{
    /**
     * add status code
     * @param $groupID int groupID
     * @param $codeDesc string 
     * @param $code string status code
     * @return bool|int
     */
	public function addCode(&$projectID,&$groupID, &$status_code_list)
    {
    	$db = getDatabase();
    	$k = 0;
    	$status_codes = $status_code_list;
    	for ($i = 0; $i < count($status_code_list); $i++)
    	{
    		  $k = 0;
    			$db -> prepareExecute('INSERT INTO eo_ams_project_status_code (eo_ams_project_status_code.groupID,eo_ams_project_status_code.code,eo_ams_project_status_code.codeDescription) VALUES (?,?,?);', array(
    					$groupID,
    					$status_code_list[$i]['code'],
    					$status_code_list[$i]['codeDesc'],
    			));
    			if(is_numeric($db->getAffectRow()))
    				$k ++;
    	}
    	if($k)
    		return $status_codes;
    		else
    			return FALSE;
	}

    /**
     * delete statuscode
     * @param $codeID int statuscodeID
     * @return bool
     */
    public function deleteCode(&$codeID)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_ams_project_status_code WHERE eo_ams_project_status_code.codeID = ?;', array($codeID));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * get code list
     * @param $groupID int
     * @return bool|array
     */
    public function getCodeList(&$groupID)
    {
    	$db = getDatabase();
    	
    	//获取多级分组列表
    	$group_id_list = $db -> prepareExecuteAll('SELECT eo_ams_project_status_code_group.groupID FROM eo_ams_project_status_code_group WHERE eo_ams_project_status_code_group.parentGroupID = ?;', array($groupID));
    	
    	$group_sql = $groupID;
    	//如果存在子分组,则拼接搜索的范围
    	if (is_array($group_id_list))
    	{
    		foreach ($group_id_list as $child_group_id)
    		{
    			$group_sql .= ",{$child_group_id['groupID']}";
    			$id_list = $db -> prepareExecuteAll('SELECT eo_ams_project_status_code_group.groupID FROM eo_ams_project_status_code_group WHERE eo_ams_project_status_code_group.parentGroupID = ?;', array($child_group_id['groupID']));
    			if(is_array($id_list))
    			{
    				foreach ($id_list as $id)
    				{
    					$group_sql .= ",{$id['groupID']}";
    				}
    			}
    		}
    	}
    	
    	
    	$result = $db -> prepareExecuteAll("SELECT eo_ams_project_status_code.codeID,eo_ams_project_status_code.code,eo_ams_project_status_code.codeDescription,
    			a.groupName,a.groupID,a.parentGroupID,IFNULL(b.parentGroupID,0) as topParentGroupID  FROM eo_ams_project_status_code INNER JOIN eo_ams_project_status_code_group a ON eo_ams_project_status_code.groupID = a.groupID LEFT JOIN eo_ams_project_status_code_group b ON a.parentGroupID = b.groupID WHERE eo_ams_project_status_code.groupID IN ($group_sql) ORDER BY eo_ams_project_status_code.code ASC;");
    	
    	if (empty($result))
    		return array();
    		else
    			return $result;
    }

    /**
     * get all code list
     * @param $projectID int 
     * @return bool|array
     */
    public function getAllCodeList(&$projectID)
    {
    	$db = getDatabase();
    	
    	$result = $db -> prepareExecuteAll('SELECT eo_ams_project_status_code.codeID,eo_ams_project_status_code.code,eo_ams_project_status_code.codeDescription,a.groupID,IFNULL(b.parentGroupID,0) as topParentGroupID,a.parentGroupID,a.groupName FROM eo_ams_project_status_code INNER JOIN eo_ams_project_status_code_group a ON eo_ams_project_status_code.groupID = a.groupID LEFT JOIN eo_ams_project_status_code_group b ON a.parentGroupID = b.groupID WHERE a.projectID = ? ORDER BY eo_ams_project_status_code.code ASC;', array($projectID));
    	
    	if (!empty($result))
    	{
    		foreach ($result as &$status_code)
    		{
    			$status_code['topParentGroupID'] = intval($status_code['topParentGroupID']);
    		}
    		return $result;
    	}
    	else
    		return array();
    }

    /**
     * edit code
     * @param $groupID int 
     * @param $codeID int 
     * @param $code string 
     * @param $codeDesc string 
     * @return bool
     */
    public function editCode(&$groupID, &$codeID, &$code, &$codeDesc)
    {
        $db = getDatabase();

        $db->prepareExecute('UPDATE eo_ams_project_status_code SET eo_ams_project_status_code.groupID = ?, eo_ams_project_status_code.code = ? ,eo_ams_project_status_code.codeDescription = ? WHERE codeID = ?;', array($groupID, $code, $codeDesc, $codeID));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * check statu code permission
     * @param $codeID int 
     * @param $userID int 
     * @return bool|int
     */
    public function checkStatusCodePermission(&$codeID, &$userID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_ams_conn_project.projectID FROM eo_ams_project_status_code INNER JOIN eo_ams_conn_project INNER JOIN eo_ams_project_status_code_group ON eo_ams_conn_project.projectID = eo_ams_project_status_code_group.projectID AND eo_ams_project_status_code_group.groupID = eo_ams_project_status_code.groupID WHERE codeID = ? AND userID = ?;', array($codeID, $userID));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }

    /**
     * search status code
     * @param $projectID int 
     * @param $tips string
     * @return bool|array
     */
    public function searchStatusCode(&$projectID, &$tips)
    {
        $db = getDatabase();

        $result = $db->prepareExecuteAll('SELECT eo_ams_project_status_code_group.groupID,eo_ams_project_status_code_group.parentGroupID,eo_ams_project_status_code_group.groupName,eo_ams_project_status_code.codeID,eo_ams_project_status_code.code,eo_ams_project_status_code.codeDescription FROM eo_ams_project_status_code INNER JOIN eo_ams_project_status_code_group ON eo_ams_project_status_code.groupID = eo_ams_project_status_code_group.groupID WHERE projectID = ? AND (eo_ams_project_status_code.code LIKE ? OR eo_ams_project_status_code.codeDescription LIKE ?);', array($projectID, '%' . $tips . '%', '%' . $tips . '%'));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get status code num
     * @param $projectID int
     * @return bool|int
     */
    public function getStatusCodeNum(&$projectID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT COUNT(*) AS num FROM eo_ams_project_status_code LEFT JOIN eo_ams_project_status_code_group ON eo_ams_project_status_code.groupID = eo_ams_project_status_code_group.groupID WHERE eo_ams_project_status_code_group.projectID = ?;', array($projectID));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * delete code
     * @param $code_ids string
     * @return bool
     */
    public function deleteCodes(&$code_ids)
    {
        $db = getDatabase();
        $db->prepareExecuteAll("DELETE FROM eo_ams_project_status_code WHERE codeID IN ($code_ids)", array());
        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * get status code
     * @param $code_ids
     * @return bool
     */
    public function getStatusCodes(&$code_ids)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT GROUP_CONCAT(DISTINCT eo_ams_project_status_code.code) AS statusCodes FROM eo_ams_project_status_code WHERE eo_ams_project_status_code.codeID IN ($code_ids)", array());
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['statusCodes'];
        }
    }

    /**
     * add status code by excel
     * @param $group_id
     * @param $code_list
     * @return bool
     */
    public function addStatusCodeByExcel(&$group_id, &$code_list)
    {
        $db = getDatabase();
        foreach ($code_list as $code) {
            $db->prepareExecute('INSERT INTO eo_ams_project_status_code (code,codeDescription,groupID) VALUES (?,?,?);', array(
                $code['code'],
                $code['codeDesc'],
                $group_id
            ));
            if ($db->getAffectRow() < 1) {
                return FALSE;
            }
        }
        return TRUE;
    }
}

?>
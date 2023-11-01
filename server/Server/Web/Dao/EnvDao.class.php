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

class EnvDao
{
    /**
     * get env list
     * @param $projectID int projectID
     * @return bool
     */
    public function getEnvList(&$projectID)
    {
        $db = getDatabase();

        $envList = $db->prepareExecuteAll("SELECT eo_ams_api_env.envID,eo_ams_api_env.envName,eo_ams_api_env.envDesc,eo_ams_api_env.envAuth,eo_ams_api_env.frontURI,eo_ams_api_env.envHeader,eo_ams_api_env.globalVariable,eo_ams_api_env.additionalVariable FROM eo_ams_api_env WHERE eo_ams_api_env.projectID = ? ORDER BY eo_ams_api_env.envID DESC;", array($projectID));
     

        if (empty($envList))
            return FALSE;
        else
            return $envList;
    }
    /**
     * 获取环境信息
     * @param int $env_id 环境ID
     */
    public function getEnvFromDB(&$env_id)
    {
    	$db = getDatabase();
    	
    	$env = $db->prepareExecute("SELECT * FROM eo_ams_api_env WHERE eo_ams_api_env.envID = ?;",array($env_id));
    	if($env)
    	{
    		if($env['envAuth'])
    		{
    			$env['envAuth'] = json_decode($env['envAuth'], TRUE);
    		}
    		else
    		{
    			$env['envAuth'] = new \stdClass();
    		}
    		$env ['headerList'] = json_decode($env['envHeader'], TRUE) ? json_decode($env['envHeader'], TRUE) : array();
    		$env ['paramList'] = json_decode($env['globalVariable'], TRUE) ? json_decode($env['globalVariable'], TRUE) : array();
    		$env ['additionalParamList'] = json_decode($env['additionalVariable'], TRUE) ? json_decode($env['additionalVariable'], TRUE) : array();
    		unset($env['envHeader']);
    		unset($env['globalVariable']);
    		unset($env['additionalVariable']);
    		return $env;
    	}
    	else
    		return FALSE;
    }
    /**
     * add env
     * @param int $projectID projectID
     * @param string $envName Env name
     * @param string $front_uri pre URI
     * @param array $headers request head
     * @param array $params 
     * @param array $additional_params 
     * @return bool|int
     */
    public function addEnv(&$projectID, &$env_name,&$env_desc, &$front_uri, &$env_header, &$env_auth, &$global_variable,  &$additional_variable)
    {
        $db = getDatabase();
        $env_id = $db->getLastInsertID();
            $db->prepareExecute("INSERT INTO eo_ams_api_env (eo_ams_api_env.envName,eo_ams_api_env.envDesc,eo_ams_api_env.projectID,eo_ams_api_env.frontURI,eo_ams_api_env.envHeader,eo_ams_api_env.envAuth,eo_ams_api_env.globalVariable,eo_ams_api_env.additionalVariable) VALUES (?,?,?,?,?,?,?,?);", array(
                $env_name,
            	$env_desc,
                $projectID,
            	$front_uri,
            	$env_header,
            	$env_auth,
            	$global_variable,
                $additional_variable
            ));
            if (is_numeric($db->getAffectRow()))
            	return $db->getLastInsertID();
            	else
            		return FALSE;
    }

    /**
     * Delete Env
     * @param $projectID int projectID
     * @param $env_id int EnvID
     * @return bool
     */
    public function deleteEnv(&$projectID, &$env_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT * FROM eo_ams_api_env WHERE eo_ams_api_env.envID = ?;', array(
            $env_id
        ));      
        $db->prepareExecute("DELETE FROM eo_ams_api_env WHERE eo_ams_api_env.envID = ? AND eo_ams_api_env.projectID = ?;", array(
            $env_id,
            $projectID
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
     * Edit env
     * @param $env_id int envID
     * @param $envName string env name
     * @param $front_uri string pre uri
     * @param $headers array request header
     * @param $params array 
     * @param $additional_params array 
     * @return bool
     */
    public function editEnv(&$env_id, &$env_name, &$env_desc,&$front_uri, &$env_header, &$env_auth, &$global_variable,  &$additional_variable)
    {
    	$db = getDatabase();
    	$db -> prepareExecute("Update eo_ams_api_env SET eo_ams_api_env.envName=?,eo_ams_api_env.envDesc=?,eo_ams_api_env.frontURI = ?,eo_ams_api_env.envHeader = ?,eo_ams_api_env.envAuth = ?,eo_ams_api_env.globalVariable = ?,eo_ams_api_env.additionalVariable = ?  WHERE eo_ams_api_env.envID = ?;", array(
    			$env_name,
    			$env_desc,
    			$front_uri,   
    			$env_header,
    			$env_auth,  			
    			$global_variable,
    			$additional_variable,
    			$env_id    			
    	));
    	if(is_numeric($db->getAffectRow()))
    		return TRUE;
    		else
    			return FALSE;
    }

    /**
     * Get env Info
     * @param int $env_id envID
     * @return bool|array
     */
    public function getEnvInfoFromDB(&$env_id)
    {
        $db = getDatabase();
        $env = $db->prepareExecute("SELECT eo_ams_api_env.envID,eo_ams_api_env.envName FROM eo_ams_api_env WHERE eo_ams_api_env.envID = ?;", array($env_id));
        $env['frontURIList'] = $db->prepareExecuteAll("SELECT eo_ams_api_env.frontURI FROM eo_ams_api_env WHERE eo_ams_api_env.envID = ?;", array($env['envID']));
        $env['authList'] =  $db->prepareExecuteAll("SELECT eo_ams_api_env.envAuth FROM eo_ams_api_env WHERE eo_ams_api_env.envID = ?;", array($env['envID']));
        $env['headerList'] = $db->prepareExecuteAll("SELECT eo_ams_api_env.envHeader FROM eo_ams_api_env WHERE eo_ams_api_env.envID = ?;", array($env['envID']));
        $env['paramList'] = $db->prepareExecuteAll("SELECT eo_ams_api_env.globalVariable FROM eo_ams_api_env WHERE eo_ams_api_env.envID = ?;", array($env['envID']));
        $env['additionalParamList'] = $db->prepareExecuteAll('SELECT eo_ams_api_env.additionalVariable FROM eo_ams_api_env WHERE eo_ams_api_env.envID = ?;', array($env['envID']));
        if ($env)
            return $env;
        else
            return FALSE;
    }

    /**
     * get env name
     * @param $envID int envID
     * @return bool
     */
    public function getEnvName(&$envID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT eo_ams_api_env.envName FROM eo_ams_api_env WHERE eo_ams_api_env.envID IN($envID);", array($envID));

        if (empty($result))
            return FALSE;
        else
            return $result['envName'];
    }
    /**
     * Delete Env
     */
    public function batchDeleteEnv(&$project_id, &$env_ids)
    {
    	$db = getDatabase();
    	$db -> prepareExecute("DELETE FROM eo_ams_api_env WHERE envID IN($env_ids) AND projectID = ?;",array($project_id));
    	if(is_numeric($db->getAffectRow()))
    		return TRUE;
    		else
    		return FALSE;
    }
    /**
     * Check env permission
     * @param $envID int envID
     * @param $userID int userID
     * @return bool
     */
    public function checkEnvPermission(&$envID, &$userID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_ams_conn_project.projectID FROM eo_ams_api_env LEFT JOIN eo_ams_conn_project ON eo_ams_api_env.projectID = eo_ams_conn_project.projectID WHERE eo_ams_api_env.envID = ? AND eo_ams_conn_project.userID = ?;', array(
            $envID,
            $userID
        ));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['projectID'];
        }
    }
}
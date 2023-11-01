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

class EnvModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * Get Env List
     * @param $project_id int Project Number ID
     * @return bool|array
     */
    public function getEnvList(&$project_id)
    {
        $projectDao = new ProjectDao;
        if (!$projectDao->checkProjectPermission($project_id, $_SESSION['userID'])) {
            return FALSE;
        }
        $env_dao = new EnvDao;
       $result = $env_dao->getEnvList($project_id);
        if($result)
        {
        	foreach ($result as &$env)
        	{
        		$env['envAuth'] = json_decode($env['envAuth'], TRUE) ? json_decode($env['envAuth'], TRUE) : new \stdClass();
        		$env['headerList'] = json_decode($env['envHeader'], TRUE) ? json_decode($env['envHeader'], TRUE) : array();
        		$env['paramList'] = json_decode($env['globalVariable'], TRUE) ? json_decode($env['globalVariable'], TRUE) : array();
        		$env['additionalParamList'] = json_decode($env['additionalVariable'], TRUE) ? json_decode($env['additionalVariable'], TRUE) : array();
        		unset($env['envHeader']);
        		unset($env['globalVariable']);
        		unset($env['additionalVariable']);
        	}
        	return $result;
        }
        else
        {
        	return array();
        }
    }

    /**
     * Add Env
     * @param $project_id int Project NumberID
     * @param $env_name string Env name
     * @param $front_uri string preURI
     * @param $headers array request header
     * @param $params array 
     * @param $apply_protocol int 
     * @param $additional_params array 
     * @return bool|int
     */
    public function addEnv(&$projectID, &$env_name, &$env_desc,&$front_uri, &$env_header, &$env_auth, &$global_variable,  &$additional_variable)
    
    {
        $env_dao = new EnvDao;
        $projectDao = new ProjectDao;
        if (!$projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return FALSE;
        }
        $env_id = $env_dao->addEnv($projectID, $env_name, $env_desc,$front_uri, $env_header, $env_auth, $global_variable, $additional_variable);
        if ($env_id) {
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_ENVIRONMENT, $env_id, ProjectLogDao::$OP_TYPE_ADD, "Add Environment:'{$env_name}'", date("Y-m-d H:i:s", time()));
            return $env_id;
        } else {
            return FALSE;
        }
    }

    /**
     * Delete Env
     * @param $project_id int Project NumberID
     * @param $env_id int Env numberID
     * @return bool
     */
    public function deleteEnv(&$project_id, &$env_id)
    {
        $env_dao = new EnvDao;
        $projectDao = new ProjectDao;
        if ($projectDao->checkProjectPermission($project_id, $_SESSION['userID'])) {
            if (!$env_dao->checkEnvPermission($env_id, $_SESSION['userID'])) {
                return FALSE;
            }
            $env_name = $env_dao->getEnvName($env_id);
            if ($env_dao->deleteEnv($project_id, $env_id)) {
                $log_dao = new \ProjectLogDao();
                $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_ENVIRONMENT, $env_id, ProjectLogDao::$OP_TYPE_DELETE, "Delete Environment:'$env_name'", date("Y-m-d H:i:s", time()));

                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }
    
    /**
    * 批量删除环境
    * @param $project_id 项目的数字ID
    * @param $user_id 用户的数字ID
    * @param $env_id 环境的数字ID
    */
    public function batchDeleteEnv(&$project_id, &$env_ids)
    {
    	$env_dao = new EnvDao;
    	$projectDao = new ProjectDao;
    	if ($projectDao->checkProjectPermission($project_id, $_SESSION['userID'])) {
    		if (!$env_dao->checkEnvPermission($env_ids, $_SESSION['userID'])) {
    			return FALSE;
    		}
    	$env_name = $env_dao->getEnvName($env_ids);
    	if ($env_dao -> batchDeleteEnv($project_id, $env_ids))
    	{
    		        
    				$log_dao = new \ProjectLogDao();
    				$log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_ENVIRONMENT, $env_id, ProjectLogDao::$OP_TYPE_DELETE, "Delete Environment:'$env_name'", date("Y-m-d H:i:s", time()));  
      			return TRUE;
    	}   
    	else {
    		return FALSE;
    	 }
    	}else
    	{
    		return FALSE;
    	}
    }
    /**
     * 获取环境信息
     * @param int $project_id 项目ID
     * @param int $env_id 环境ID
     */
    public function getEnvInfo(&$project_id, &$env_id)
    {
    	$env_dao = new EnvDao;
    	return $env_dao -> getEnvFromDB($env_id);
    }
    /**
     * Edit Env
     * @param $env_id int Project NumberID
     * @param $env_name string Env name
     * @param $front_uri string preURI
     * @param $headers array Request Header
     * @param $params array 
     * @param $apply_protocol int 
     * @param $additional_params array
     * @return bool
     */
    public function editEnv(&$env_id, &$env_name, &$env_desc,&$front_uri, &$env_header, &$env_auth,&$global_variable, &$additional_params)
    {
        $env_dao = new EnvDao;
        if (!($project_id = $env_dao->checkEnvPermission($env_id, $_SESSION['userID']))) {
            return FALSE;
        }
        if ($env_dao->editEnv($env_id, $env_name, $env_desc,$front_uri, $env_header, $env_auth,$global_variable, $additional_params)) {
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_ENVIRONMENT, $project_id, ProjectLogDao::$OP_TYPE_UPDATE, "Edit Environment:'{$env_name}'", date("Y-m-d H:i:s", time()));

            return TRUE;
        } else {
            return FALSE;
        }
    }
}
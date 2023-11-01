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

class ProjectController
{
    // Return json Type
    private $returnJson = array('type' => 'project');

    /**
     * Check Login
     */
    public function __construct()
    {
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * Create Project
     */
    public function addProject()
    {
        $nameLen = mb_strlen(quickInput('projectName'), 'utf8');
        $projectName = securelyInput('projectName');
        $projectType = securelyInput('projectType');
        $projectVersion = quickInput('projectVersion');
        $version_len = mb_strlen(quickInput('projectVersion'));
        if (!($nameLen >= 1 && $nameLen <= 32 && preg_match('/^[0-3]{1}$/', $projectType))) {
           
            $this->returnJson['statusCode'] = '140002';
        } elseif ($version_len < 1 || $version_len > 10) {
            
            $this->returnJson['statusCode'] = '140017';
        } else {
            
            $service = new ProjectModule();
            $result = $service->addProject($projectName, $projectType, $projectVersion);
            
            if ($result) {
                
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['projectInfo'] = $result;
            } else
                
                $this->returnJson['statusCode'] = '140001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Delete Project
     */
    public function deleteProject()
    {
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType != 0) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }

        
        if (preg_match('/^[0-9]{1,11}$/', $projectID)) {
            
            $service = new ProjectModule();
            $result = $service->deleteProject($projectID);
            
            if ($result)
                
                $this->returnJson['statusCode'] = '000000';
            else
                
                $this->returnJson['statusCode'] = '140003';
        } else {
            
            $this->returnJson['statusCode'] = '140004';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get Project List
     */
    public function getProjectList()
    {
        $nameLen = mb_strlen(quickInput('projectName'), 'utf8');
        $projectType = securelyInput('projectType');
        //$projectName = securelyInput('projectName');
        if (!preg_match('/^[0-3]|[-1]{1}$/', $projectType) || ($nameLen != 0 && $nameLen < 1 || $nameLen > 30)) {
            $this->returnJson['statusCode'] = '140002';
            exitOutput($this->returnJson);
        } else {
            $service = new ProjectModule();
            $result = $service->getProjectList($projectType);

            if ($result) {

                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['projectList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '140005';
            }
        }

        exitOutput($this->returnJson);
    }

    /**
     * Edit Project
     */
    public function editProject()
    {
        $nameLen = mb_strlen(quickInput('projectName'), 'utf8');
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $projectType = securelyInput('projectType');
        $projectName = securelyInput('projectName');
        $projectVersion = quickInput('projectVersion');
        $version_len = mb_strlen(quickInput('projectVersion'));

       
        if (!(preg_match('/^[0-9]{1,11}$/', $projectID) && $nameLen >= 1 && $nameLen <= 32 && preg_match('/^[0-3]{1}$/', $projectType))) {
            
            $this->returnJson['statusCode'] = '140007';
        } elseif ($version_len < 1 || $version_len > 10) {
           
            $this->returnJson['statusCode'] = '140017';
        } else {
            
            $service = new ProjectModule();
            $result = $service->editProject($projectID, $projectName, $projectType, $projectVersion);

            
            if ($result)
                
                $this->returnJson['statusCode'] = '000000';
            else
                
                $this->returnJson['statusCode'] = '140006';
        }

        exitOutput($this->returnJson);
    }

    /**
     * Get Project
     */
    public function getProject()
    {
        $projectID = securelyInput('projectID');
        if (preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $service = new ProjectModule;
            $result = $service->getProject($projectID);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson = array_merge($this->returnJson, $result);
            } else {
                $this->returnJson['statusCode'] = '140005';
            }
        } else {
            $this->returnJson['statusCode'] = '140007';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Export Project
     */
    public function dumpProject()
    {
        $projectID = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            
            $this->returnJson['statusCode'] = '140004';
            exitOutput($this->returnJson);
        }
        $service = new ProjectModule;
        $fileName = $service->dumpProject($projectID);
        if ($fileName) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['fileName'] = $fileName;
        } else {
            
            $this->returnJson['statusCode'] = '140021';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get Api Num
     */
    public function getApiNum()
    {
        $projectID = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            
            $this->returnJson['statusCode'] = '140004';
            exitOutput($this->returnJson);
        }
        $service = new ProjectModule;
        $result = $service->getApiNum($projectID);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['num'] = $result['num'];
        } else {
            $this->returnJson['statusCode'] = '140023';
        }
        exitOutput($this->returnJson);
    }
    /**
     * getProjectMemberList
     */
    public function getProjectMemberList()
    {
    	$projectID = securelyInput('projectID');
    	// 判断项目ID是否合法
    	if (!preg_match('/^[0-9a-zA-Z]{32,}$/', $projectID))
    	{
    		// 项目ID不合法
    		$this -> return_json['statusCode'] = '140004';
    	}
    	else
    	{
    		$user_id = get_user_info('userID');
    		$service = new ProjectModule;
    		$result = $service -> getProjectMemberList($projectID);
    		if($result)
    		{
    			//成功
    			$this -> return_json['statusCode'] = '000000';
    			$this -> return_json['memberList'] = $result;
    		}
    		else
    		{
    			//失败
    			$this -> return_json['statusCode'] = '140000';
    		}
    	}
    	exitOutput($this->returnJson);
    }
    /**
     * 获取用户项目权限
     */
    public function getUserProjectPermission()
    {
    	  $project_hash_key = securelyInput('projectID');
    		$service = new ProjectModule;
    		$result = $service->getUserProjectPermission($project_hash_key);
    		if($result)
    		{
    			$this->returnJson['statusCode'] = '000000';
    			$this->returnJson['permission'] = $result;
    		}
    		else
    		{
    			// 失败
    			$this->returnJson['statusCode'] = '510000';
    		}
    	exitOutput($this->returnJson);
    }
    /**
     * Get Project Log List
     */
    public function getProjectLogList()
    {
       
        $project_id = securelyInput('projectID');
        
        $page = securelyInput('page', 1);
        
        $page_size = securelyInput('pageSize', 15);

        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            
            $this->returnJson['statusCode'] = '140004';
        } else {
            $service = new ProjectModule();
            $result = $service->getProjectLogList($project_id, $page, $page_size);

            if ($result) {
                
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson = array_merge($this->returnJson, $result);
            } else {
                
                $this->returnJson['statusCode'] = '140000';
            }
        }
        exitOutput($this->returnJson);
    }
}

?>
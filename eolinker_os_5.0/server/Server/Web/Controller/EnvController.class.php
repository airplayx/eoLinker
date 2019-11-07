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

class EnvController
{
    private $returnJson = array('type' => 'environment');

    /**
     * Check Login
     */
    public function __construct()
    {
        $module = new GuestModule;
        if (!$module->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * Get Env List
     */
    public function getEnvList()
    {
        $service = new EnvModule;
        $projectID = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '170003';
        } else {
            $result = $service->getEnvList($projectID); 
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['envList'] = $result;
        }
        exitOutput($this->returnJson);
    }

    /**
     * Add Env
     */
    public function addEnv()
    {
    	//环境名称
    	$env_name = securelyInput('envName');
    	//环境名称长度
    	$name_length = mb_strlen(quickInput('envName'), 'utf8');
    	//前置URI地址
    	$front_uri = securelyInput('frontURI');
    	//请求头部
    	$env_header = quickInput('headers');
    	//全局变量
    	$global_variable = quickInput('params');
    	//环境鉴权
    	$env_auth = quickInput('envAuth');
    	//额外参数
    	$additional_variable = quickInput('additionalParams');
    	//环境说明
    	$env_desc = quickInput('envDesc');
    	//判断名称长度是否合法
    	$projectID = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '170003';
        }
        elseif ($name_length < 1 || $name_length > 32) {
            $this->returnJson['statusCode'] = '170001';
        } else {
            $service = new EnvModule;
            $result = $service->addEnv($projectID, $env_name, $env_desc,$front_uri, $env_header, $env_auth,$global_variable, $additional_variable);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['envID'] = $result;
            } else {
                $this->returnJson['statusCode'] = '170000';
            }
        }
        exitOutput($this->returnJson);
    }
    /**
     *Batch delete Env
     */
    public function batchDeleteEnv()
    {
    	$env_id = securelyInput('envID');
    	$env_id = json_decode($env_id, TRUE);
    	$project_id = securelyInput('projectID');
    	// 判断环境ID是否合法
    	if(empty($env_id))
    	{
    		// 环境ID不合法
    		$this->return_json['statusCode'] = '170002';
    	}
    	elseif (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
    		$this->returnJson['statusCode'] = '170003';
    	}else{
    		    $service = new EnvModule();
    			$env_ids = implode(",", $env_id);
    			if ($service->batchDeleteEnv($project_id, $env_ids)) {
    				$this->returnJson['statusCode'] = '000000';
    			} else {
    				$this->returnJson['statusCode'] = '170000';
    			} 
    	}
    	exitOutput($this->returnJson);
    }
    /**
     * Delete Env
     */
    public function deleteEnv()
    {
        $env_id = securelyInput('envID');
        $project_id = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '170003';
        } 
        elseif (!preg_match('/^[0-9]{1,11}$/', $env_id)) {
            $this->returnJson['statusCode'] = '170002';
        } else {
            $service = new EnvModule();
            if ($service->deleteEnv($project_id, $env_id)) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '170000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Edit Env
     */
    public function editEnv()
    {
        $env_id = securelyInput('envID');
        //环境名称
        $env_name = securelyInput('envName');
        //环境名称长度
        $name_length = mb_strlen(quickInput('envName'), 'utf8');
        //前置URI地址
        $front_uri = securelyInput('frontURI');
        //请求头部
        $env_header = quickInput('headers');
        //全局变量
        $global_variable = quickInput('params');
        //环境鉴权
        $env_auth = quickInput('envAuth');
        //额外参数
        $additional_variable = quickInput('additionalParams');
        //环境说明
        $env_desc = quickInput('envDesc');
        if ($name_length < 1 || $name_length > 32) {
            $this->returnJson['statusCode'] = '170001';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $env_id)) {
            $this->returnJson['statusCode'] = '170002';
        } else {
            $service = new EnvModule();
            if ($service->editEnv($env_id, $env_name,$env_desc, $front_uri, $env_header, $env_auth,$global_variable, $additional_variable)) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '170000';
            }
        }
        exitOutput($this->returnJson);
    }
    /**
     * 获取项目环境信息
     */
    public function getEnvInfo()
    {
    	$project_id = securelyInput('projectID');
    	$env_id = securelyInput('envID');
    	if(! preg_match('/^[0-9]{1,11}$/', $env_id))
    	{
    		// 环境ID不合法
    		$this->return_json['statusCode'] = '170002';
    	}
    	else
    	{
    		  $service = new EnvModule();
    			$result = $service -> getEnvInfo($project_id, $env_id);
    			// 验证结果
    			if($result)
    			{
    				$this->returnJson['statusCode'] = '000000';
    				$this->returnJson['envInfo'] = $result;
    			}
    			else
    			{
    				// 环境列表为空
    				$this->returnJson['statusCode'] = '170000';
    			}    		
    	}
    	exitOutput($this->returnJson);
    }
}
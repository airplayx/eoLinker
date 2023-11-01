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

class StatusCodeGroupController
{

    // Return json Type
    private $returnJson = array('type' => 'status_code_group');

    /**
     * Check Login
     */
    public function __construct()
    {
        // 身份验证
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * Add group
     */
    public function addGroup()
    {
        $nameLen = mb_strlen(quickInput('groupName'), 'utf8');
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $groupName = securelyInput('groupName');
        $parentGroupID = securelyInput('parentGroupID', NULL);
        $isChild = securelyInput('isChild', 0);

        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
           
            $this->returnJson['statusCode'] = '180005';
        } elseif (!($nameLen >= 1 && $nameLen <= 32)) {
            
            $this->returnJson['statusCode'] = '180004';
        } else {
            $service = new StatusCodeGroupModule;
            $result = $service->addGroup($projectID, $groupName, $parentGroupID, $isChild);

            if ($result) {
                
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['statusGroupID'] = $result;
            } else {
               
                $this->returnJson['statusCode'] = '180002';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Delete Group
     */
    public function deleteGroup()
    {
        $groupID = securelyInput('groupID');
        $module = new StatusCodeGroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }

        if (!preg_match('/^[0-9]{1,11}$/', $groupID)) {
            
            $this->returnJson['statusCode'] = '180003';
        } else {
            $service = new StatusCodeGroupModule;
            $result = $service->deleteGroup($groupID);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '180006';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get Group List
     */
    public function getGroupList()
    {
        $projectID = securelyInput('projectID');

        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            
            $this->returnJson['statusCode'] = '180005';
        } else {
            $service = new StatusCodeGroupModule;
            $result = $service->getGroupList($projectID);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson = array_merge($this->returnJson, $result);
            } else {
                $this->returnJson['statusCode'] = '180001';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Edit Group
     */
    public function editGroup()
    {
        $nameLen = mb_strlen(quickInput('groupName'), 'utf8');
        $groupID = securelyInput('groupID');
        $parentGroupID = securelyInput('parentGroupID');
        $isChild = securelyInput('isChild');
        $module = new StatusCodeGroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $groupName = securelyInput('groupName');

        if (!preg_match('/^[0-9]{1,11}$/', $groupID) || ($parentGroupID != NULL && !preg_match('/^[0-9]{1,11}$/', $parentGroupID))) {
            
            $this->returnJson['statusCode'] = '180003';
        } elseif (!($nameLen >= 1 && $nameLen <= 32)) {
            
            $this->returnJson['statusCode'] = '180004';
        } elseif ($groupID == $parentGroupID) {
            $this->returnJson['statusCode'] = '180009';
        } else {
            $service = new StatusCodeGroupModule;
            $result = $service->editGroup($groupID, $groupName, $parentGroupID, $isChild);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '180007';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Sort Group
     */
    public function sortGroup()
    {
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        
        $orderList = quickInput('orderNumber');
        
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '180005';
        } else if (empty($orderList)) {
            
            $this->returnJson['statusCode'] = '180008';
        } else {
            $service = new StatusCodeGroupModule();
            $result = $service->sortGroup($projectID, $orderList);
            
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '180000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Export Group
     */
    public function exportGroup()
    {
        
        $group_id = securelyInput('groupID');
        if (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            
            $this->returnJson['statusCode'] = '180003';
        } else {
            $service = new StatusCodeGroupModule();
            $user_type = $service->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $result = $service->exportGroup($group_id);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['fileName'] = $result;
                } else {
                    $this->returnJson['statusCode'] = '180000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Import Group
     */
    public function importGroup()
    {
        $project_id = securelyInput('projectID');
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '180007';
        } 
        elseif (empty($data)) {
            $this->returnJson['statusCode'] = '180005';
            exitOutput($this->returnJson);
        } else {
            $service = new ProjectModule();
            $user_type = $service->getUserType($project_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            }
            $server = new StatusCodeGroupModule();
            $result = $server->importGroup($project_id, $data);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '180000';
            }
        }
        exitOutput($this->returnJson);
    }
}

?>
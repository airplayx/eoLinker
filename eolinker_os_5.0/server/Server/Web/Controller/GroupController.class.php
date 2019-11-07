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
class GroupController
{
    // Return Json
    private $returnJson = array('type' => 'group');

    /**
     * Check Login in
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
     * Add group
     */
    public function addGroup()
    {
        $nameLen = mb_strlen(quickInput('groupName'), 'utf8');
        $projectID = securelyInput('projectID');
        $isChild = securelyInput('isChild', 0);
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $groupName = securelyInput('groupName');
        $parentGroupID = securelyInput('parentGroupID', NULL);
        if (preg_match('/^[0-9]{1,11}$/', $projectID) && $nameLen >= 1 && $nameLen <= 30) {
            $service = new GroupModule();
            $result = $service->addGroup($projectID, $groupName, $parentGroupID, $isChild);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['groupID'] = $result;
            } else
                $this->returnJson['statusCode'] = '150001';
        } else {
            $this->returnJson['statusCode'] = '150002';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Delete Group
     */
    public function deleteGroup()
    {
        $groupID = securelyInput('groupID');
        $module = new GroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        if (preg_match('/^[0-9]{1,11}$/', $groupID)) {
            $service = new GroupModule();
            $result = $service->deleteGroup($groupID);
            if ($result)
                $this->returnJson['statusCode'] = '000000';
            else
                $this->returnJson['statusCode'] = '150003';
        } else {
            $this->returnJson['statusCode'] = '150004';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get Group List
     */
    public function getGroupList()
    {
        $projectID = securelyInput('projectID');
        if (preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $service = new GroupModule;
            $result = $service->getGroupList($projectID);
            $orderList = $service->getGroupOrderList($projectID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['groupList'] = $result;
                $this->returnJson['groupOrder'] = $orderList;
            } else {
                $this->returnJson['statusCode'] = '150008';
            }
        } else {
            $this->returnJson['statusCode'] = '150007';
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
        $module = new GroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $groupName = securelyInput('groupName');
        if (preg_match('/^[0-9]{1,11}$/', $groupID) && $nameLen >= 1 && $nameLen <= 30) {
            if ($groupID == $parentGroupID) {
                $this->returnJson['statusCode'] = '150008';
                exitOutput($this->returnJson);
            }
            $service = new GroupModule();
            $result = $service->editGroup($groupID, $groupName, $parentGroupID, $isChild);
            if ($result)
                $this->returnJson['statusCode'] = '000000';
            else
                $this->returnJson['statusCode'] = '150005';
        } else {
            $this->returnJson['statusCode'] = '150002';
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
        $orderNumber = quickInput('orderNumber');
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '150007';
        } else if (empty($orderNumber)) {
            $this->returnJson['statusCode'] = '150004';
        } else {
            $service = new GroupModule;
            $result = $service->sortGroup($projectID, $orderNumber);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '150000';
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
            $this->returnJson['statusCode'] = '150003';
        } else {
            $service = new GroupModule();
            $user_type = $service->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $result = $service->exportGroup($group_id);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['fileName'] = $result;
                } else {
                    $this->returnJson['statusCode'] = '150000';
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
            $this->returnJson['statusCode'] = '150007';
        } 
        elseif (empty($data)) {
            $this->returnJson['statusCode'] = '150005';
            exitOutput($this->returnJson);
        } else {
            $service = new ProjectModule();
            $user_type = $service->getUserType($project_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            }
            $server = new GroupModule();
            $result = $server->importGroup($project_id, $data);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '150000';
            }
        }
        exitOutput($this->returnJson);
    }
}

?>
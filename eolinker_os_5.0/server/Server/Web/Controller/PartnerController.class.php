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

class PartnerController
{
    // Return json Type
    private $returnJson = array('type' => 'partner');

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
     * 获取分配项目人员
     */
    public function getMemberList()
    {
    	$projectID = securelyInput('projectID');
    	$userServer = new PartnerModule;
    	$result = $userServer->getMemberList($projectID);
    	$this->returnJson ['statusCode'] = '000000';
    	$this->returnJson ['memberList'] = $result;
    	exitOutput($this->returnJson);
    }
    /**
     * 获取未分配项目人员
     */
    public function getNotMemberList()
    {
    	$projectID = securelyInput('projectID');
    	$userServer = new PartnerModule;
    	$result = $userServer->getNotMemberList($projectID);
    	$this->returnJson ['statusCode'] = '000000';
    	$this->returnJson ['memberList'] = $result;
    	exitOutput($this->returnJson);
    }
    /**
     * Get Partner Info
     */
    public function getPartnerInfo()
    {
        $userName = securelyInput('userName');
        $projectID = securelyInput('projectID');

        if (!preg_match('/^([a-zA-Z][0-9a-zA-Z_]{3,59})$/', $userName)) {
            $this->returnJson['statusCode'] = '250001';
        } else {
            $userServer = new UserModule;
            $userInfo = $userServer->checkUserExist($userName);
            if ($userInfo) {
                $partnerServer = new PartnerModule;
                if ($partnerServer->checkIsInvited($projectID, $userName)) {
                    $this->returnJson['statusCode'] = '250007';
                    $this->returnJson['userInfo']['userName'] = $userName;
                    $this->returnJson['userInfo']['userNickName'] = $userInfo['userNickName'];
                    $this->returnJson['userInfo']['isInvited'] = 1;
                } else {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['userInfo']['userName'] = $userName;
                    $this->returnJson['userInfo']['userNickName'] = $userInfo['userNickName'];
                    $this->returnJson['userInfo']['isInvited'] = 0;
                }
            } else {
                $this->returnJson['statusCode'] = '250002';
            }

        }
        exitOutput($this->returnJson);
    }
    /**
     * 添加成员
     */
    public function addMember()
    {
    	$projectID = securelyInput('projectID');
    	$ids = securelyInput ( 'userID' );
    	$arr = json_decode ( $ids );
    	$arr = preg_grep ( '/^[0-9]{1,11}$/', $arr ); // 去掉数组中不是数字的ID、
    	if(empty ( $arr ))
    	{
    		// 关联ID格式非法
    		$this->return_json ['statusCode'] = '1000002';
    	}
    	else
    	{
    		
    		$partnerServer = new PartnerModule;
    		$result = $partnerServer->addMember ( $projectID, $arr );
    		if($result)
    		{
    			// 成功
    			$this->returnJson ['statusCode'] = '000000';
    		}
    		else
    		{
    			$this->returnJson ['statusCode'] = '1000000';
    		}
    	}
    	exitOutput ( $this->returnJson );
    }
    /**
     * Invite Partner
     */
    public function invitePartner()
    {
        $userName = securelyInput('userName');
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }

        if (!preg_match('/^([a-zA-Z][0-9a-zA-Z_]{3,59})$/', $userName)) {
            $this->returnJson['statusCode'] = '250001';
        } else {
            $userServer = new UserModule;
            $userInfo = $userServer->checkUserExist($userName);
            if ($userInfo) {
                $partnerServer = new PartnerModule;
                if ($partnerServer->checkIsInvited($projectID, $userName)) {
                    $this->returnJson['statusCode'] = '250007';
                } else {
                    if ($connID = $partnerServer->invitePartner($projectID, $userInfo['userID'])) {
                        $this->returnJson['statusCode'] = '000000';
                        $this->returnJson['connID'] = $connID;
                    } else {
                        $this->returnJson['statusCode'] = '250003';
                    }
                }
            } else {
                $this->returnJson['statusCode'] = '250002';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Remove Partner
     */
    public function removePartner()
    {
        $projectID = securelyInput('projectID');
        $connID = securelyInput('connID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        

        $server = new PartnerModule;
        if ($server->removePartner($projectID, $connID)) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '250004';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get Partner List
     */
    public function getPartnerList()
    {
        $projectID = securelyInput('projectID');

        $server = new PartnerModule;
        $result = $server->getPartnerList($projectID);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['partnerList'] = $result;
        } else {
            $this->returnJson['statusCode'] = '250005';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Quit Partner
     */
    public function quitPartner()
    {
        $projectID = securelyInput('projectID');

        $server = new PartnerModule;
        $result = $server->quitPartner($projectID);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '250006';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Edit Partner Nick Name
     */
    public function editPartnerNickName()
    {
        $projectID = securelyInput('projectID');
        $conn_id = securelyInput('connID');
        $nick_name = securelyInput('nickName');
        $name_length = mb_strlen(quickInput('nickName'), 'utf8');
        if (!preg_match('/^[0-9]{1,11}$/', $conn_id)) {
            $this->returnJson['statusCode'] = '250003';
        } elseif ($name_length < 1 || $name_length > 32) {
            $this->returnJson['statusCode'] = '250004';
        } else {
            $module = new PartnerModule();
            $result = $module->editPartnerNickName($projectID, $conn_id, $nick_name);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '250000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Edit Partner Type
     */
    public function editPartnerType()
    {
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $conn_id = securelyInput('connID');
        $user_type = securelyInput('userType');

        if (!preg_match('/^[0-9]{1,11}$/', $conn_id)) {
            $this->returnJson['statusCode'] = '250003';
        } elseif (!preg_match('/^[1-3]{1}$/', $user_type)) {
            $this->returnJson['statusCode'] = '250005';
        } else {
            $module = new PartnerModule();
            $result = $module->editPartnerType($projectID, $conn_id, $user_type);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '250000';
            }
        }
        exitOutput($this->returnJson);
    }

    public function getProjectInviteCode()
    {
        $projectID = securelyInput('projectID');
    }

    public function joinProjectByInviteCode()
    {
        $projectID = securelyInput('projectID');
    }
}

?>
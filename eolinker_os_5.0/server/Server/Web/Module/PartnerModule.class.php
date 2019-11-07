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

class PartnerModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * Get USER TYPE
     * @param $projectID int ProjectID
     * @return bool|int
     */
    public function getUserType(&$projectID)
    {
        $dao = new AuthorizationDao();
        $result = $dao->getProjectUserType($_SESSION['userID'], $projectID);
        if ($result === FALSE) {
            return -1;
        }
        return $result;
    }
    /**
     * 获取项目成员列表
     *
     *
     */
    public function getMemberList(&$projectID)
    {
    	$partnerDao = new PartnerDao;
    	return $partnerDao->getMemberListFromDB($projectID );
    }
    /**
     * 获取未分配项目成员列表
     *
     *
     */
    public function getNotMemberList(&$projectID)
    {
    	$partnerDao = new PartnerDao;
    	return $partnerDao->getNotMemberListFromDB($projectID );
    }
    /**
     * Invite developer
     * @param $projectID int ProjectID
     * @param $inviteUserID int InviterID
     * @return bool|int
     */
    public function invitePartner(&$projectID, &$inviteUserID)
    {
        $projectDao = new ProjectDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $projectInfo = $projectDao->getProjectName($projectID);
            $summary = 'You have been Invited to：' . $projectInfo['projectName'] . '，Enjoy your teamwork!';
            $msg = '<p>Hello!Dear user：</p><p>You have been invited to：<b style="color:#4caf50">' . $projectInfo['projectName'] . '</b>，Now you could take part in developing work.</p><p>If you have any question please go to our slack and share with us<a href="http://eolinker.slack.com"><b style="color:#4caf50"></b></a>Thank You.</p>';

            
            $partnerDao = new PartnerDao;
            if ($connID = $partnerDao->invitePartner($projectID, $inviteUserID, $_SESSION['userID'])) {
                $inviteUserCall = $partnerDao->getPartnerUserCall($inviteUserID);
             
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_PARTNER, $inviteUserID, ProjectLogDao::$OP_TYPE_ADD, "Invite New Member:'$inviteUserCall'", date("Y-m-d H:i:s", time()));

           
                $msgDao = new MessageDao;
                $msgDao->sendMessage($_SESSION['userID'], $inviteUserID, 1, $summary, $msg);
                return $connID;
            } else
                return FALSE;
        } else
            return FALSE;
    }

    /**
     * Delete developer
     * @param $projectID int project ID
     * @param $connID int connID
     * @return bool
     */
    public function removePartner($projectID, $connID)
    {
        $projectDao = new ProjectDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $projectInfo = $projectDao->getProjectName($projectID);
            $summary = 'You have been removed from：' . $projectInfo['projectName'];
            $msg = '<p>Hello!Dear user：</p><p>You have been removed from project：<b style="color:#4caf50">' . $projectInfo['projectName'] . '</b>.</p><p>If you have any question please go to our slack and share with us<a href="http://eolinker.slack.com"><b style="color:#4caf50"></b></a>Thank You.</p>';

            $partnerDao = new PartnerDao;
            if ($partnerDao->removePartner($projectID, $connID)) {
                $inviteUserCall = $partnerDao->getPartnerUserCall($remotePartnerID);
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_PARTNER, $remotePartnerID, ProjectLogDao::$OP_TYPE_DELETE, "Remove Member:'$inviteUserCall'", date("Y-m-d H:i:s", time()));
                $msgDao = new MessageDao;
                $msgDao->sendMessage(0, $remotePartnerID, 1, $summary, $msg);
                return TRUE;
            } else
                return FALSE;
        } else
            return FALSE;

    }
    
    
    public function addMember(&$projectID, &$conn_ids)
    {
        $projectDao = new PartnerDao;
    	$user_ids = array();	
    	foreach ($conn_ids as $user)
    	{	
    		if(! $projectDao->checkIsProjectMember ( $projectID, $user ))
    			$user_ids [] = $user;
    	}
    	if($user_ids)
    	{
    		$result = $projectDao->addMember ($projectID, $user_ids);
    		
    		if($result)
    		{
    			$log_dao = new ProjectLogDao();
    			$log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_PARTNER, $remotePartnerID, ProjectLogDao::$OP_TYPE_DELETE, "Add Member:'$user'", date("Y-m-d H:i:s", time()));
    			$msgDao = new MessageDao;
    			$msgDao->sendMessage(0, $remotePartnerID, 1, $summary, $msg);
    			return TRUE;
    		}
    		else
    		{
    			return FALSE;
    		}
    	}
    	else
    		return FALSE;
    }
    /**
     * Get Developer List
     * @param $projectID int ProjectID
     * @return bool|array
     */
    public function getPartnerList(&$projectID)
    {
        $projectDao = new ProjectDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $partnerDao = new PartnerDao;
            $list = $partnerDao->getPartnerList($projectID);
            foreach ($list as &$param) {
                if ($param['userID'] == $_SESSION['userID'])
                    $param['isNow'] = 1;
                else
                    $param['isNow'] = 0;
            }
            return $list;
        } else
            return FALSE;
    }

    /**
     * Quit project
     * @param $projectID int ProjectID
     * @return bool
     */
    public function quitPartner(&$projectID)
    {
        $projectDao = new ProjectDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $partnerDao = new PartnerDao;
            if ($partnerDao->quitPartner($projectID, $_SESSION['userID'])) {
                $user_call = $partnerDao->getPartnerUserCall($_SESSION['userID']);
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_PARTNER, $_SESSION['userID'], ProjectLogDao::$OP_TYPE_OTHERS, "'$user_call'Quit Project", date("Y-m-d H:i:s", time()));

                return TRUE;
            } else
                return FALSE;
        } else
            return FALSE;
    }

    /**
     * Check is invited
     * @param $projectID int ProjectID
     * @param $userName string Username
     * @return bool
     */
    public function checkIsInvited(&$projectID, &$userName)
    {
        $dao = new PartnerDao;
        return $dao->checkIsInvited($projectID, $userName);
    }

    /**
     * Edit developer nickname
     * @param $project_id int ProjectID
     * @param $conn_id int connID
     * @param $nick_name string nickname
     * @return bool
     */
    public function editPartnerNickName(&$project_id, &$conn_id, &$nick_name)
    {
        $dao = new PartnerDao();
        return $dao->editPartnerNickName($project_id, $conn_id, $nick_name);
    }

    /**
     * Edit developer Type
     * @param $project_id int ProjectID
     * @param $conn_id int connID
     * @param $user_type int UserType
     * @return bool
     */
    public function editPartnerType(&$project_id, &$conn_id, &$user_type)
    {
        $dao = new PartnerDao();
        $result = $dao->editPartnerType($project_id, $conn_id, $user_type);
        if ($result) {
            $remote_partner_id = $dao->getUserID($conn_id);
            $invite_user_call = $dao->getPartnerUserCall($remote_partner_id);
            switch ($user_type) {
                case 1:
                    $type = 'Admin';
                    break;
                case 2:
                    $type = 'Member ( Read&Write )';
                    break;
                case 3:
                    $type = 'Member ( Read )';
                    break;
                default:
                    break;
            }

            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_PARTNER, $remote_partner_id, ProjectLogDao::$OP_TYPE_DELETE, "Edit Member:'$invite_user_call'To'$type'", date("Y-m-d H:i:s", time()));
            return $result;
        } else {
            return FALSE;
        }
    }

    public function getProjectInviteCode(&$project_id)
    {

    }

    public function joinProjectByInviteCode()
    {

    }
}

?>
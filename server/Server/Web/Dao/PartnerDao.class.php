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

class PartnerDao
{
    /**
     * Invite developer
     * @param $projectID int projectID
     * @param $inviteUserID int InvitationID
     * @return bool|int
     */
    public function invitePartner(&$projectID, &$userID, &$inviteUserID)
    {
        $db = getDatabase();
        $db->prepareExecute('INSERT INTO eo_ams_conn_project (eo_ams_conn_project.projectID,eo_ams_conn_project.userID,eo_ams_conn_project.userType,eo_ams_conn_project.inviteUserID) VALUES (?,?,2,?);', array(
            $projectID,
            $userID,
            $inviteUserID
        ));

        if ($db->getAffectRow() > 0)
            return $db->getLastInsertID();
        else
            return FALSE;
    }
    /**
     * 检查是否是项目成员
     */
    public function checkIsProjectMember(&$project_id, &$user_id)
    {
    	$db = getDatabase();
    	$result = $db->prepareExecute("SELECT eo_ams_conn_project.userID FROM eo_ams_conn_project WHERE eo_ams_conn_project.projectID = ? AND eo_ams_conn_project.userID = ?;", array(
    			$project_id,
    			$user_id
    	));
    	if ($result)
    		return TRUE;
    		else
    			return FALSE;
    }
    /**
     * 添加项目成员
     * @param int $space_id 空间ID
     * @param int $project_id 项目ID
     * @param int $group_id 分组ID
     * @param array $user_ids 用户ID
     * @param string $group_type 权限
     * @throws \PDOException
     */
    public function addMember(&$project_id,&$user_ids)
    {
    	$db = getDatabase();
    		foreach ($user_ids as $user_id) {
    			// 插入项目分组用户表
    			$db->prepareExecute("INSERT INTO eo_ams_conn_project(eo_ams_conn_project.userID,eo_ams_conn_project.projectID,eo_ams_conn_project.userType) VALUES(?,?,?);", array(
    					$user_id,
    					$project_id,
    					2
    			));
    			if ($db->getAffectRow() < 1)
    				throw new \PDOException("addMember error");
    		}  		
    		return TRUE;
    	}
    /**
     * 获取项目成员
     *
     * @param int $space_id
     *            公司ID
     */
    public function getMemberListFromDB(&$projectID)
    {
    	$db = getDatabase();
    	$result = $db->prepareExecuteAll('SELECT eo_user.userName,eo_user.userNickName,eo_ams_conn_project.connID,eo_ams_conn_project.userType,eo_ams_conn_project.partnerNickName FROM eo_ams_conn_project  LEFT JOIN eo_user ON eo_ams_conn_project.userID = eo_user.userID
				WHERE EXISTS(SELECT eo_ams_conn_project.userID,eo_user.userName FROM eo_ams_conn_project WHERE eo_ams_conn_project.userID = eo_user.userID) AND eo_ams_conn_project.projectID = ?;', array(
						$projectID
				));
    	if($result)
    		return $result;
    		else
    			return array();
    }
    /**
     * 获取未加入项目成员
     *
     * @param int $space_id
     *            公司ID
     */
    public function getNotMemberListFromDB(&$projectID)
    {
    	$db = getDatabase();
    	$result = $db->prepareExecuteAll('SELECT eo_user.userName,eo_user.userID,eo_user.userNickName FROM eo_user
				WHERE NOT	EXISTS(SELECT eo_user.userID FROM eo_ams_conn_project WHERE eo_ams_conn_project.projectID = ? AND eo_user.userID = eo_ams_conn_project.userID);', array(
						$projectID
				));
    	if($result)
    		return $result;
    		else
    			return array();
    }
    /**
     * rmove developer
     * @param $projectID int projectID
     * @param $connID int connID
     * @return bool
     */
    public function removePartner(&$projectID, &$connID)
    {
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_ams_conn_project WHERE eo_ams_conn_project.projectID = ? AND eo_ams_conn_project.connID = ? AND eo_ams_conn_project.userType != 0;', array(
            $projectID,
            $connID
        ));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * get developer list
     * @param $projectID int projectID
     * @return bool|array
     */
    public function getPartnerList(&$projectID)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT eo_ams_conn_project.userID,eo_ams_conn_project.connID,eo_ams_conn_project.userType,eo_user.userName,eo_user.userNickName,eo_ams_conn_project.partnerNickName FROM eo_ams_conn_project INNER JOIN eo_user ON eo_ams_conn_project.userID = eo_user.userID WHERE eo_ams_conn_project.projectID = ? ORDER BY eo_ams_conn_project.userType ASC;', array($projectID));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * quit project
     * @param $projectID int projectID
     * @param $userID int userID
     * @return bool
     */
    public function quitPartner(&$projectID, &$connID)
    {
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_ams_conn_project WHERE eo_ams_conn_project.projectID = ? AND eo_ams_conn_project.connID = ? AND eo_ams_conn_project.userType != 0;', array(
            $projectID,
            $connID
        ));

        if ($db->getAffectRow() > 0) {
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * check joined project or not
     * @param $projectID int projectID
     * @param $userName string username
     * @return bool
     */
    public function checkIsInvited(&$projectID, &$userName)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT eo_ams_conn_project.connID FROM eo_ams_conn_project INNER JOIN eo_user ON eo_user.userID = eo_ams_conn_project.userID WHERE eo_ams_conn_project.projectID = ? AND eo_user.userName = ?;', array(
            $projectID,
            $userName
        ));
        if (empty($result))
            return FALSE;
        else
            return TRUE;
    }

    /**
     * get userID
     * @param $connID int connID
     * @return bool|int
     */
    public function getUserID(&$connID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_ams_conn_project.userID FROM eo_ams_conn_project WHERE eo_ams_conn_project.connID = ?;', array($connID));
        if (empty($result))
            return FALSE;
        else
            return $result['userID'];
    }

    /**
     * edit developer name
     * @param $project_id int projectID
     * @param $conn_id int connID
     * @param $nick_name string nickname
     * @return bool
     */
    public function editPartnerNickName(&$project_id, &$conn_id, &$nick_name)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_ams_conn_project SET eo_ams_conn_project.partnerNickName = ? WHERE eo_ams_conn_project.connID = ? AND eo_ams_conn_project.projectID = ?;', array(
            $nick_name,
            $conn_id,
            $project_id
        ));

        if ($db->getAffectRow() > 0) {
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * edit developer type
     * @param $project_id int projectID
     * @param $conn_id int connID
     * @param $user_type int user type
     * @return bool
     */
    public function editPartnerType(&$project_id, &$conn_id, &$user_type)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_ams_conn_project SET eo_ams_conn_project.userType = ? WHERE eo_ams_conn_project.connID = ? AND eo_ams_conn_project.projectID = ?;', array(
            $user_type,
            $conn_id,
            $project_id
        ));

        if ($db->getAffectRow() > 0) {
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * get developer usercall
     * @param $user_id
     * @return bool
     */
    public function getPartnerUserCall(&$user_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_user.userName FROM eo_user WHERE eo_user.userID = ?;', array(
            $user_id
        ));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['userName'];
        }
    }

    public function getProjectInviteCode(&$project_id)
    {
        $db = getDatabase();   
       $count = 0;
        do {
            $count++;
           
            $invite_code = '';
            $strPool = 'NMqlzxcvdfghjQXCER67ty5HuasJKLZYTWmPASDFGk12iBpn34UIb9werV8';
            for ($i = 0; $i <= 5; $i++) {
                $invite_code .= $strPool[rand(0, 58)];
            }

            $result = $db->prepareExecute('SELECT eo_ams_project_invite.projectID FROM eo_ams_project_invite WHERE eo_ams_project_invite.projectInviteCode = ?;', array(
                $invite_code
            ));
        } while (!empty($result) && $count < 3);
        if (!empty($result)) {
            return FALSE;
        }
    }
}

?>
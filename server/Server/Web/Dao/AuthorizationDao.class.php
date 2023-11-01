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
class AuthorizationDao
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * Get user type in project
     * @param $userID int UserID
     * @param $projectID int ProjectID
     * @return bool
     */
    public function getProjectUserType(&$userID, &$projectID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_ams_conn_project.userType FROM eo_ams_conn_project WHERE eo_ams_conn_project.projectID = ? AND eo_ams_conn_project.userID = ?;', array(
            $projectID,
            $userID
        ));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['userType'];
        }
    }

    /**
     * Get Database User Type
     * @param $userID int User ID
     * @param $dbID int Database ID
     * @return bool
     */
    public function getDatabaseUserType(&$userID, &$dbID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_ams_conn_database.userType FROM eo_ams_conn_database WHERE eo_ams_conn_database.dbID = ? AND eo_ams_conn_database.userID = ?;', array(
            $dbID,
            $userID
        ));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['userType'];
        }
    }
}
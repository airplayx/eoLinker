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
class AuthorizationModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * get Project USER TYPE
     * @param $projectID int projectID
     * @return bool
     */
    public function getProjectUserType(&$projectID)
    {
        $dao = new AuthorizationDao();
        return $dao->getProjectUserType($_SESSION['userID'], $projectID);
    }

    /**
     * get database user type
     * @param $dbID int databaseID
     * @return bool
     */
    public function getDatabaseUserType(&$dbID)
    {
        $dao = new AuthorizationDao();
        return $dao->getDatabaseUserType($_SESSION['userID'], $dbID);
    }
}
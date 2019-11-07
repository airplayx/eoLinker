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

class TestHistoryDao
{
    /**
     * add test history
     * @param $projectID int projectID
     * @param $apiID int API ID
     * @param $requestInfo string test request Info
     * @param $resultInfo string test result
     * @param $testTime string test time
     * @return bool|int
     */
    public function addTestHistory(&$projectID, &$apiID, &$requestInfo, &$resultInfo, &$testTime)
    {
        $db = getDatabase();
        $db->prepareExecute('INSERT INTO eo_ams_api_test_history (eo_ams_api_test_history.projectID,eo_ams_api_test_history.apiID,eo_ams_api_test_history.requestInfo,eo_ams_api_test_history.resultInfo,eo_ams_api_test_history.testTime) VALUES (?,?,?,?,?);', array(
            $projectID,
            $apiID,
            $requestInfo,
            $resultInfo,
            $testTime
        ));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else {
            return $db->getLastInsertID();
        }
    }

    /**
     * delete test history
     * @param $testID int 
     * @return bool
     */
    public function deleteTestHistory(&$testID)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_ams_api_test_history WHERE eo_ams_api_test_history.testID =?;', array($testID));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * getTestHistory
     * @param $testID int ID
     * @return bool|array
     */
    public function getTestHistory(&$testID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_ams_api_test_history.projectID,eo_ams_api_test_history.apiID,eo_ams_api_test_history.testID,eo_ams_api_test_history.requestInfo,eo_ams_api_test_history.resultInfo,eo_ams_api_test_history.testTime FROM eo_ams_api_test_history WHERE testID =?;', array($testID));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * checkTestHistoryPermission
     * @param $testID int test record ID
     * @param $userID int userID
     * @return bool|int
     */
    public function checkTestHistoryPermission(&$testID, &$userID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_ams_conn_project.projectID FROM eo_ams_api_test_history INNER JOIN eo_ams_api INNER JOIN eo_ams_conn_project ON eo_ams_api.projectID = eo_ams_conn_project.projectID AND eo_ams_api.apiID = eo_ams_api_test_history.apiID WHERE eo_ams_api_test_history.testID = ? AND eo_ams_conn_project.userID = ?;', array(
            $testID,
            $userID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }

    /**
     *delete all test history
     * @param $apiID int API ID
     * @return bool
     */
    public function deleteAllTestHistory(&$apiID)
    {
        $db = getDatabase();
        $db->prepareExecuteAll('DELETE FROM eo_ams_api_test_history WHERE apiID = ?;', array($apiID));
        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    /**
     * 获取测试历史记录
     * @param int $testID 测试ID
     */
    public function getTestHistoryListFromDB(&$project_id, &$api_id)
    {
    	$db = getDatabase();
    	
    	$result = $db->prepareExecuteAll('SELECT eo_ams_api_test_history.testID,eo_ams_api_test_history.requestInfo,eo_ams_api_test_history.resultInfo,eo_ams_api_test_history.testTime FROM eo_ams_api_test_history WHERE eo_ams_api_test_history.apiID = ? AND eo_ams_api_test_history.projectID = ? ORDER BY eo_ams_api_test_history.testID DESC LIMIT 50;', array(
    			$api_id,
    			$project_id
    	));
    	if(empty($result))
    		return FALSE;
    		else
    			return $result;
    }
}

?>
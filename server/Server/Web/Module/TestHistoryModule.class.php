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

class TestHistoryModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * Add Test History
     * @param $apiID int 
     * @param $requestInfo string 
     * @param $resultInfo string 
     * @param $testTime string 
     * @return bool|int
     */
    public function addTestHistory(&$apiID, &$requestInfo, &$resultInfo, &$testTime)
    {
    
        if (empty($resultInfo)) {
            $resultInfo = '';
        }

        $projectDao = new ProjectDao;
        $apiDao = new ApiDao;
        $testHistoryDao = new TestHistoryDao;

        if ($projectID = $apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            return $testHistoryDao->addTestHistory($projectID, $apiID, $requestInfo, $resultInfo, $testTime);
        } else
            return FALSE;
    }

    /**
     * Delete Test History
     * @param $testID int 
     * @return bool
     */
    public function deleteTestHistory(&$testID)
    {
        $testHistoryDao = new TestHistoryDao;
        $projectDao = new ProjectDao;
        if ($projectID = $testHistoryDao->checkTestHistoryPermission($testID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            return $testHistoryDao->deleteTestHistory($testID);
        } else
            return FALSE;
    }

    /**
     * Get TEST hISTORY
     * @param $testID int 
     * @return bool|array
     */
    public function getTestHistory(&$testID)
    {
        $testHistoryDao = new TestHistoryDao;
        $projectDao = new ProjectDao;
        if ($projectID = $testHistoryDao->checkTestHistoryPermission($testID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            return $testHistoryDao->getTestHistory($testID);
        } else
            return FALSE;
    }

    /**
     * Delete all test history
     * @param $apiID int
     * @return bool
     */
    public function deleteAllTestHistory(&$apiID)
    {
        $apiDao = new ApiDao();
        if ($apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            $dao = new TestHistoryDao();
            return $dao->deleteAllTestHistory($apiID);
        } else {
            return FALSE;
        }
    }
    /**
     * 获取测试历史记录
     * @param int $testID
     * @return Ambigous <boolean, multitype:>|boolean
     */
    public function getTestHistoryList(&$projectID, &$api_id)
    {
    	
    	$dao = new TestHistoryDao();
    	$result = $dao  -> getTestHistoryListFromDB($projectID, $api_id);
    	if($result)
    	{
    		foreach ($result as &$history)
    		{
    			$history['requestInfo'] = json_decode($history['requestInfo'], TRUE);
    			$history['resultInfo'] = json_decode($history['resultInfo'], TRUE);
    		}
    		return $result;
    	}
    	else
    		return array();
    }
}

?>
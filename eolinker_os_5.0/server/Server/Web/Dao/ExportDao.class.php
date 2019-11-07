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

class ExportDao
{
    /**
     * Get project DATA
     * 
     * @param $projectID int ProjectID
     * @return array|bool
     */
    public function getProjectData(&$projectID)
    {
        $db = getdatabase();

        $dumpJson = array();

        
        $dumpJson['projectInfo'] = $db->prepareExecute("SELECT projectName,projectType,projectUpdateTime,projectDesc,projectVersion FROM eo_ams_api_project WHERE eo_ams_api_project.projectID = ?;", array($projectID));

        
        $api_group_list = $db->prepareExecuteAll("SELECT * FROM eo_ams_api_group WHERE eo_ams_api_group.projectID = ? AND eo_ams_api_group.isChild = ?;", array($projectID, 0));
        $i = 0;
        foreach ($api_group_list as $api_group) {
            $dumpJson['apiGroupList'][$i] = $api_group;

            
            $apiList = $db->prepareExecuteAll("SELECT eo_ams_api_cache.apiJson,eo_ams_api_cache.starred FROM eo_ams_api_cache INNER JOIN eo_ams_api ON eo_ams_api.apiID = eo_ams_api_cache.apiID WHERE eo_ams_api_cache.projectID = ? AND eo_ams_api_cache.groupID = ? AND eo_ams_api.removed = 0;", array(
                $projectID,
                $api_group['groupID']
            ));
            $dumpJson['apiGroupList'][$i]['apiList'] = array();
            $j = 0;
            foreach ($apiList as $api) {
                $dumpJson['apiGroupList'][$i]['apiList'][$j] = json_decode($api['apiJson'], TRUE);
                $dumpJson['apiGroupList'][$i]['apiList'][$j]['baseInfo']['starred'] = $api['starred'];
                ++$j;
            }
            $api_group_clild_list = $db->prepareExecuteAll("SELECT * FROM eo_ams_api_group WHERE eo_ams_api_group.parentGroupID = ? AND eo_ams_api_group.isChild = ?;", array($api_group['groupID'], 1));
            $k = 0;
            if ($api_group_clild_list) {
                foreach ($api_group_clild_list as $api_group_clid) {
                    $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k] = $api_group_clid;

                    
                    $apiList = $db->prepareExecuteAll("SELECT eo_ams_api_cache.apiJson,eo_ams_api_cache.starred FROM eo_ams_api_cache INNER JOIN eo_ams_api ON eo_ams_api.apiID = eo_ams_api_cache.apiID WHERE eo_ams_api_cache.projectID = ? AND eo_ams_api_cache.groupID = ? AND eo_ams_api.removed = 0;", array(
                        $projectID,
                        $api_group_clid['groupID']
                    ));
                    $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k]['apiList'] = array();
                    $x = 0;
                    foreach ($apiList as $api) {
                        $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k]['apiList'][$x] = json_decode($api['apiJson'], TRUE);
                        $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k]['apiList'][$x]['baseInfo']['starred'] = $api['starred'];
                        ++$x;
                    }
                    ++$k;
                }
            }
            ++$i;
        }

        
        $statusCodeGroupList = $db->prepareExecuteAll("SELECT * FROM eo_ams_project_status_code_group WHERE eo_ams_project_status_code_group.projectID = ? AND eo_ams_project_status_code_group.isChild = ?;", array($projectID, 0));

        $i = 0;
        foreach ($statusCodeGroupList as $statusCodeGroup) {
            $dumpJson['statusCodeGroupList'][$i] = $statusCodeGroup;

           
            $statusCodeList = $db->prepareExecuteAll("SELECT * FROM eo_ams_project_status_code WHERE eo_ams_project_status_code.groupID = ?;", array($statusCodeGroup['groupID']));

            $j = 0;
            foreach ($statusCodeList as $statusCode) {
                $dumpJson['statusCodeGroupList'][$i]['statusCodeList'][$j] = $statusCode;
                ++$j;
            }
            $statusCodeGroupList_child = $db->prepareExecuteAll("SELECT * FROM eo_ams_project_status_code_group WHERE eo_ams_project_status_code_group.parentGroupID = ? AND eo_ams_project_status_code_group.isChild = ? ;", array($statusCodeGroup['groupID'], 1));
            $k = 0;
            if ($statusCodeGroupList_child) {
                foreach ($statusCodeGroupList_child as $statusCodeGroup_child) {
                    $dumpJson['statusCodeGroupList'][$i]['statusCodeGroupChildList'][$k] = $statusCodeGroup_child;
                    $statusCodeList = $db->prepareExecuteAll("SELECT * FROM eo_ams_project_status_code WHERE eo_ams_project_status_code.groupID = ?;", array($statusCodeGroup_child['groupID']));
                    $x = 0;
                    foreach ($statusCodeList as $statusCode) {
                        $dumpJson['statusCodeGroupList'][$i]['statusCodeGroupChildList'][$k]['statusCodeList'][$x] = $statusCode;
                        ++$x;
                    }
                    ++$k;
                }
            }
            ++$i;
        }
        if (empty($dumpJson))
            return FALSE;
        else
            return $dumpJson;
    }

}

?>
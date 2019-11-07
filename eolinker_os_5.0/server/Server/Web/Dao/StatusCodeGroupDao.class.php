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

class StatusCodeGroupDao
{
    /**
     * add group
     * @param $projectID int projectID
     * @param $groupName string group name
     * @return int|bool
     */
    public function addGroup(&$projectID, &$groupName)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_ams_project_status_code_group (eo_ams_project_status_code_group.projectID,eo_ams_project_status_code_group.groupName) VALUES (?,?);', array(
            $projectID,
            $groupName
        ));

        $groupID = $db->getLastInsertID();

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $groupID;

    }

    /**
     * add childen group
     * @param $projectID int projectID
     * @param $groupName string parent group
     * @param $parentGroupID int parent groupID
     * @param $isChild
     * @return bool|int
     */
    public function addChildGroup(&$projectID, &$groupName, &$parentGroupID, &$isChild)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_ams_project_status_code_group (eo_ams_project_status_code_group.projectID,eo_ams_project_status_code_group.groupName,eo_ams_project_status_code_group.parentGroupID,eo_ams_project_status_code_group.isChild) VALUES (?,?,?,?);', array(
            $projectID,
            $groupName,
            $parentGroupID,
            $isChild
        ));

        $groupID = $db->getLastInsertID();

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $groupID;
    }

    /**
     * check status code group permission
     * @param $groupID int
     * @param $userID int
     * @return bool|int
     */
    public function checkStatusCodeGroupPermission(&$groupID, &$userID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_ams_conn_project.projectID FROM eo_ams_conn_project INNER JOIN eo_ams_project_status_code_group ON eo_ams_conn_project.projectID = eo_ams_project_status_code_group.projectID WHERE groupID = ? AND userID = ?;', array(
            $groupID,
            $userID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }

    /**
     * delete group
     * @param $groupID int groupID
     * @return bool
     */
    public function deleteGroup(&$groupID)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_ams_project_status_code_group WHERE eo_ams_project_status_code_group.groupID = ?;', array($groupID));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * get group list
     * @param $projectID int projectID
     * @return bool|array
     */
    public function getGroupList(&$projectID)
    {
        $db = getDatabase();

        $groupList = $db->prepareExecuteAll('SELECT eo_ams_project_status_code_group.groupID,eo_ams_project_status_code_group.groupName FROM eo_ams_project_status_code_group WHERE projectID = ? AND isChild = 0 ORDER BY eo_ams_project_status_code_group.groupID DESC;', array($projectID));

        if (is_array($groupList))
            foreach ($groupList as &$parentGroup) {
                $parentGroup['childGroupList'] = array();
                $childGroup = $db->prepareExecuteAll('SELECT eo_ams_project_status_code_group.groupID,eo_ams_project_status_code_group.groupName,eo_ams_project_status_code_group.parentGroupID FROM eo_ams_project_status_code_group WHERE projectID = ? AND isChild = 1 AND parentGroupID = ? ORDER BY eo_ams_project_status_code_group.groupID DESC;', array(
                    $projectID,
                    $parentGroup['groupID']
                ));

                if ($childGroup) {
                    foreach ($childGroup as &$group) {
                        $secondChildGroup = $db->prepareExecuteAll('SELECT eo_ams_project_status_code_group.groupID,eo_ams_project_status_code_group.groupName,eo_ams_project_status_code_group.parentGroupID FROM eo_ams_project_status_code_group WHERE projectID = ? AND isChild = 2 AND parentGroupID = ? ORDER BY eo_ams_project_status_code_group.groupID DESC;', array(
                            $projectID,
                            $group['groupID']
                        ));
                        if ($secondChildGroup) {
                            $group['childGroupList'] = $secondChildGroup;
                        } else {
                            $group['childGroupList'] = array();
                        }
                    }
                }

            
                if ($childGroup) {
                    $parentGroup['childGroupList'] = $childGroup;
                } else {
                    $parentGroup['childGroupList'] = array();
                }
            }

        $result = array();
        $result['groupList'] = $groupList;
        $groupOrder = $db->prepareExecute('SELECT eo_ams_api_status_code_group_order.orderList FROM eo_ams_api_status_code_group_order WHERE projectID = ?;', array(
            $projectID
        ));
        $result['groupOrder'] = $groupOrder['orderList'];

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * edit group
     * @param $groupID int gourpID
     * @param $groupName string group name
     * @param $parentGroupID int parent groupID
     * @param $isChild
     * @return bool
     */
    public function editGroup(&$groupID, &$groupName, $parentGroupID, &$isChild)
    {
        $db = getDatabase();

        if (!$parentGroupID) {
            $db->prepareExecute('UPDATE eo_ams_project_status_code_group SET eo_ams_project_status_code_group.groupName = ?,isChild = 0,parentGroupID = NULL WHERE eo_ams_project_status_code_group.groupID = ?;', array(
                $groupName,
                $groupID
            ));
        } else {
            $db->prepareExecute('UPDATE eo_ams_project_status_code_group SET eo_ams_project_status_code_group.groupName = ?,isChild = ?,parentGroupID = ? WHERE eo_ams_project_status_code_group.groupID = ?;', array(
                $groupName,
                $isChild,
                $parentGroupID,
                $groupID
            ));
        }


        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * update sort group
     * @param $projectID int projectID
     * @param $orderList string sort order
     * @return bool
     */
    public function sortGroup(&$projectID, &$orderList)
    {
        $db = getDatabase();
        $db->prepareExecute('REPLACE INTO eo_ams_api_status_code_group_order(projectID, orderList) VALUES (?,?);', array(
            $projectID,
            $orderList
        ));
        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * get group name
     * @param $group_id
     * @return bool
     */
    public function getGroupName(&$group_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_ams_project_status_code_group.groupName FROM eo_ams_project_status_code_group WHERE eo_ams_project_status_code_group.groupID = ?;', array($group_id));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['groupName'];
        }
    }

    /**
     *get group data
     * @param $project_id
     * @param $group_id
     * @return array|bool
     */
    public function getGroupData(&$project_id, &$group_id)
    {
        $db = getDatabase();
        $result = array();
        $group = $db->prepareExecute('SELECT eo_ams_project_status_code_group.groupName,eo_ams_project_status_code_group.isChild FROM eo_ams_project_status_code_group WHERE eo_ams_project_status_code_group.projectID = ? AND eo_ams_project_status_code_group.groupID = ?;', array(
            $project_id,
            $group_id
        ));
        $result['statusCodeList'] = $db->prepareExecuteAll("SELECT eo_ams_project_status_code.codeID,eo_ams_project_status_code.code,eo_ams_project_status_code.codeDescription FROM eo_ams_project_status_code WHERE eo_ams_project_status_code.groupID = ?", array(
            $group_id
        ));
        $result['groupName'] = $group['groupName'];
        if ($group['isChild'] <= 1) {
            $child_group_list = $db->prepareExecuteAll('SELECT eo_ams_project_status_code_group.groupID,eo_ams_project_status_code_group.groupName FROM eo_ams_project_status_code_group WHERE eo_ams_project_status_code_group.parentGroupID = ? AND eo_ams_project_status_code_group.projectID = ?', array(
                $group_id,
                $project_id
            ));
            if ($child_group_list) {
                $i = 0;
                foreach ($child_group_list as $group) {
                    $result['childGroupList'][$i]['groupID'] = $group['groupID'];
                    $result['childGroupList'][$i]['groupName'] = $group['groupName'];
                    $result['childGroupList'][$i]['statusCodeList'] = $db->prepareExecuteAll("SELECT eo_ams_project_status_code.codeID,eo_ams_project_status_code.code,eo_ams_project_status_code.codeDescription FROM eo_ams_project_status_code WHERE eo_ams_project_status_code.groupID = ?", array(
                        $group['groupID']
                    ));
                    $group_list = $db->prepareExecuteAll('SELECT eo_ams_project_status_code_group.groupID,eo_ams_project_status_code_group.groupName FROM eo_ams_project_status_code_group WHERE eo_ams_project_status_code_group.parentGroupID = ? AND eo_ams_project_status_code_group.projectID = ?', array(
                        $group['groupID'],
                        $project_id
                    ));
                    if ($group_list) {
                        $j = 0;
                        foreach ($group_list as $child_group) {
                            $result['childGroupList'][$i]['childGroupList'][$j]['groupID'] = $child_group['groupID'];
                            $result['childGroupList'][$i]['childGroupList'][$j]['groupName'] = $child_group['groupName'];
                            $result['childGroupList'][$i]['childGroupList'][$j]['statusCodeList'] = $db->prepareExecuteAll("SELECT eo_ams_project_status_code.codeID,eo_ams_project_status_code.code,eo_ams_project_status_code.codeDescription FROM eo_ams_project_status_code WHERE eo_ams_project_status_code.groupID = ?", array(
                                $child_group['groupID']
                            ));
                            $j++;
                        }
                    }
                    $i++;
                }
            }
        }
        if ($result)
            return $result;
        else
            return FALSE;
    }

    /**
     * import gourp
     * @param $project_id
     * @param $data
     * @return bool
     */
    public function importGroup(&$project_id, &$data)
    {
        $db = getDatabase();
        try {
            $db->beginTransaction();
       
            $db->prepareExecute('INSERT INTO eo_ams_project_status_code_group (eo_ams_project_status_code_group.projectID,eo_ams_project_status_code_group.groupName) VALUES (?,?);', array(
                $project_id,
                $data['groupName']
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("add statusCodeGroup error");
            $group_id = $db->getLastInsertID();
            if ($data['statusCodeList']) {
                
                foreach ($data['statusCodeList'] as $status_code) {
                    $db->prepareExecute('INSERT INTO eo_ams_project_status_code (eo_ams_project_status_code.groupID,eo_ams_project_status_code.code,eo_ams_project_status_code.codeDescription) VALUES (?,?,?);', array(
                        $group_id,
                        $status_code['code'],
                        $status_code['codeDescription']
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("add statusCode error");
                }
            }
            if ($data['childGroupList']) {
                $group_id_parent = $group_id;
                foreach ($data['childGroupList'] as $child_group) {
                    
                    $db->prepareExecute('INSERT INTO eo_ams_project_status_code_group (eo_ams_project_status_code_group.projectID,eo_ams_project_status_code_group.groupName,eo_ams_project_status_code_group.parentGroupID,eo_ams_project_status_code_group.isChild) VALUES (?,?,?,?);', array(
                        $project_id,
                        $child_group['groupName'],
                        $group_id_parent,
                        1
                    ));
                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("add statusCodeGroup error");
                    }

                    $group_id = $db->getLastInsertID();
                    if ($child_group['statusCodeList']) {
                        
                        foreach ($child_group['statusCodeList'] as $status_code) {
                            $db->prepareExecute('INSERT INTO eo_ams_project_status_code (eo_ams_project_status_code.groupID,eo_ams_project_status_code.code,eo_ams_project_status_code.codeDescription) VALUES (?,?,?);', array(
                                $group_id,
                                $status_code['code'],
                                $status_code['codeDescription']
                            ));

                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("add statusCode error");
                            }
                        }
                    }

                    if ($child_group['childGroupList']) {
                        $parent_id = $group_id;
                        foreach ($child_group['childGroupList'] as $group) {
                            
                            $db->prepareExecute('INSERT INTO eo_ams_project_status_code_group (eo_ams_project_status_code_group.projectID,eo_ams_project_status_code_group.groupName,eo_ams_project_status_code_group.parentGroupID,eo_ams_project_status_code_group.isChild) VALUES (?,?,?,?);', array(
                                $project_id,
                                $group['groupName'],
                                $parent_id,
                                2
                            ));
                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("add statusCodeGroup error");
                            }

                            $group_id = $db->getLastInsertID();
                            if ($group['statusCodeList']) {
                               
                                foreach ($child_group['statusCodeList'] as $status_code) {
                                    $db->prepareExecute('INSERT INTO eo_ams_project_status_code (eo_ams_project_status_code.groupID,eo_ams_project_status_code.code,eo_ams_project_status_code.codeDescription) VALUES (?,?,?);', array(
                                        $group_id,
                                        $status_code['code'],
                                        $status_code['codeDescription']
                                    ));

                                    if ($db->getAffectRow() < 1) {
                                        throw new \PDOException("add statusCode error");
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $db->commit();
            return TRUE;
        } catch (\PDOException $e) {
            $db->rollback();
            return FALSE;
        }
    }
}

?>
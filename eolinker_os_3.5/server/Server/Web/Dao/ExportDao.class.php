<?php

/**
 * @name eolinker ams open source，eolinker开源版本
 * @link https://www.eolinker.com/
 * @package eolinker
 * @author www.eolinker.com 广州银云信息科技有限公司 2015-2017
 * eoLinker是目前全球领先、国内最大的在线API接口管理平台，提供自动生成API文档、API自动化测试、Mock测试、团队协作等功能，旨在解决由于前后端分离导致的开发效率低下问题。
 * 如在使用的过程中有任何问题，欢迎加入用户讨论群进行反馈，我们将会以最快的速度，最好的服务态度为您解决问题。
 *
 * eoLinker AMS开源版的开源协议遵循Apache License 2.0，如需获取最新的eolinker开源版以及相关资讯，请访问:https://www.eolinker.com/#/os/download
 *
 * 官方网站：https://www.eolinker.com/
 * 官方博客以及社区：http://blog.eolinker.com/
 * 使用教程以及帮助：http://help.eolinker.com/
 * 商务合作邮箱：market@eolinker.com
 * 用户讨论QQ群：284421832
 */
class ExportDao
{
    /**
     * 获取项目数据
     * @param $projectID int 项目ID
     * @return array|bool
     */
    public function getProjectData(&$projectID)
    {
        $db = getdatabase();

        $dumpJson = array();

        //获取项目信息
        $dumpJson['projectInfo'] = $db->prepareExecute("SELECT projectName,projectType,projectUpdateTime,projectDesc,projectVersion FROM eo_api_project WHERE eo_api_project.projectID = ?;", array($projectID));

        //获取接口父分组信息
        $api_group_list = $db->prepareExecuteAll("SELECT * FROM eo_api_group WHERE eo_api_group.projectID = ? AND eo_api_group.isChild = ?;", array($projectID, 0));
        $i = 0;
        foreach ($api_group_list as $api_group) {
            $dumpJson['apiGroupList'][$i] = $api_group;

            //获取接口信息
            $apiList = $db->prepareExecuteAll("SELECT eo_api_cache.apiJson,eo_api_cache.starred FROM eo_api_cache INNER JOIN eo_api ON eo_api.apiID = eo_api_cache.apiID WHERE eo_api_cache.projectID = ? AND eo_api_cache.groupID = ? AND eo_api.removed = 0;", array(
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
            $api_group_clild_list = $db->prepareExecuteAll("SELECT * FROM eo_api_group WHERE eo_api_group.parentGroupID = ? AND eo_api_group.isChild = ?;", array($api_group['groupID'], 1));
            $k = 0;
            if ($api_group_clild_list) {
                foreach ($api_group_clild_list as $api_group_clid) {
                    $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k] = $api_group_clid;

                    //获取接口信息
                    $apiList = $db->prepareExecuteAll("SELECT eo_api_cache.apiJson,eo_api_cache.starred FROM eo_api_cache INNER JOIN eo_api ON eo_api.apiID = eo_api_cache.apiID WHERE eo_api_cache.projectID = ? AND eo_api_cache.groupID = ? AND eo_api.removed = 0;", array(
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

        //获取状态码分组信息
        $statusCodeGroupList = $db->prepareExecuteAll("SELECT * FROM eo_api_status_code_group WHERE eo_api_status_code_group.projectID = ? AND eo_api_status_code_group.isChild = ?;", array($projectID, 0));

        $i = 0;
        foreach ($statusCodeGroupList as $statusCodeGroup) {
            $dumpJson['statusCodeGroupList'][$i] = $statusCodeGroup;

            //获取状态码信息
            $statusCodeList = $db->prepareExecuteAll("SELECT * FROM eo_api_status_code WHERE eo_api_status_code.groupID = ?;", array($statusCodeGroup['groupID']));

            $j = 0;
            foreach ($statusCodeList as $statusCode) {
                $dumpJson['statusCodeGroupList'][$i]['statusCodeList'][$j] = $statusCode;
                ++$j;
            }
            $statusCodeGroupList_child = $db->prepareExecuteAll("SELECT * FROM eo_api_status_code_group WHERE eo_api_status_code_group.parentGroupID = ? AND eo_api_status_code_group.isChild = ? ;", array($statusCodeGroup['groupID'], 1));
            $k = 0;
            if ($statusCodeGroupList_child) {
                foreach ($statusCodeGroupList_child as $statusCodeGroup_child) {
                    $dumpJson['statusCodeGroupList'][$i]['statusCodeGroupChildList'][$k] = $statusCodeGroup_child;
                    $statusCodeList = $db->prepareExecuteAll("SELECT * FROM eo_api_status_code WHERE eo_api_status_code.groupID = ?;", array($statusCodeGroup_child['groupID']));
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
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
class ImportDao
{

    /**
     * 导入eolinker
     * @param $data array 从eolinker导出的json格式数据
     * @param $user_id int 用户ID
     * @return bool
     */
    public function importEoapi(&$data, &$user_id)
    {
        $db = getDatabase();
        try {
            // 开始事务
            $db->beginTransaction();

            // 插入项目
            $db->prepareExecute('INSERT INTO eo_project(eo_project.projectName,eo_project.projectType,eo_project.projectVersion,eo_project.projectUpdateTime) VALUES (?,?,?,?);', array(
                $data['projectInfo']['projectName'],
                $data['projectInfo']['projectType'],
                $data['projectInfo']['projectVersion'],
                date('Y-m-d H:i:s', time())
            ));
            if ($db->getAffectRow() < 1)
                throw new \PDOException("addProject error");

            // 获取projectID
            $project_id = $db->getLastInsertID();

            // 生成项目与用户的联系
            $db->prepareExecute('INSERT INTO eo_conn_project(eo_conn_project.projectID,eo_conn_project.userID,eo_conn_project.userType) VALUES (?,?,0);', array(
                $project_id,
                $user_id
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("addConnProject error");

            if (!empty($data['apiGroupList'])) {
                // 插入接口分组信息
                foreach ($data['apiGroupList'] as $api_group) {
                    $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID) VALUES (?,?);', array(
                        $api_group['groupName'],
                        $project_id
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addGroup error");

                    $group_id = $db->getLastInsertID();
                    if ($api_group['apiList']) {
                        foreach ($api_group['apiList'] as $api) {
                            // 插入api基本信息
                            $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime,eo_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                                $api['baseInfo']['apiName'],
                                $api['baseInfo']['apiURI'],
                                $api['baseInfo']['apiProtocol'],
                                $api['baseInfo']['apiSuccessMock'],
                                $api['baseInfo']['apiFailureMock'],
                                $api['baseInfo']['apiRequestType'],
                                $api['baseInfo']['apiStatus'],
                                $group_id,
                                $project_id,
                                $api['baseInfo']['starred'],
                                $api['baseInfo']['apiNoteType'],
                                $api['baseInfo']['apiNoteRaw'],
                                $api['baseInfo']['apiNote'],
                                $api['baseInfo']['apiRequestParamType'],
                                $api['baseInfo']['apiRequestRaw'],
                                $api['baseInfo']['apiUpdateTime'],
                                $user_id
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addApi error");

                            $api_id = $db->getLastInsertID();

                            // 插入header信息
                            foreach ($api['headerInfo'] as $header) {
                                $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                                    $header['headerName'],
                                    $header['headerValue'],
                                    $api_id
                                ));

                                if ($db->getAffectRow() < 1)
                                    throw new \PDOException("addHeader error");
                            }

                            // 插入api请求值信息
                            foreach ($api['requestInfo'] as $request) {
                                $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                                    $api_id,
                                    $request['paramName'],
                                    $request['paramKey'],
                                    $request['paramValue'],
                                    $request['paramLimit'],
                                    $request['paramNotNull'],
                                    $request['paramType']
                                ));

                                if ($db->getAffectRow() < 1)
                                    throw new \PDOException("addRequestParam error");

                                $param_id = $db->getLastInsertID();

                                foreach ($request['paramValueList'] as $value) {
                                    $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                        $param_id,
                                        $value['value'],
                                        $value['valueDescription']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addApi error");
                                };
                            };

                            // 插入api返回值信息
                            foreach ($api['resultInfo'] as $result) {
                                $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                                    $api_id,
                                    $result['paramName'],
                                    $result['paramKey'],
                                    $result['paramNotNull']
                                ));

                                if ($db->getAffectRow() < 1)
                                    throw new \PDOException("addResultParam error");

                                $param_id = $db->getLastInsertID();

                                foreach ($result['paramValueList'] as $value) {
                                    $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                        $param_id,
                                        $value['value'],
                                        $value['valueDescription']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addApi error");
                                };
                            };

                            // 插入api缓存数据用于导出
                            $db->prepareExecute("INSERT INTO eo_api_cache (eo_api_cache.projectID,eo_api_cache.groupID,eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred) VALUES (?,?,?,?,?);", array(
                                $project_id,
                                $group_id,
                                $api_id,
                                json_encode($api),
                                $api['baseInfo']['starred']
                            ));

                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("addApiCache error");
                            }
                        }
                    }
                    // 二级分组代码
                    if ($api_group['apiGroupChildList']) {
                        $group_parent_id = $group_id;
                        foreach ($api_group['apiGroupChildList'] as $api_group_child) {
                            $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID,eo_api_group.parentGroupID, eo_api_group.isChild) VALUES (?,?,?,?);', array(
                                $api_group_child['groupName'],
                                $project_id,
                                $group_parent_id,
                                1
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addChildGroup error");

                            $group_id = $db->getLastInsertID();

                            // 如果当前分组没有接口，则跳过到下一分组
                            if (empty($api_group_child['apiList']))
                                continue;

                            foreach ($api_group_child['apiList'] as $api) {
                                // 插入api基本信息
                                $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime,eo_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                                    $api['baseInfo']['apiName'],
                                    $api['baseInfo']['apiURI'],
                                    $api['baseInfo']['apiProtocol'],
                                    $api['baseInfo']['apiSuccessMock'],
                                    $api['baseInfo']['apiFailureMock'],
                                    $api['baseInfo']['apiRequestType'],
                                    $api['baseInfo']['apiStatus'],
                                    $group_id,
                                    $project_id,
                                    $api['baseInfo']['starred'],
                                    $api['baseInfo']['apiNoteType'],
                                    $api['baseInfo']['apiNoteRaw'],
                                    $api['baseInfo']['apiNote'],
                                    $api['baseInfo']['apiRequestParamType'],
                                    $api['baseInfo']['apiRequestRaw'],
                                    $api['baseInfo']['apiUpdateTime'],
                                    $user_id
                                ));

                                if ($db->getAffectRow() < 1)
                                    throw new \PDOException("addChildApi error");

                                $api_id = $db->getLastInsertID();

                                // 插入header信息
                                foreach ($api['headerInfo'] as $header) {
                                    $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                                        $header['headerName'],
                                        $header['headerValue'],
                                        $api_id
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildHeader error");
                                }

                                // 插入api请求值信息
                                foreach ($api['requestInfo'] as $request) {
                                    $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                                        $api_id,
                                        $request['paramName'],
                                        $request['paramKey'],
                                        $request['paramValue'],
                                        $request['paramLimit'],
                                        $request['paramNotNull'],
                                        $request['paramType']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildRequestParam error");

                                    $param_id = $db->getLastInsertID();
                                    if ($request['paramValueList']) {
                                        foreach ($request['paramValueList'] as $value) {
                                            $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                                $param_id,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildApi error");
                                        };
                                    }
                                };

                                // 插入api返回值信息
                                foreach ($api['resultInfo'] as $result) {
                                    $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                                        $api_id,
                                        $result['paramName'],
                                        $result['paramKey'],
                                        $result['paramNotNull']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildResultParam error");

                                    $param_id = $db->getLastInsertID();
                                    if ($result['paramValueList']) {
                                        foreach ($result['paramValueList'] as $value) {
                                            $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                                $param_id,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildParamValue error");
                                        };
                                    }
                                };

                                // 插入api缓存数据用于导出
                                $db->prepareExecute("INSERT INTO eo_api_cache (eo_api_cache.projectID,eo_api_cache.groupID,eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred) VALUES (?,?,?,?,?);", array(
                                    $project_id,
                                    $group_id,
                                    $api_id,
                                    json_encode($api),
                                    $api['baseInfo']['starred']
                                ));

                                if ($db->getAffectRow() < 1) {
                                    throw new \PDOException("addChildApiCache error");
                                }
                            }
                        }
                    }
                }
            }
            //插入状态码
            if (!empty($data['statusCodeGroupList'])) {
                // 导入状态码
                foreach ($data['statusCodeGroupList'] as $status_codeGroup) {
                    // 插入分组
                    $db->prepareExecute('INSERT INTO eo_project_status_code_group (eo_project_status_code_group.projectID,eo_project_status_code_group.groupName) VALUES (?,?);', array(
                        $project_id,
                        $status_codeGroup['groupName']
                    ));

                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("addChildstatusCodeGroup error");
                    }

                    $group_id = $db->getLastInsertID();

                    if (empty($status_codeGroup['statusCodeList']))
                        continue;

                    // 插入状态码
                    foreach ($status_codeGroup['statusCodeList'] as $status_code) {
                        $db->prepareExecute('INSERT INTO eo_project_status_code (eo_project_status_code.groupID,eo_project_status_code.code,eo_project_status_code.codeDescription) VALUES (?,?,?);', array(
                            $group_id,
                            $status_code['code'],
                            $status_code['codeDescription']
                        ));

                        if ($db->getAffectRow() < 1) {
                            throw new \PDOException("add statusCode error");
                        }
                    }
                    if ($status_codeGroup['statusCodeGroupChildList']) {
                        $group_id_parent = $group_id;
                        foreach ($status_codeGroup['statusCodeGroupChildList'] as $status_codeGroup_child) {
                            // 插入分组
                            $db->prepareExecute('INSERT INTO eo_project_status_code_group (eo_project_status_code_group.projectID,eo_project_status_code_group.groupName,eo_project_status_code_group.parentGroupID,eo_project_status_code_group.isChild) VALUES (?,?,?,?);', array(
                                $project_id,
                                $status_codeGroup_child['groupName'],
                                $group_id_parent,
                                1
                            ));
                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("addChildStatusCodeGroup error");
                            }

                            $group_id = $db->getLastInsertID();
                            if (empty($status_codeGroup_child['statusCodeList']))
                                continue;

                            // 插入状态码
                            foreach ($status_codeGroup_child['statusCodeList'] as $status_code) {
                                $db->prepareExecute('INSERT INTO eo_project_status_code (eo_project_status_code.groupID,eo_project_status_code.code,eo_project_status_code.codeDescription) VALUES (?,?,?);', array(
                                    $group_id,
                                    $status_code['code'],
                                    $status_code['codeDescription']
                                ));

                                if ($db->getAffectRow() < 1) {
                                    throw new \PDOException("addChildStatusCode error");
                                }
                            }
                        }
                    }
                }
            }
            //插入文档信息
            if (!empty($data['pageGroupList'])) {
                //导入状态码
                foreach ($data['pageGroupList'] as $pageGroup) {
                    //插入分组
                    $db->prepareExecute('INSERT INTO eo_project_document_group(eo_project_document_group.projectID,eo_project_document_group.groupName) VALUES (?,?);', array(
                        $project_id,
                        $pageGroup['groupName']
                    ));

                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("add pageGroup error");
                    }

                    $group_id = $db->getLastInsertID();
                    //插入状态码
                    foreach ($pageGroup['pageList'] as $page) {
                        $db->prepareExecute('INSERT INTO eo_project_document(eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.updateTime,eo_project_document.userID) VALUES (?,?,?,?,?,?,?,?);', array(
                            $group_id,
                            $project_id,
                            $page['contentType'],
                            $page['contentRaw'],
                            $page['content'],
                            $page['title'],
                            $page['updateTime'],
                            $user_id,
                        ));

                        if ($db->getAffectRow() < 1) {
                            throw new \PDOException("add page error");
                        }
                    }
                    if ($pageGroup['pageGroupChildList']) {
                        $group_id_parent = $group_id;
                        foreach ($pageGroup['pageGroupChildList'] as $page_group_child) {
                            //插入分组
                            $db->prepareExecute('INSERT INTO eo_project_document_group(eo_project_document_group.projectID,eo_project_document_group.groupName,eo_project_document_group.parentGroupID,eo_project_document_group.isChild) VALUES (?,?,?,?);', array(
                                $project_id,
                                $page_group_child['groupName'],
                                $group_id_parent,
                                1,
                            ));
                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("add pageGroup error");
                            }

                            $group_id = $db->getLastInsertID();
                            //插入状态码
                            foreach ($page_group_child['pageList'] as $page) {
                                $db->prepareExecute('INSERT INTO eo_project_document(eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.updateTime,eo_project_document.userID) VALUES (?,?,?,?,?,?,?,?);', array(
                                    $group_id,
                                    $project_id,
                                    $page['contentType'],
                                    $page['contentRaw'],
                                    $page['content'],
                                    $page['title'],
                                    $page['updateTime'],
                                    $user_id,
                                ));
                                if ($db->getAffectRow() < 1)
                                    throw new \PDOException("add page error");
                            }
                        }
                    }
                }
            }
            //插入环境信息
            if (!empty($data['env'])) {
                foreach ($data['env'] as $env) {
                    $db->prepareExecute("INSERT INTO eo_api_env (eo_api_env.envName,eo_api_env.projectID) VALUES (?,?);", array(
                        $env['envName'],
                        $project_id
                    ));
                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("add env error");
                    $env_id = $db->getLastInsertID();
                    $db->prepareExecute("INSERT INTO eo_api_env_front_uri (eo_api_env_front_uri.envID,eo_api_env_front_uri.applyProtocol,eo_api_env_front_uri.uri) VALUES (?,?,?);", array(
                        $env_id,
                        $env['frontURI']['applyProtocol'],
                        $env['frontURI']['uri']
                    ));
                    foreach ($env['headerList'] as $header) {
                        $db->prepareExecute("INSERT INTO eo_api_env_header (eo_api_env_header.envID,eo_api_env_header.applyProtocol,eo_api_env_header.headerName,eo_api_env_header.headerValue) VALUES (?,?,?,?);", array(
                            $env_id,
                            $header['applyProtocol'],
                            $header['headerName'],
                            $header['headerValue']
                        ));
                    }
                    foreach ($env['paramList'] as $param) {
                        $db->prepareExecute("INSERT INTO eo_api_env_param (eo_api_env_param.envID,eo_api_env_param.paramKey,eo_api_env_param.paramValue) VALUES (?,?,?);", array(
                            $env_id,
                            $param['paramKey'],
                            $param['paramValue']
                        ));
                    }
                }
            }
        } catch (\PDOException $e) {
            $db->rollBack();
            return FALSE;
        }
        $db->commit();
        return TRUE;
    }

    /**
     * 导入其他
     * @param $projectInfo array 项目信息
     * @param $groupInfoList array 分组信息
     * @param $userID int 用户ID
     * @return bool
     */
    public function importOther(&$projectInfo, &$groupInfoList, &$userID)
    {
        $db = getDatabase();
        try {
            // 开始事务
            $db->beginTransaction();
            // 插入项目
            $db->prepareExecute('INSERT INTO eo_project(eo_project.projectName,eo_project.projectType,eo_project.projectVersion,eo_project.projectUpdateTime) VALUES (?,?,?,?);', array(
                $projectInfo['projectName'],
                $projectInfo['projectType'],
                $projectInfo['projectVersion'],
                date('Y-m-d H:i:s', time())
            ));
            if ($db->getAffectRow() < 1)
                throw new \PDOException("addProject error");

            $projectID = $db->getLastInsertID();

            // 生成项目与用户的联系
            $db->prepareExecute('INSERT INTO eo_conn_project (eo_conn_project.projectID,eo_conn_project.userID,eo_conn_project.userType) VALUES (?,?,0);', array(
                $projectID,
                $userID
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("addConnProject error");

            if (is_array($groupInfoList)) {
                foreach ($groupInfoList as $groupInfo) {
                    $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID) VALUES (?,?);', array(
                        $groupInfo['groupName'],
                        $projectID
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addGroup error");

                    $groupID = $db->getLastInsertID();
                    if (is_array($groupInfo['apiList'])) {
                        foreach ($groupInfo['apiList'] as $api) {
                            // 插入api基本信息
                            $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime,eo_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                                $api['baseInfo']['apiName'],
                                $api['baseInfo']['apiURI'],
                                $api['baseInfo']['apiProtocol'],
                                $api['baseInfo']['apiSuccessMock'],
                                $api['baseInfo']['apiFailureMock'],
                                $api['baseInfo']['apiRequestType'],
                                $api['baseInfo']['apiStatus'],
                                $groupID,
                                $projectID,
                                $api['baseInfo']['starred'],
                                $api['baseInfo']['apiNoteType'],
                                $api['baseInfo']['apiNoteRaw'],
                                $api['baseInfo']['apiNote'],
                                $api['baseInfo']['apiRequestParamType'],
                                $api['baseInfo']['apiRequestRaw'],
                                $api['baseInfo']['apiUpdateTime'],
                                $userID
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addApi error");

                            $apiID = $db->getLastInsertID();

                            // 插入header信息
                            if (is_array($api['headerInfo'])) {
                                foreach ($api['headerInfo'] as $param) {
                                    $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                                        $param['headerName'],
                                        $param['headerValue'],
                                        $apiID
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addHeader error");
                                }
                            }

                            // 插入api请求值信息
                            if (is_array($api['requestInfo'])) {
                                foreach ($api['requestInfo'] as $param) {
                                    $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                                        $apiID,
                                        $param['paramName'],
                                        $param['paramKey'],
                                        ($param['paramValue']) ? $param['paramValue'] : "",
                                        $param['paramLimit'],
                                        $param['paramNotNull'],
                                        $param['paramType']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addRequestParam error");

                                    $paramID = $db->getLastInsertID();

                                    if (is_array($param['paramValueList'])) {
                                        foreach ($param['paramValueList'] as $value) {
                                            $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);;', array(
                                                $paramID,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addRequestParamValue error");
                                        };
                                    }
                                };
                            }

                            // 插入api返回值信息
                            if (is_array($api['resultInfo'])) {
                                foreach ($api['resultInfo'] as $param) {
                                    $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                                        $apiID,
                                        $param['paramName'],
                                        $param['paramKey'],
                                        $param['paramNotNull']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addResultParam error");

                                    $paramID = $db->getLastInsertID();

                                    if (is_array($param['paramValueList'])) {
                                        foreach ($param['paramValueList'] as $value) {
                                            $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                                $paramID,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addResultParamValue error");
                                        };
                                    }
                                };
                            }

                            // 插入api缓存数据用于导出
                            $db->prepareExecute("INSERT INTO eo_api_cache (eo_api_cache.projectID,eo_api_cache.groupID,eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred) VALUES (?,?,?,?,?);", array(
                                $projectID,
                                $groupID,
                                $apiID,
                                json_encode($api),
                                0
                            ));

                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("addApiCache error");
                            }
                        }
                    }

                    if (is_array($groupInfo['childGroupList'])) {
                        foreach ($groupInfo['childGroupList'] as $childGroupInfo) {
                            $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID,eo_api_group.parentGroupID,eo_api_group.isChild) VALUES (?,?,?,?);', array(
                                $childGroupInfo['groupName'],
                                $projectID,
                                $groupID,
                                1
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addChildGroup error");

                            $childGroupID = $db->getLastInsertID();

                            if (is_array($childGroupInfo['apiList'])) {
                                foreach ($childGroupInfo['apiList'] as $api) {
                                    // 插入api基本信息
                                    $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime,eo_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                                        $api['baseInfo']['apiName'],
                                        $api['baseInfo']['apiURI'],
                                        $api['baseInfo']['apiProtocol'],
                                        $api['baseInfo']['apiSuccessMock'],
                                        $api['baseInfo']['apiFailureMock'],
                                        $api['baseInfo']['apiRequestType'],
                                        $api['baseInfo']['apiStatus'],
                                        $childGroupID,
                                        $projectID,
                                        $api['baseInfo']['starred'],
                                        $api['baseInfo']['apiNoteType'],
                                        $api['baseInfo']['apiNoteRaw'],
                                        $api['baseInfo']['apiNote'],
                                        $api['baseInfo']['apiRequestParamType'],
                                        $api['baseInfo']['apiRequestRaw'],
                                        $api['baseInfo']['apiUpdateTime'],
                                        $userID
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildGroupApi error");

                                    $apiID = $db->getLastInsertID();

                                    // 插入header信息
                                    if (is_array($api['headerInfo'])) {
                                        foreach ($api['headerInfo'] as $param) {
                                            $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                                                $param['headerName'],
                                                $param['headerValue'],
                                                $apiID
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildGroupHeader error");
                                        }
                                    }

                                    // 插入api请求值信息
                                    if (is_array($api['requestInfo'])) {
                                        foreach ($api['requestInfo'] as $param) {
                                            $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                                                $apiID,
                                                $param['paramName'],
                                                $param['paramKey'],
                                                ($param['paramValue']) ? $param['paramValue'] : "",
                                                $param['paramLimit'],
                                                $param['paramNotNull'],
                                                $param['paramType']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildGroupRequestParam error");

                                            $paramID = $db->getLastInsertID();

                                            if (is_array($param['paramValueList'])) {
                                                foreach ($param['paramValueList'] as $value) {
                                                    $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);;', array(
                                                        $paramID,
                                                        $value['value'],
                                                        $value['valueDescription']
                                                    ));

                                                    if ($db->getAffectRow() < 1)
                                                        throw new \PDOException("addChildGroupRequestParamValue error");
                                                };
                                            }
                                        };
                                    }

                                    // 插入api返回值信息
                                    if (is_array($api['resultInfo'])) {
                                        foreach ($api['resultInfo'] as $param) {
                                            $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                                                $apiID,
                                                $param['paramName'],
                                                $param['paramKey'],
                                                $param['paramNotNull']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildGroupResultParam error");

                                            $paramID = $db->getLastInsertID();

                                            if (is_array($param['paramValueList'])) {
                                                foreach ($param['paramValueList'] as $value) {
                                                    $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                                        $paramID,
                                                        $value['value'],
                                                        $value['valueDescription']
                                                    ));

                                                    if ($db->getAffectRow() < 1)
                                                        throw new \PDOException("addChildGroupResultParamValue error");
                                                };
                                            }
                                        };
                                    }

                                    // 插入api缓存数据用于导出
                                    $db->prepareExecute("INSERT INTO eo_api_cache (eo_api_cache.projectID,eo_api_cache.groupID,eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred) VALUES (?,?,?,?,?);", array(
                                        $projectID,
                                        $childGroupID,
                                        $apiID,
                                        json_encode($api),
                                        0
                                    ));

                                    if ($db->getAffectRow() < 1) {
                                        throw new \PDOException("addChildGroupApiCache error");
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\PDOException $e) {
            var_dump($e->getMessage());
            $db->rollBack();
            return FALSE;
        }
        $db->commit();
        return TRUE;
    }
}

?>
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

class ImportDao
{

    /**
     * import eolinker
     * @param $data array 
     * @param $user_id int UserID
     * @return bool
     */
    public function importEoapi(&$data, &$user_id)
    {
        $db = getDatabase();
        try {
            
            $db->beginTransaction();

            
            $db->prepareExecute('INSERT INTO eo_ams_project(eo_ams_project.projectName,eo_ams_project.projectType,eo_ams_project.projectVersion,eo_ams_project.projectUpdateTime) VALUES (?,?,?,?);', array(
                $data['projectInfo']['projectName'],
                $data['projectInfo']['projectType'],
                $data['projectInfo']['projectVersion'],
                date('Y-m-d H:i:s', time())
            ));
            if ($db->getAffectRow() < 1)
                throw new \PDOException("addProject error");

            
            $project_id = $db->getLastInsertID();

            
            $db->prepareExecute('INSERT INTO eo_ams_conn_project(eo_ams_conn_project.projectID,eo_ams_conn_project.userID,eo_ams_conn_project.userType) VALUES (?,?,0);', array(
                $project_id,
                $user_id
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("addConnProject error");

            if (!empty($data['apiGroupList'])) {
                
                foreach ($data['apiGroupList'] as $api_group) {
                    $db->prepareExecute('INSERT INTO eo_ams_api_group (eo_ams_api_group.groupName,eo_ams_api_group.projectID) VALUES (?,?);', array(
                        $api_group['groupName'],
                        $project_id
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addGroup error");

                    $group_id = $db->getLastInsertID();
                    if ($api_group['apiList']) {
                        foreach ($api_group['apiList'] as $api) {
                           
                        	$db->prepareExecute('INSERT INTO eo_ams_api (eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiProtocol,eo_ams_api.apiSuccessMock,eo_ams_api.apiFailureMock,eo_ams_api.apiRequestType,eo_ams_api.apiStatus,eo_ams_api.groupID,eo_ams_api.projectID,eo_ams_api.starred,eo_ams_api.apiRequestParamType,eo_ams_api.apiRequestRaw,eo_ams_api.apiUpdateTime,eo_ams_api.updateUserID,eo_ams_api.mockResult,eo_ams_api.mockRule,eo_ams_api.mockConfig,eo_ams_api.apiFailureStatusCode,eo_ams_api.apiSuccessStatusCode,eo_ams_api.beforeInject,eo_ams_api.afterInject) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
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
                        			$api['baseInfo']['apiRequestParamType'],
                        			$api['baseInfo']['apiRequestRaw'],
                        			$api['baseInfo']['apiUpdateTime'],
                        			$user_id,
                        			$api['mockInfo']['result'] ? $api['mockInfo']['result'] : '',
                        			$api['mockInfo']['rule'] ? json_encode($api['mockInfo']['rule']) : '',
                        			json_encode($api['mockInfo']['mockConfig']),
                        			$api['baseInfo']['apiFailureStatusCode'] ? $api['baseInfo']['apiFailureStatusCode'] : '200',
                        			$api['baseInfo']['apiSuccessStatusCode'] ? $api['baseInfo']['apiSuccessStatusCode'] : '200',
                        			$api['baseInfo']['beforeInject'],
                        			$api['baseInfo']['afterInject']
                        	));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addApi error");

                            $api_id = $db->getLastInsertID();

                            
                            foreach ($api['headerInfo'] as $header) {
                                $db->prepareExecute('INSERT INTO eo_ams_api_header (eo_ams_api_header.headerName,eo_ams_api_header.headerValue,eo_ams_api_header.apiID) VALUES (?,?,?);', array(
                                    $header['headerName'],
                                    $header['headerValue'],
                                    $api_id
                                ));

                                if ($db->getAffectRow() < 1)
                                    throw new \PDOException("addHeader error");
                            }

                            
                            foreach ($api['requestInfo'] as $request) {
                                $db->prepareExecute('INSERT INTO eo_ams_api_request_param (eo_ams_api_request_param.apiID,eo_ams_api_request_param.paramName,eo_ams_api_request_param.paramKey,eo_ams_api_request_param.paramValue,eo_ams_api_request_param.paramLimit,eo_ams_api_request_param.paramNotNull,eo_ams_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
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
                                    $db->prepareExecute('INSERT INTO eo_ams_api_request_value (eo_ams_api_request_value.paramID,eo_ams_api_request_value.`value`,eo_ams_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                        $param_id,
                                        $value['value'],
                                        $value['valueDescription']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addApi error");
                                };
                            };

                            
                            foreach ($api['resultInfo'] as $result) {
                                $db->prepareExecute('INSERT INTO eo_ams_api_result_param (eo_ams_api_result_param.apiID,eo_ams_api_result_param.paramName,eo_ams_api_result_param.paramKey,eo_ams_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                                    $api_id,
                                    $result['paramName'],
                                    $result['paramKey'],
                                    $result['paramNotNull']
                                ));

                                if ($db->getAffectRow() < 1)
                                    throw new \PDOException("addResultParam error");

                                $param_id = $db->getLastInsertID();

                                foreach ($result['paramValueList'] as $value) {
                                    $db->prepareExecute('INSERT INTO eo_ams_api_result_value (eo_ams_api_result_value.paramID,eo_ams_api_result_value.`value`,eo_ams_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                        $param_id,
                                        $value['value'],
                                        $value['valueDescription']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addApi error");
                                };
                            };

                            
                            $db->prepareExecute("INSERT INTO eo_ams_api_cache (eo_ams_api_cache.projectID,eo_ams_api_cache.groupID,eo_ams_api_cache.apiID,eo_ams_api_cache.apiJson,eo_ams_api_cache.starred) VALUES (?,?,?,?,?);", array(
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
                    
                    if ($api_group['apiGroupChildList']) {
                        $group_parent_id = $group_id;
                        foreach ($api_group['apiGroupChildList'] as $api_group_child) {
                            $db->prepareExecute('INSERT INTO eo_ams_api_group (eo_ams_api_group.groupName,eo_ams_api_group.projectID,eo_ams_api_group.parentGroupID, eo_ams_api_group.isChild) VALUES (?,?,?,?);', array(
                                $api_group_child['groupName'],
                                $project_id,
                                $group_parent_id,
                                1
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addChildGroup error");

                            $group_id = $db->getLastInsertID();

                            
                            if (empty($api_group_child['apiList']))
                                continue;

                            foreach ($api_group_child['apiList'] as $api) {
                                
                            	$db->prepareExecute('INSERT INTO eo_ams_api (eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiProtocol,eo_ams_api.apiSuccessMock,eo_ams_api.apiFailureMock,eo_ams_api.apiRequestType,eo_ams_api.apiStatus,eo_ams_api.groupID,eo_ams_api.projectID,eo_ams_api.starred,eo_ams_api.apiRequestParamType,eo_ams_api.apiRequestRaw,eo_ams_api.apiUpdateTime,eo_ams_api.updateUserID,eo_ams_api.mockResult,eo_ams_api.mockRule,eo_ams_api.mockConfig,eo_ams_api.apiFailureStatusCode,eo_ams_api.apiSuccessStatusCode,eo_ams_api.beforeInject,eo_ams_api.afterInject) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
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
                            			$api['baseInfo']['apiRequestParamType'],
                            			$api['baseInfo']['apiRequestRaw'],
                            			$api['baseInfo']['apiUpdateTime'],
                            			$user_id,
                            			$api['mockInfo']['result'] ? $api['mockInfo']['result'] : '',
                            			$api['mockInfo']['rule'] ? json_encode($api['mockInfo']['rule']) : '',
                            			json_encode($api['mockInfo']['mockConfig']),
                            			$api['baseInfo']['apiFailureStatusCode'] ? $api['baseInfo']['apiFailureStatusCode'] : '200',
                            			$api['baseInfo']['apiSuccessStatusCode'] ? $api['baseInfo']['apiSuccessStatusCode'] : '200',
                            			$api['baseInfo']['beforeInject'],
                            			$api['baseInfo']['afterInject']
                            	));
                            	

                                if ($db->getAffectRow() < 1)
                                    throw new \PDOException("addChildApi error");

                                $api_id = $db->getLastInsertID();

                                
                                foreach ($api['headerInfo'] as $header) {
                                    $db->prepareExecute('INSERT INTO eo_ams_api_header (eo_ams_api_header.headerName,eo_ams_api_header.headerValue,eo_ams_api_header.apiID) VALUES (?,?,?);', array(
                                        $header['headerName'],
                                        $header['headerValue'],
                                        $api_id
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildHeader error");
                                }

                                
                                foreach ($api['requestInfo'] as $request) {
                                    $db->prepareExecute('INSERT INTO eo_ams_api_request_param (eo_ams_api_request_param.apiID,eo_ams_api_request_param.paramName,eo_ams_api_request_param.paramKey,eo_ams_api_request_param.paramValue,eo_ams_api_request_param.paramLimit,eo_ams_api_request_param.paramNotNull,eo_ams_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
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
                                            $db->prepareExecute('INSERT INTO eo_ams_api_request_value (eo_ams_api_request_value.paramID,eo_ams_api_request_value.`value`,eo_ams_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                                $param_id,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildApi error");
                                        };
                                    }
                                };

                               
                                foreach ($api['resultInfo'] as $result) {
                                    $db->prepareExecute('INSERT INTO eo_ams_api_result_param (eo_ams_api_result_param.apiID,eo_ams_api_result_param.paramName,eo_ams_api_result_param.paramKey,eo_ams_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
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
                                            $db->prepareExecute('INSERT INTO eo_ams_api_result_value (eo_ams_api_result_value.paramID,eo_ams_api_result_value.`value`,eo_ams_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                                $param_id,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildParamValue error");
                                        };
                                    }
                                };

                                
                                $db->prepareExecute("INSERT INTO eo_ams_api_cache (eo_ams_api_cache.projectID,eo_ams_api_cache.groupID,eo_ams_api_cache.apiID,eo_ams_api_cache.apiJson,eo_ams_api_cache.starred) VALUES (?,?,?,?,?);", array(
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
                            if ($api_group_child['apiGroupChildList']) {
                                $parent_id = $group_id;
                                foreach ($api_group_child['apiGroupChildList'] as $group_child) {
                                    $db->prepareExecute('INSERT INTO eo_ams_api_group (eo_ams_api_group.groupName,eo_ams_api_group.projectID,eo_ams_api_group.parentGroupID, eo_ams_api_group.isChild) VALUES (?,?,?,?);', array(
                                        $group_child['groupName'],
                                        $project_id,
                                        $parent_id,
                                        2
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildGroup error");

                                    $group_id = $db->getLastInsertID();

                                    
                                    if (empty($group_child['apiList']))
                                        continue;

                                    foreach ($group_child['apiList'] as $api) {
                                        
                                    	$db->prepareExecute('INSERT INTO eo_ams_api (eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiProtocol,eo_ams_api.apiSuccessMock,eo_ams_api.apiFailureMock,eo_ams_api.apiRequestType,eo_ams_api.apiStatus,eo_ams_api.groupID,eo_ams_api.projectID,eo_ams_api.starred,eo_ams_api.apiRequestParamType,eo_ams_api.apiRequestRaw,eo_ams_api.apiUpdateTime,eo_ams_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
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
                                    			$api['baseInfo']['apiRequestParamType'],
                                    			$api['baseInfo']['apiRequestRaw'],
                                    			$api['baseInfo']['apiUpdateTime'],
                                    			$user_id
                                    	));

                                        if ($db->getAffectRow() < 1)
                                            throw new \PDOException("addChildApi error");

                                        $api_id = $db->getLastInsertID();

                                        
                                        foreach ($api['headerInfo'] as $header) {
                                            $db->prepareExecute('INSERT INTO eo_ams_api_header (eo_ams_api_header.headerName,eo_ams_api_header.headerValue,eo_ams_api_header.apiID) VALUES (?,?,?);', array(
                                                $header['headerName'],
                                                $header['headerValue'],
                                                $api_id
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildHeader error");
                                        }

                                        
                                        foreach ($api['requestInfo'] as $request) {
                                            $db->prepareExecute('INSERT INTO eo_ams_api_request_param (eo_ams_api_request_param.apiID,eo_ams_api_request_param.paramName,eo_ams_api_request_param.paramKey,eo_ams_api_request_param.paramValue,eo_ams_api_request_param.paramLimit,eo_ams_api_request_param.paramNotNull,eo_ams_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
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
                                                    $db->prepareExecute('INSERT INTO eo_ams_api_request_value (eo_ams_api_request_value.paramID,eo_ams_api_request_value.`value`,eo_ams_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                                        $param_id,
                                                        $value['value'],
                                                        $value['valueDescription']
                                                    ));

                                                    if ($db->getAffectRow() < 1)
                                                        throw new \PDOException("addChildApi error");
                                                };
                                            }
                                        };

                                        
                                        foreach ($api['resultInfo'] as $result) {
                                            $db->prepareExecute('INSERT INTO eo_ams_api_result_param (eo_ams_api_result_param.apiID,eo_ams_api_result_param.paramName,eo_ams_api_result_param.paramKey,eo_ams_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
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
                                                    $db->prepareExecute('INSERT INTO eo_ams_api_result_value (eo_ams_api_result_value.paramID,eo_ams_api_result_value.`value`,eo_ams_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                                        $param_id,
                                                        $value['value'],
                                                        $value['valueDescription']
                                                    ));

                                                    if ($db->getAffectRow() < 1)
                                                        throw new \PDOException("addChildParamValue error");
                                                };
                                            }
                                        };

                                       
                                        $db->prepareExecute("INSERT INTO eo_ams_api_cache (eo_ams_api_cache.projectID,eo_ams_api_cache.groupID,eo_ams_api_cache.apiID,eo_ams_api_cache.apiJson,eo_ams_api_cache.starred) VALUES (?,?,?,?,?);", array(
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
                }
            }
            
            if (!empty($data['statusCodeGroupList'])) {
                
                foreach ($data['statusCodeGroupList'] as $status_codeGroup) {
                    
                    $db->prepareExecute('INSERT INTO eo_ams_project_status_code_group (eo_ams_project_status_code_group.projectID,eo_ams_project_status_code_group.groupName) VALUES (?,?);', array(
                        $project_id,
                        $status_codeGroup['groupName']
                    ));

                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("addChildstatusCodeGroup error");
                    }

                    $group_id = $db->getLastInsertID();

                    if (empty($status_codeGroup['statusCodeList']))
                        continue;

                    
                    foreach ($status_codeGroup['statusCodeList'] as $status_code) {
                        $db->prepareExecute('INSERT INTO eo_ams_project_status_code (eo_ams_project_status_code.groupID,eo_ams_project_status_code.code,eo_ams_project_status_code.codeDescription) VALUES (?,?,?);', array(
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
                           
                            $db->prepareExecute('INSERT INTO eo_ams_project_status_code_group (eo_ams_project_status_code_group.projectID,eo_ams_project_status_code_group.groupName,eo_ams_project_status_code_group.parentGroupID,eo_ams_project_status_code_group.isChild) VALUES (?,?,?,?);', array(
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

                            
                            foreach ($status_codeGroup_child['statusCodeList'] as $status_code) {
                                $db->prepareExecute('INSERT INTO eo_ams_project_status_code (eo_ams_project_status_code.groupID,eo_ams_project_status_code.code,eo_ams_project_status_code.codeDescription) VALUES (?,?,?);', array(
                                    $group_id,
                                    $status_code['code'],
                                    $status_code['codeDescription']
                                ));

                                if ($db->getAffectRow() < 1) {
                                    throw new \PDOException("addChildStatusCode error");
                                }
                            }

                            if ($status_codeGroup_child['statusCodeGroupChildList']) {
                                $parent_id = $group_id;
                                foreach ($status_codeGroup_child['statusCodeGroupChildList'] as $second_status_code_group_child) {
                                   
                                    $db->prepareExecute('INSERT INTO eo_ams_project_status_code_group (eo_ams_project_status_code_group.projectID,eo_ams_project_status_code_group.groupName,eo_ams_project_status_code_group.parentGroupID,eo_ams_project_status_code_group.isChild) VALUES (?,?,?,?);', array(
                                        $project_id,
                                        $second_status_code_group_child['groupName'],
                                        $parent_id,
                                        2
                                    ));
                                    if ($db->getAffectRow() < 1) {
                                        throw new \PDOException("addChildStatusCodeGroup error");
                                    }

                                    $group_id = $db->getLastInsertID();
                                    if (empty($second_status_code_group_child['statusCodeList']))
                                        continue;

                                   
                                    foreach ($second_status_code_group_child['statusCodeList'] as $status_code) {
                                        $db->prepareExecute('INSERT INTO eo_ams_project_status_code (eo_ams_project_status_code.groupID,eo_ams_project_status_code.code,eo_ams_project_status_code.codeDescription) VALUES (?,?,?);', array(
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
                }
            }
            
            if (!empty($data['pageGroupList'])) {
                
                foreach ($data['pageGroupList'] as $pageGroup) {
                    
                    $db->prepareExecute('INSERT INTO eo_ams_project_document_group(eo_ams_project_document_group.projectID,eo_ams_project_document_group.groupName) VALUES (?,?);', array(
                        $project_id,
                        $pageGroup['groupName']
                    ));

                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("add pageGroup error");
                    }

                    $group_id = $db->getLastInsertID();
                    
                    foreach ($pageGroup['pageList'] as $page) {
                        $db->prepareExecute('INSERT INTO eo_ams_project_document(eo_ams_project_document.groupID,eo_ams_project_document.projectID,eo_ams_project_document.contentType,eo_ams_project_document.contentRaw,eo_ams_project_document.content,eo_ams_project_document.title,eo_ams_project_document.updateTime,eo_ams_project_document.userID) VALUES (?,?,?,?,?,?,?,?);', array(
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
                           
                            $db->prepareExecute('INSERT INTO eo_ams_project_document_group(eo_ams_project_document_group.projectID,eo_ams_project_document_group.groupName,eo_ams_project_document_group.parentGroupID,eo_ams_project_document_group.isChild) VALUES (?,?,?,?);', array(
                                $project_id,
                                $page_group_child['groupName'],
                                $group_id_parent,
                                1,
                            ));
                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("add pageGroup error");
                            }

                            $group_id = $db->getLastInsertID();
                            
                            foreach ($page_group_child['pageList'] as $page) {
                                $db->prepareExecute('INSERT INTO eo_ams_project_document(eo_ams_project_document.groupID,eo_ams_project_document.projectID,eo_ams_project_document.contentType,eo_ams_project_document.contentRaw,eo_ams_project_document.content,eo_ams_project_document.title,eo_ams_project_document.updateTime,eo_ams_project_document.userID) VALUES (?,?,?,?,?,?,?,?);', array(
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
                            if ($page_group_child['pageGroupChildList']) {
                                $parent_id = $group_id;
                                foreach ($page_group_child['pageGroupChildList'] as $second_page_group_child) {
                                    
                                    $db->prepareExecute('INSERT INTO eo_ams_project_document_group(eo_ams_project_document_group.projectID,eo_ams_project_document_group.groupName,eo_ams_project_document_group.parentGroupID,eo_ams_project_document_group.isChild) VALUES (?,?,?,?);', array(
                                        $project_id,
                                        $second_page_group_child['groupName'],
                                        $parent_id,
                                        2
                                    ));
                                    if ($db->getAffectRow() < 1) {
                                        throw new \PDOException("add pageGroup error");
                                    }

                                    $group_id = $db->getLastInsertID();
                                   
                                    foreach ($second_page_group_child['pageList'] as $page) {
                                        $db->prepareExecute('INSERT INTO eo_ams_project_document(eo_ams_project_document.groupID,eo_ams_project_document.projectID,eo_ams_project_document.contentType,eo_ams_project_document.contentRaw,eo_ams_project_document.content,eo_ams_project_document.title,eo_ams_project_document.updateTime,eo_ams_project_document.userID) VALUES (?,?,?,?,?,?,?,?);', array(
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
                }
            }
            
            if (!empty($data['env'])) {
                foreach ($data['env'] as $env) {
                    $db->prepareExecute("INSERT INTO eo_ams_api_env (eo_ams_api_env.envID,eo_ams_api_env.envName,eo_ams_api_env.projectID,eo_ams_api_env.frontURI,eo_ams_api_env.envHeader,eo_ams_api_env.globalVariable,eo_ams_api_env.additionalVariable) VALUES (?,?,?,?,?,?,?);", array(
                    	$env_id,
                        $env['envName'],
                        $project_id,
                    		$env['frontURI'],
                    		$env['envHeader'],
                    		$env['globalVariable'],
                    		$env['globalVariable']
                    ));
                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("add env error");                   
                }
            }
           
            if (!empty($data['caseGroupList'])) {
                foreach ($data['caseGroupList'] as $case_group) {
                    
                    $db->prepareExecute('INSERT INTO eo_ams_project_test_case_group (eo_ams_project_test_case_group.projectID,eo_ams_project_test_case_group.groupName) VALUES (?,?);', array(
                        $project_id,
                        $case_group['groupName']
                    ));
                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addCaseGroup error");
                    $group_id = $db->getLastInsertID();
                    if ($case_group['caseList']) {
                      
                        foreach ($case_group['caseList'] as $case) {
                            $db->prepareExecute('INSERT INTO eo_ams_project_test_case(eo_ams_project_test_case.projectID,eo_ams_project_test_case.userID,eo_ams_project_test_case.caseName,eo_ams_project_test_case.caseDesc,eo_ams_project_test_case.createTime,eo_ams_project_test_case.updateTime,eo_ams_project_test_case.caseType,eo_ams_project_test_case.groupID,eo_ams_project_test_case.caseCode)VALUES(?,?,?,?,?,?,?,?,?);', array(
                                $project_id,
                                $user_id,
                                $case['caseName'],
                                $case['caseDesc'],
                                date('Y-m-d H:i:s', time()),
                                date('Y-m-d H:i:s', time()),
                                $case['caseType'],
                                $group_id,
                                $case['caseCode']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addCase error");
                            $case_id = $db->getLastInsertID();
                            if ($case['caseSingleList']) {
                                foreach ($case['caseSingleList'] as $single_case) {
                                    $match = array();
                                    
                                    if (preg_match_all('#<response\[(\d+)\]#', $single_case['caseData'], $match) > 0) {
                                     
                                        foreach ($match[1] as $response_id) {
                                            for ($i = 0; $i < count($case['caseSingleList']); $i++) {
                                                if ($case['caseSingleList'][$i]['connID'] == $response_id) {
                                                    $result = $db->prepareExecute("SELECT connID FROM eo_ams_project_test_case_single WHERE apiName = ? AND apiURI = ? AND caseID = ?;", array(
                                                        $case['caseSingleList'][$i]['apiName'],
                                                        $case['caseSingleList'][$i]['apiURI'],
                                                        $case_id
                                                    ));
                                                    $single_case['caseData'] = str_replace("<response[" . $response_id, "<response[" . $result['connID'], $single_case['caseData']);
                                                }
                                            }
                                        }
                                    }

                                    $db->prepareExecute('INSERT INTO eo_ams_project_test_case_single(eo_ams_project_test_case_single.caseID,eo_ams_project_test_case_single.caseData,eo_ams_project_test_case_single.caseCode,eo_ams_project_test_case_single.statusCode,eo_ams_project_test_case_single.matchType,eo_ams_project_test_case_single.matchRule, eo_ams_project_test_case_single.apiName, eo_ams_project_test_case_single.apiURI, eo_ams_project_test_case_single.apiRequestType,eo_ams_project_test_case_single.orderNumber)VALUES(?,?,?,?,?,?,?,?,?,?);', array(
                                        $case_id,
                                        $single_case['caseData'],
                                        $single_case['caseCode'],
                                        $single_case['statusCode'],
                                        $single_case['matchType'],
                                        $single_case['matchRule'],
                                        $single_case['apiName'],
                                        $single_case['apiURI'],
                                        $single_case['apiRequestType'],
                                        $single_case['orderNumber']
                                    ));
                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException('addSingleCase error');
                                }
                            }
                        }
                    }
                    if ($case_group['caseChildGroupList']) {
                        $group_id_parent = $group_id;
                        foreach ($case_group['caseChildGroupList'] as $child_group) {
                          
                            $db->prepareExecute('INSERT INTO eo_ams_project_test_case_group (eo_ams_project_test_case_group.projectID,eo_ams_project_test_case_group.groupName,eo_ams_project_test_case_group.parentGroupID,eo_ams_project_test_case_group.isChild) VALUES (?,?,?,?);', array(
                                $project_id,
                                $child_group['groupName'],
                                $group_id_parent,
                                1
                            ));
                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("addCaseGroup error");
                            }
                            $group_id = $db->getLastInsertID();
                            if ($child_group['caseList']) {
                                
                                foreach ($child_group['caseList'] as $case) {
                                    $db->prepareExecute('INSERT INTO eo_ams_project_test_case(eo_ams_project_test_case.projectID,eo_ams_project_test_case.userID,eo_ams_project_test_case.caseName,eo_ams_project_test_case.caseDesc,eo_ams_project_test_case.createTime,eo_ams_project_test_case.updateTime,eo_ams_project_test_case.caseType,eo_ams_project_test_case.groupID,eo_ams_project_test_case.caseCode)VALUES(?,?,?,?,?,?,?,?,?);', array(
                                        $project_id,
                                        $user_id,
                                        $case['caseName'],
                                        $case['caseDesc'],
                                        date('Y-m-d H:i:s', time()),
                                        date('Y-m-d H:i:s', time()),
                                        $case['caseType'],
                                        $group_id,
                                        $case['caseCode']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addCase error");
                                    $case_id = $db->getLastInsertID();
                                    if ($case['caseSingleList']) {
                                        foreach ($case['caseSingleList'] as $single_case) {
                                            $match = array();
                                           
                                            if (preg_match_all('#<response\[(\d+)\]#', $single_case['caseData'], $match) > 0) {
                                               
                                                foreach ($match[1] as $response_id) {
                                                    for ($i = 0; $i < count($case['caseSingleList']); $i++) {
                                                        if ($case['caseSingleList'][$i]['connID'] == $response_id) {
                                                            $result = $db->prepareExecute("SELECT connID FROM eo_ams_project_test_case_single WHERE apiName = ? AND apiURI = ? AND caseID = ?;", array(
                                                                $case['caseSingleList'][$i]['apiName'],
                                                                $case['caseSingleList'][$i]['apiURI'],
                                                                $case_id
                                                            ));
                                                            $single_case['caseData'] = str_replace("<response[" . $response_id, "<response[" . $result['connID'], $single_case['caseData']);
                                                        }
                                                    }
                                                }
                                            }
                                            $db->prepareExecute('INSERT INTO eo_ams_project_test_case_single(eo_ams_project_test_case_single.caseID,eo_ams_project_test_case_single.caseData,eo_ams_project_test_case_single.caseCode,eo_ams_project_test_case_single.statusCode,eo_ams_project_test_case_single.matchType,eo_ams_project_test_case_single.matchRule, eo_ams_project_test_case_single.apiName, eo_ams_project_test_case_single.apiURI, eo_ams_project_test_case_single.apiRequestType,eo_ams_project_test_case_single.orderNumber)VALUES(?,?,?,?,?,?,?,?,?,?);', array(
                                                $case_id,
                                                $single_case['caseData'],
                                                $single_case['caseCode'],
                                                $single_case['statusCode'],
                                                $single_case['matchType'],
                                                $single_case['matchRule'],
                                                $single_case['apiName'],
                                                $single_case['apiURI'],
                                                $single_case['apiRequestType'],
                                                $single_case['orderNumber']
                                            ));
                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException('addSingleCase error');
                                        }
                                    }
                                }
                            }
                            if ($child_group['caseChildGroupList']) {
                                $parent_id = $group_id;
                                foreach ($child_group['caseChildGroupList'] as $second_child_group) {
                                   
                                    $db->prepareExecute('INSERT INTO eo_ams_project_test_case_group (eo_ams_project_test_case_group.projectID,eo_ams_project_test_case_group.groupName,eo_ams_project_test_case_group.parentGroupID,eo_ams_project_test_case_group.isChild) VALUES (?,?,?,?);', array(
                                        $project_id,
                                        $second_child_group['groupName'],
                                        $parent_id,
                                        2
                                    ));
                                    if ($db->getAffectRow() < 1) {
                                        throw new \PDOException("addCaseGroup error");
                                        var_dump($project_id, $second_child_group['groupName'], $parent_id);
                                    }
                                    $group_id = $db->getLastInsertID();
                                    if ($second_child_group['caseList']) {
                                        
                                        foreach ($second_child_group['caseList'] as $case) {
                                            $db->prepareExecute('INSERT INTO eo_ams_project_test_case(eo_ams_project_test_case.projectID,eo_ams_project_test_case.userID,eo_ams_project_test_case.caseName,eo_ams_project_test_case.caseDesc,eo_ams_project_test_case.createTime,eo_ams_project_test_case.updateTime,eo_ams_project_test_case.caseType,eo_ams_project_test_case.groupID,eo_ams_project_test_case.caseCode)VALUES(?,?,?,?,?,?,?,?,?);', array(
                                                $project_id,
                                                $user_id,
                                                $case['caseName'],
                                                $case['caseDesc'],
                                                date('Y-m-d H:i:s', time()),
                                                date('Y-m-d H:i:s', time()),
                                                $case['caseType'],
                                                $group_id,
                                                $case['caseCode']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addCase error");
                                            $case_id = $db->getLastInsertID();
                                            if ($case['caseSingleList']) {
                                                foreach ($case['caseSingleList'] as $single_case) {
                                                    $match = array();
                                                    
                                                    if (preg_match_all('#<response\[(\d+)\]#', $single_case['caseData'], $match) > 0) {
                                                       
                                                        foreach ($match[1] as $response_id) {
                                                            for ($i = 0; $i < count($case['caseSingleList']); $i++) {
                                                                if ($case['caseSingleList'][$i]['connID'] == $response_id) {
                                                                    $result = $db->prepareExecute("SELECT connID FROM eo_ams_project_test_case_single WHERE apiName = ? AND apiURI = ? AND caseID = ?;", array(
                                                                        $case['caseSingleList'][$i]['apiName'],
                                                                        $case['caseSingleList'][$i]['apiURI'],
                                                                        $case_id
                                                                    ));
                                                                    $single_case['caseData'] = str_replace("<response[" . $response_id, "<response[" . $result['connID'], $single_case['caseData']);
                                                                }
                                                            }
                                                        }
                                                    }
                                                    $db->prepareExecute('INSERT INTO eo_ams_project_test_case_single(eo_ams_project_test_case_single.caseID,eo_ams_project_test_case_single.caseData,eo_ams_project_test_case_single.caseCode,eo_ams_project_test_case_single.statusCode,eo_ams_project_test_case_single.matchType,eo_ams_project_test_case_single.matchRule, eo_ams_project_test_case_single.apiName, eo_ams_project_test_case_single.apiURI, eo_ams_project_test_case_single.apiRequestType,eo_ams_project_test_case_single.orderNumber)VALUES(?,?,?,?,?,?,?,?,?,?);', array(
                                                        $case_id,
                                                        $single_case['caseData'],
                                                        $single_case['caseCode'],
                                                        $single_case['statusCode'],
                                                        $single_case['matchType'],
                                                        $single_case['matchRule'],
                                                        $single_case['apiName'],
                                                        $single_case['apiURI'],
                                                        $single_case['apiRequestType'],
                                                        $single_case['orderNumber']
                                                    ));
                                                    if ($db->getAffectRow() < 1)
                                                        throw new \PDOException('addSingleCase error');
                                                }
                                            }
                                        }
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

    /**
     * import others
     * @param $projectInfo array 
     * @param $groupInfoList array 
     * @param $userID int 
     * @return bool
     */
    public function importOther(&$projectInfo, &$groupInfoList, &$userID)
    {
        $db = getDatabase();
        try {
            
            $db->beginTransaction();
            
            $db->prepareExecute('INSERT INTO eo_ams_project(eo_ams_project.projectName,eo_ams_project.projectType,eo_ams_project.projectVersion,eo_ams_project.projectUpdateTime) VALUES (?,?,?,?);', array(
                $projectInfo['projectName'],
                $projectInfo['projectType'],
                $projectInfo['projectVersion'],
                date('Y-m-d H:i:s', time())
            ));
            if ($db->getAffectRow() < 1)
                throw new \PDOException("addProject error");

            $projectID = $db->getLastInsertID();

            
            $db->prepareExecute('INSERT INTO eo_ams_conn_project (eo_ams_conn_project.projectID,eo_ams_conn_project.userID,eo_ams_conn_project.userType) VALUES (?,?,0);', array(
                $projectID,
                $userID
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("addConnProject error");

            if (is_array($groupInfoList)) {
                foreach ($groupInfoList as $groupInfo) {
                    $db->prepareExecute('INSERT INTO eo_ams_api_group (eo_ams_api_group.groupName,eo_ams_api_group.projectID) VALUES (?,?);', array(
                        $groupInfo['groupName'],
                        $projectID
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addGroup error");

                    $groupID = $db->getLastInsertID();
                    if (is_array($groupInfo['apiList'])) {
                        foreach ($groupInfo['apiList'] as $api) {
                            
                            $db->prepareExecute('INSERT INTO eo_ams_api (eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiProtocol,eo_ams_api.apiSuccessMock,eo_ams_api.apiFailureMock,eo_ams_api.apiRequestType,eo_ams_api.apiStatus,eo_ams_api.groupID,eo_ams_api.projectID,eo_ams_api.starred,eo_ams_api.apiRequestParamType,eo_ams_api.apiRequestRaw,eo_ams_api.apiUpdateTime,eo_ams_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
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
                                $api['baseInfo']['apiRequestParamType'],
                                $api['baseInfo']['apiRequestRaw'],
                                $api['baseInfo']['apiUpdateTime'],
                                $userID
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addApi error");

                            $apiID = $db->getLastInsertID();

                            
                            if (is_array($api['headerInfo'])) {
                                foreach ($api['headerInfo'] as $param) {
                                    $db->prepareExecute('INSERT INTO eo_ams_api_header (eo_ams_api_header.headerName,eo_ams_api_header.headerValue,eo_ams_api_header.apiID) VALUES (?,?,?);', array(
                                        $param['headerName'],
                                        $param['headerValue'],
                                        $apiID
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addHeader error");
                                }
                            }

                           
                            if (is_array($api['requestInfo'])) {
                                foreach ($api['requestInfo'] as $param) {
                                    $db->prepareExecute('INSERT INTO eo_ams_api_request_param (eo_ams_api_request_param.apiID,eo_ams_api_request_param.paramName,eo_ams_api_request_param.paramKey,eo_ams_api_request_param.paramValue,eo_ams_api_request_param.paramLimit,eo_ams_api_request_param.paramNotNull,eo_ams_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
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
                                            $db->prepareExecute('INSERT INTO eo_ams_api_request_value (eo_ams_api_request_value.paramID,eo_ams_api_request_value.`value`,eo_ams_api_request_value.valueDescription) VALUES (?,?,?);;', array(
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

                            
                            if (is_array($api['resultInfo'])) {
                                foreach ($api['resultInfo'] as $param) {
                                    $db->prepareExecute('INSERT INTO eo_ams_api_result_param (eo_ams_api_result_param.apiID,eo_ams_api_result_param.paramName,eo_ams_api_result_param.paramKey,eo_ams_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
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
                                            $db->prepareExecute('INSERT INTO eo_ams_api_result_value (eo_ams_api_result_value.paramID,eo_ams_api_result_value.`value`,eo_ams_api_result_value.valueDescription) VALUES (?,?,?);;', array(
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

                            
                            $db->prepareExecute("INSERT INTO eo_ams_api_cache (eo_ams_api_cache.projectID,eo_ams_api_cache.groupID,eo_ams_api_cache.apiID,eo_ams_api_cache.apiJson,eo_ams_api_cache.starred) VALUES (?,?,?,?,?);", array(
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
                            $db->prepareExecute('INSERT INTO eo_ams_api_group (eo_ams_api_group.groupName,eo_ams_api_group.projectID,eo_ams_api_group.parentGroupID,eo_ams_api_group.isChild) VALUES (?,?,?,?);', array(
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
                                    
                                    $db->prepareExecute('INSERT INTO eo_ams_api (eo_ams_api.apiName,eo_ams_api.apiURI,eo_ams_api.apiProtocol,eo_ams_api.apiSuccessMock,eo_ams_api.apiFailureMock,eo_ams_api.apiRequestType,eo_ams_api.apiStatus,eo_ams_api.groupID,eo_ams_api.projectID,eo_ams_api.starred,eo_ams_api.apiRequestParamType,eo_ams_api.apiRequestRaw,eo_ams_api.apiUpdateTime,eo_ams_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
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
                                        $api['baseInfo']['apiRequestParamType'],
                                        $api['baseInfo']['apiRequestRaw'],
                                        $api['baseInfo']['apiUpdateTime'],
                                        $userID
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildGroupApi error");

                                    $apiID = $db->getLastInsertID();

                                    
                                    if (is_array($api['headerInfo'])) {
                                        foreach ($api['headerInfo'] as $param) {
                                            $db->prepareExecute('INSERT INTO eo_ams_api_header (eo_ams_api_header.headerName,eo_ams_api_header.headerValue,eo_ams_api_header.apiID) VALUES (?,?,?);', array(
                                                $param['headerName'],
                                                $param['headerValue'],
                                                $apiID
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildGroupHeader error");
                                        }
                                    }

                                    
                                    if (is_array($api['requestInfo'])) {
                                        foreach ($api['requestInfo'] as $param) {
                                            $db->prepareExecute('INSERT INTO eo_ams_api_request_param (eo_ams_api_request_param.apiID,eo_ams_api_request_param.paramName,eo_ams_api_request_param.paramKey,eo_ams_api_request_param.paramValue,eo_ams_api_request_param.paramLimit,eo_ams_api_request_param.paramNotNull,eo_ams_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
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
                                                    $db->prepareExecute('INSERT INTO eo_ams_api_request_value (eo_ams_api_request_value.paramID,eo_ams_api_request_value.`value`,eo_ams_api_request_value.valueDescription) VALUES (?,?,?);;', array(
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

                                   
                                    if (is_array($api['resultInfo'])) {
                                        foreach ($api['resultInfo'] as $param) {
                                            $db->prepareExecute('INSERT INTO eo_ams_api_result_param (eo_ams_api_result_param.apiID,eo_ams_api_result_param.paramName,eo_ams_api_result_param.paramKey,eo_ams_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
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
                                                    $db->prepareExecute('INSERT INTO eo_ams_api_result_value (eo_ams_api_result_value.paramID,eo_ams_api_result_value.`value`,eo_ams_api_result_value.valueDescription) VALUES (?,?,?);;', array(
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

                                    
                                    $db->prepareExecute("INSERT INTO eo_ams_api_cache (eo_ams_api_cache.projectID,eo_ams_api_cache.groupID,eo_ams_api_cache.apiID,eo_ams_api_cache.apiJson,eo_ams_api_cache.starred) VALUES (?,?,?,?,?);", array(
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
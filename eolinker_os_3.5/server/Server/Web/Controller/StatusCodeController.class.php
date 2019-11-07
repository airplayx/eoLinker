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
class StatusCodeController
{
    // 返回json类型
    private $returnJson = array('type' => 'status_code');

    /**
     * 检查登录状态
     */
    public function __construct()
    {
        // 身份验证
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * 添加状态码
     */
    public function addCode()
    {
        $codeLen = mb_strlen(quickInput('code'), 'utf8');
        $codeDescLen = mb_strlen(quickInput('codeDesc'), 'utf8');
        $groupID = securelyInput('groupID');
        $module = new StatusCodeGroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $code = securelyInput('code');
        $codeDesc = securelyInput('codeDesc');

        if (!preg_match('/^[0-9]{1,11}$/', $groupID)) {
            //分组ID格式不合法
            $this->returnJson['statusCode'] = '190002';
        } elseif (!($codeLen >= 1 && $codeLen <= 255)) {
            //状态码格式不合法
            $this->returnJson['statusCode'] = '190008';
        } elseif (!($codeDescLen >= 1 && $codeDescLen <= 255)) {
            //状态码描述格式不合法
            $this->returnJson['statusCode'] = '190003';
        } else {
            $service = new StatusCodeModule;
            $result = $service->addCode($groupID, $codeDesc, $code);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['codeID'] = $result;
            } else {
                $this->returnJson['statusCode'] = '190004';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除状态码
     */
    public function deleteCode()
    {
        // 状态码ID数组
        $ids = quickInput('codeID');
        $arr = json_decode($ids);
        $arr = preg_grep('/^[0-9]{1,11}$/', $arr);
        if (empty ($arr)) {
            // 状态码ID格式不合法
            $this->returnJson ['statusCode'] = '190003';
        } else {
            $code_ids = implode(',', $arr);
            $service = new StatusCodeModule();
            $result = $service->deleteCodes($code_ids);

            if ($result) {
                //成功
                $this->returnJson ['statusCode'] = '000000';
            } else {
                //失败
                $this->returnJson ['statusCode'] = '190000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取状态码列表
     */
    public function getCodeList()
    {
        $groupID = securelyInput('groupID');

        if (!preg_match('/^[0-9]{1,11}$/', $groupID)) {
            //分组ID格式不合法
            $this->returnJson['statusCode'] = '190002';
        } else {
            $service = new StatusCodeModule;
            $result = $service->getCodeList($groupID);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['codeList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '190001';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取所有状态码列表
     */
    public function getAllCodeList()
    {
        $projectID = securelyInput('projectID');

        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            //项目ID格式不合法
            $this->returnJson['statusCode'] = '190007';
        } else {
            $service = new StatusCodeModule;
            $result = $service->getAllCodeList($projectID);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['codeList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '190001';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改状态码
     */
    public function editCode()
    {
        $codeLen = mb_strlen(quickInput('code'), 'utf8');
        $codeDescLen = mb_strlen(quickInput('codeDesc'), 'utf8');
        $codeID = securelyInput('codeID');
        $module = new StatusCodeModule();
        $userType = $module->getUserType($codeID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $groupID = securelyInput('groupID');
        $code = securelyInput('code');
        $codeDesc = securelyInput('codeDesc');

        if (!preg_match('/^[0-9]{1,11}$/', $codeID)) {
            //状态码ID格式非法
            $this->returnJson['statusCode'] = '190005';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $groupID)) {
            //分组ID格式非法
            $this->returnJson['statusCode'] = '190002';
        } elseif (!($codeLen >= 1 && $codeLen <= 255)) {
            //状态码格式非法
            $this->returnJson['statusCode'] = '190008';
        } elseif (!($codeDescLen >= 1 && $codeDescLen <= 255)) {
            //状态码描述格式非法
            $this->returnJson['statusCode'] = '190003';
        } else {
            $service = new StatusCodeModule;
            $result = $service->editCode($groupID, $codeID, $code, $codeDesc);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '190009';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 搜索状态码
     */
    public function searchStatusCode()
    {
        $projectID = securelyInput('projectID');
        $tipsLen = mb_strlen(quickInput('tips'), 'utf8');
        $tips = securelyInput('tips');

        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            //项目ID格式不合法
            $this->returnJson['statusCode'] = '190007';
        } elseif (!($tipsLen >= 1 && $tipsLen <= 255)) {
            $this->returnJson['statusCode'] = '190008';
        } else {
            $service = new StatusCodeModule;
            $result = $service->searchStatusCode($projectID, $tips);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['codeList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '190001';
            }
        }
        exitOutput($this->returnJson);
    }

    /*
     * 获取状态码数量
     */
    public function getStatusCodeNum()
    {
        $projectID = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            //项目ID格式不合法
            $this->returnJson['statusCode'] = '190007';
        } else {
            $service = new StatusCodeModule;
            $result = $service->getStatusCodeNum($projectID);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['num'] = $result['num'];
            } else
                $this->returnJson['statusCode'] = '190010';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 通过Excel批量添加状态码
     */
    public function addStatusCodeByExcel()
    {
        quickRequire(PATH_EXTEND . 'excel/PHPExcel.php');
        quickRequire(PATH_EXTEND . 'excel/PHPExcel/IOFactory.php');
        $filename = $_FILES['excel']['tmp_name'];
        $group_id = securelyInput('groupID');
        if (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            //分组ID格式非法
            $this->returnJson['statusCode'] = '190002';
        } else {
            //检查权限
            $service = new StatusCodeGroupModule();
            $user_type = $service->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $status_code_list = array();
                try {
                    $PHPExcel = \PHPExcel_IOFactory::load($filename);
                    $currentSheet = $PHPExcel->getSheet(0); // 读取第一个工作簿
                    $all_row = $currentSheet->getHighestRow(); // 所有行数
                    for ($i = 3; $i <= $all_row; $i++) {
                        $code = $currentSheet->getCell('A' . $i)->getValue();
                        $code_desc = $currentSheet->getCell('B' . $i)->getValue();
                        if (empty($code)) {
                            continue;
                        }
                        $status_code_list[] = array(
                            'code' => $code,
                            'codeDesc' => $code_desc ? $code_desc : ''
                        );
                    }
                    if ($status_code_list) {
                        $service = new StatusCodeModule();
                        $result = $service->addStatusCodeByExcel($group_id, $status_code_list);
                        if ($result) {
                            $this->returnJson['statusCode'] = '000000';
                        } else {
                            $this->returnJson['statusCode'] = '190000';
                        }
                    } else {
                        //内容为空
                        $this->returnJson['statusCode'] = '190006';
                    }
                } catch (\Exception $e) {
                    //读取Excel文件失败
                    $this->returnJson['statusCode'] = '190005';
                }
            }
        }

        exitOutput($this->returnJson);
    }
}

?>
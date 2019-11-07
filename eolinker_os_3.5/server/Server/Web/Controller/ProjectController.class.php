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
class ProjectController
{
    // 返回json类型
    private $returnJson = array('type' => 'project');

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
     * 创建项目
     */
    public function addProject()
    {
        $nameLen = mb_strlen(quickInput('projectName'), 'utf8');
        $projectName = securelyInput('projectName');
        $projectType = securelyInput('projectType');
        $projectVersion = quickInput('projectVersion');
        $version_len = mb_strlen(quickInput('projectVersion'));

        // 验证项目名和项目类型格式
        if (!($nameLen >= 1 && $nameLen <= 32 && preg_match('/^[0-3]{1}$/', $projectType))) {
            // 项目名或项目类型不合法
            $this->returnJson['statusCode'] = '140002';
        } elseif ($version_len < 1 || $version_len > 10) {
            // 项目版本不合法
            $this->returnJson['statusCode'] = '140017';
        } else {
            // 项目名和项目类型合法
            $service = new ProjectModule();
            $result = $service->addProject($projectName, $projectType, $projectVersion);
            // 判断新建项目是否成功
            if ($result) {
                // 新建项目成功
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['projectInfo'] = $result;
            } else
                // 新建项目失败
                $this->returnJson['statusCode'] = '140001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除项目
     */
    public function deleteProject()
    {
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType != 0) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }

        // 判断项目ID是否合法
        if (preg_match('/^[0-9]{1,11}$/', $projectID)) {
            // 项目ID合法
            $service = new ProjectModule();
            $result = $service->deleteProject($projectID);
            // 判断删除项目是否成功
            if ($result)
                // 删除项目成功
                $this->returnJson['statusCode'] = '000000';
            else
                // 删除项目失败
                $this->returnJson['statusCode'] = '140003';
        } else {
            // 项目ID不合法
            $this->returnJson['statusCode'] = '140004';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取项目列表
     */
    public function getProjectList()
    {
        $nameLen = mb_strlen(quickInput('projectName'), 'utf8');
        $projectType = securelyInput('projectType');
        //$projectName = securelyInput('projectName');
        if (!preg_match('/^[0-3]|[-1]{1}$/', $projectType) || ($nameLen != 0 && $nameLen < 1 || $nameLen > 30)) {
            // 项目类型或项目名称不合法
            $this->returnJson['statusCode'] = '140002';
            exitOutput($this->returnJson);
        } else {
            $service = new ProjectModule();
            $result = $service->getProjectList($projectType);

            if ($result) {
                // 获取项目列表成功
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['projectList'] = $result;
            } else {
                // 项目列表为空
                $this->returnJson['statusCode'] = '140005';
            }
        }

        exitOutput($this->returnJson);
    }

    /**
     * 更改项目
     */
    public function editProject()
    {
        $nameLen = mb_strlen(quickInput('projectName'), 'utf8');
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $projectType = securelyInput('projectType');
        $projectName = securelyInput('projectName');
        $projectVersion = quickInput('projectVersion');
        $version_len = mb_strlen(quickInput('projectVersion'));

        // 判断项目参数格式是否合法
        if (!(preg_match('/^[0-9]{1,11}$/', $projectID) && $nameLen >= 1 && $nameLen <= 32 && preg_match('/^[0-3]{1}$/', $projectType))) {
            // 项目参数格式不合法
            $this->returnJson['statusCode'] = '140007';
        } elseif ($version_len < 1 || $version_len > 10) {
            // 项目版本不合法
            $this->returnJson['statusCode'] = '140017';
        } else {
            // 项目参数格式合法
            $service = new ProjectModule();
            $result = $service->editProject($projectID, $projectName, $projectType, $projectVersion);

            // 判断修改项目是否成功
            if ($result)
                // 项目修改成功
                $this->returnJson['statusCode'] = '000000';
            else
                // 项目修改失败
                $this->returnJson['statusCode'] = '140006';
        }

        exitOutput($this->returnJson);
    }

    /**
     * 获取项目信息
     */
    public function getProject()
    {
        $projectID = securelyInput('projectID');
        if (preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $service = new ProjectModule;
            $result = $service->getProject($projectID);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson = array_merge($this->returnJson, $result);
            } else {
                $this->returnJson['statusCode'] = '140005';
            }
        } else {
            $this->returnJson['statusCode'] = '140007';
        }
        exitOutput($this->returnJson);
    }

//    /**
//     * 获取项目环境列表
//     */
//    public function getEnvList()
//    {
//        $projectID = securelyInput('projectID');
//
//        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
//            //项目ID不合法
//            $this->returnJson['statusCode'] = '140004';
//        } else {
//            $service = new ProjectModule;
//            $result = $service->getEnvList($projectID);
//            if ($result) {
//                $this->returnJson['statusCode'] = '000000';
//                $this->returnJson['envList'] = $result;
//            } else {
//                //环境列表为空
//                $this->returnJson['statusCode'] = '140018';
//            }
//        }
//        exitOutput($this->returnJson);
//    }
//
//    /**
//     * 添加项目环境
//     */
//    public function addEnv()
//    {
//        $projectID = securelyInput('projectID');
//        $envName = securelyInput('envName');
//        $envURI = securelyInput('envURI');
//
//        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
//            //项目ID不合法
//            $this->returnJson['statusCode'] = '140004';
//        } else {
//            $service = new ProjectModule;
//            $result = $service->addEnv($projectID, $envName, $envURI);
//            if ($result) {
//                $this->returnJson['statusCode'] = '000000';
//                $this->returnJson['envID'] = $result;
//            } else {
//                $this->returnJson['statusCode'] = '140019';
//            }
//        }
//        exitOutput($this->returnJson);
//    }
//
//    /**
//     * 删除项目环境
//     */
//    public function deleteEnv()
//    {
//        $projectID = securelyInput('projectID');
//        $envID = securelyInput('envID');
//
//        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
//            //项目ID不合法
//            $this->returnJson['statusCode'] = '140004';
//        } elseif (!preg_match('/^[0-9]{1,11}$/', $envID)) {
//            //环境ID不合法
//            $this->returnJson['statusCode'] = '140022';
//        } else {
//            $service = new ProjectModule;
//            if ($service->deleteEnv($projectID, $envID)) {
//                $this->returnJson['statusCode'] = '000000';
//            } else {
//                //删除环境失败，projectID与envID不匹配
//                $this->returnJson['statusCode'] = '140020';
//            }
//        }
//        exitOutput($this->returnJson);
//    }
//
//    /**
//     * 修改项目环境
//     */
//    public function editEnv()
//    {
//        $projectID = securelyInput('projectID');
//        $envID = securelyInput('envID');
//        $envName = securelyInput('envName');
//        $envURI = securelyInput('envURI');
//
//        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
//            //项目ID不合法
//            $this->returnJson['statusCode'] = '140004';
//        } elseif (!preg_match('/^[0-9]{1,11}$/', $envID)) {
//            //环境ID不合法
//            $this->returnJson['statusCode'] = '140022';
//        } else {
//            $service = new ProjectModule;
//            if ($service->editEnv($projectID, $envID, $envName, $envURI)) {
//                $this->returnJson['statusCode'] = '000000';
//            } else {
//                //修改失败
//                $this->returnJson['statusCode'] = '140021';
//            }
//        }
//        exitOutput($this->returnJson);
//    }

    /**
     * 导出项目
     */
    public function dumpProject()
    {
        $projectID = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            //项目ID不合法
            $this->returnJson['statusCode'] = '140004';
            exitOutput($this->returnJson);
        }
        $service = new ProjectModule;
        $fileName = $service->dumpProject($projectID);
        if ($fileName) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['fileName'] = $fileName;
        } else {
            //修改失败
            $this->returnJson['statusCode'] = '140021';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取api数量
     */
    public function getApiNum()
    {
        $projectID = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            //项目ID不合法
            $this->returnJson['statusCode'] = '140004';
            exitOutput($this->returnJson);
        }
        $service = new ProjectModule;
        $result = $service->getApiNum($projectID);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['num'] = $result['num'];
        } else {
            $this->returnJson['statusCode'] = '140023';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取项目动态
     */
    public function getProjectLogList()
    {
        //项目ID
        $project_id = securelyInput('projectID');
        //页码，默认1
        $page = securelyInput('page', 1);
        //每页的条目数量，默认10
        $page_size = securelyInput('pageSize', 15);

        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            // 项目ID不合法
            $this->returnJson['statusCode'] = '140004';
        } else {
            $service = new ProjectModule();
            $result = $service->getProjectLogList($project_id, $page, $page_size);

            if ($result) {
                //成功
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson = array_merge($this->returnJson, $result);
            } else {
                //获取失败，可能数据库出错
                $this->returnJson['statusCode'] = '140000';
            }
        }
        exitOutput($this->returnJson);
    }
}

?>
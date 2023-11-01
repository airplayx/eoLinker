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

class StatusCodeController
{
    // Return json Type
    private $returnJson = array('type' => 'status_code');

    /**
     * Check Login
     */
    public function __construct()
    {
        
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * Add Code
     */
    public function addCode()
    {
    	$projectID = securelyInput('projectID');
        $groupID = securelyInput('groupID');
        $status_code_list = json_decode(quickInput('statusCode'),TRUE);
        $module = new StatusCodeGroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        if (!preg_match('/^[0-9]{1,11}$/', $groupID)) {
            
            $this->returnJson['statusCode'] = '190002';
        } else {
            $service = new StatusCodeModule;
            $result = $service->addCode($projectID,$groupID, $status_code_list);
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
     * Delete Code
     */
    public function deleteCode()
    {
       
        $ids = quickInput('codeID');
        $arr = json_decode($ids);
        $arr = preg_grep('/^[0-9]{1,11}$/', $arr);
        if (empty ($arr)) {
            
            $this->returnJson ['statusCode'] = '190003';
        } else {
            $code_ids = implode(',', $arr);
            $service = new StatusCodeModule();
            $result = $service->deleteCodes($code_ids);

            if ($result) {
                
                $this->returnJson ['statusCode'] = '000000';
            } else {
                
                $this->returnJson ['statusCode'] = '190000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get Code List
     */
    public function getCodeList()
    {
        $groupID = securelyInput('groupID');

        if (!preg_match('/^[0-9]{1,11}$/', $groupID)) {
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
     * Get All Code List
     */
    public function getAllCodeList()
    {
        $projectID = securelyInput('projectID');

        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
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
     * Edit Code
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
           
            $this->returnJson['statusCode'] = '190005';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $groupID)) {
            
            $this->returnJson['statusCode'] = '190002';
        } elseif (!($codeLen >= 1 && $codeLen <= 255)) {
            
            $this->returnJson['statusCode'] = '190008';
        } elseif (!($codeDescLen >= 1 && $codeDescLen <= 255)) {
           
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
     * Search Status Code
     */
    public function searchStatusCode()
    {
        $projectID = securelyInput('projectID');
        $tipsLen = mb_strlen(quickInput('tips'), 'utf8');
        $tips = securelyInput('tips');

        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
           
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
     * Get Status Code Num
     */
    public function getStatusCodeNum()
    {
        $projectID = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
           
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
     * Add Status Code by Excel
     */
    public function addStatusCodeByExcel()
    {
        quickRequire(PATH_EXTEND . 'excel/PHPExcel.php');
        quickRequire(PATH_EXTEND . 'excel/PHPExcel/IOFactory.php');
        $filename = $_FILES['excel']['tmp_name'];
        $group_id = securelyInput('groupID');
        if (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            
            $this->returnJson['statusCode'] = '190002';
        } else {
            
            $service = new StatusCodeGroupModule();
            $user_type = $service->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $status_code_list = array();
                try {
                    $PHPExcel = \PHPExcel_IOFactory::load($filename);
                    $currentSheet = $PHPExcel->getSheet(0); 
                    $all_row = $currentSheet->getHighestRow();  
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
                        
                        $this->returnJson['statusCode'] = '190006';
                    }
                } catch (\Exception $e) {
                    $this->returnJson['statusCode'] = '190005';
                }
            }
        }

        exitOutput($this->returnJson);
    }
}

?>
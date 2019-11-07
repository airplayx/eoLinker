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

class ImportController
{
    // Return Json
    private $returnJson = array('type' => 'import');

    /**
     * Check Login
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
     * Import eoapi data
     */
    public function importEoapi()
    {
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        if (!$data) {
            $this->returnJson['statusCode'] = '310004';
            exitOutput($this->returnJson);
        }
        $server = new ImportModule;
        $result = $server->eoapiImport($data);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '310005';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Import DHC data
     */
    public function importDHC()
    {
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        if (!$data) {
            $this->returnJson['statusCode'] = '310004';
            exitOutput($this->returnJson);
        }
        $server = new ImportModule;
        $result = $server->importDHC($data);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '310001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Import postman
     */
    public function importPostman()
    {
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        $version = securelyInput('version');
        if (!$data) {
            $this->returnJson['statusCode'] = '310004';
            exitOutput($this->returnJson);
        } elseif ($version != 1 and $version != 2) {
            $this->returnJson['statusCode'] = '310002';
            exitOutput($this->returnJson);
        }
        $server = new ImportModule;
        if ($version == 1) {
            $result = $server->importPostmanV1($data);
        } else {
            $result = $server->importPostmanV2($data);
        }
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '310003';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Import Swagger
     */
    public function importSwagger()
    {
        $data = quickInput('data');
        $json = json_decode($data, TRUE);
        if (empty($json)) {
            $this->returnJson['statusCode'] = '310004';
            exitOutput($this->returnJson);
        }
        $server = new ImportModule;
        $result = $server->importSwagger($data);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '310001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Import Rap
     */
    public function importRAP()
    {
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        
        if (empty($data['modelJSON'])) {
            $this->returnJson['statusCode'] = '310001';
            exitOutput($this->returnJson);
        }
        $model_json = json_decode(str_replace("\'", "'", $data['modelJSON']), TRUE);
        
        if (empty($model_json)) {
            $this->returnJson['statusCode'] = '310003';
            exitOutput($this->returnJson);
        }
        $server = new ImportModule();
        $result = $server->importRAP($model_json);
       
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '310000';
        }
        exitOutput($this->returnJson);
    }
}

?>
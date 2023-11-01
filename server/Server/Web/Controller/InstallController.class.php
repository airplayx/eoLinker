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

class InstallController
{
    //Return Json type
    private $returnJson = array('type' => 'install');

    /**
     * Check Env
     */
    public function checkoutEnv()
    {
        $dbURL = quickInput('dbURL');
        $dbName = quickInput('dbName');
        $dbUser = quickInput('dbUser');
        $dbPassword = quickInput('dbPassword');
        $server = new InstallModule;
        $result = $server->checkoutEnv($dbURL, $dbName, $dbUser, $dbPassword);
        if (isset($result['error'])) {
            $this->returnJson['statusCode'] = '200004';
            $this->returnJson['error'] = $result['error'];
        } else {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['envStatus'] = $result;
        }
        exitOutput($this->returnJson);
    }

    /**
     * Check Config
     */
    public function checkConfig()
    {
        if (!file_exists(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php')) {
            if (@!defined(DB_URL))
                $this->returnJson['statusCode'] = '200003';
        } else {
            $this->returnJson['statusCode'] = '000000';
        }
        exitOutput($this->returnJson);
    }

    /**
     * install eolinker
     */
    public function start()
    {
        ini_set("max_execution_time", 0);
        if (file_exists(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php') || defined(DB_URL)) {
            $this->returnJson['statusCode'] = '000000';
            exitOutput($this->returnJson);
        } elseif (!file_exists(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php')) {
            $dbURL = quickInput('dbURL');
            $dbName = quickInput('dbName');
            $dbUser = quickInput('dbUser');
            $dbPassword = quickInput('dbPassword');
            $websiteName = quickInput('websiteName');
            $language = quickInput('language');
            if (empty($language)) {
                $language = 'zh-cn';
            }
            if (empty($dbURL) || empty($dbName) || empty($dbUser)) {
                $this->returnJson['statusCode'] = '200003';
                exitOutput($this->returnJson);
            }
            $server = new InstallModule;
            if ($server->createConfigFile($dbURL, $dbName, $dbUser, $dbPassword, $websiteName, $language)) {
                quickRequire(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php');
                if ($server->installDatabase()) {
                    $this->returnJson['statusCode'] = '000000';
                    @session_start();
                    @session_destroy();
                } else {
                    $this->returnJson['statusCode'] = '200002';
                    unlink(realpath(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php'));
                }
            } else {
                $this->returnJson['statusCode'] = '200001';
                unlink(realpath(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php'));
            }
            exitOutput($this->returnJson);
        }
    }

}

?>
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
class AutoGenerateController
{
    /**
     * import API
     */
    public function importApi()
    {
        $user_name = securelyInput('userName');
        $user_password = securelyInput('password');
        $project_id = securelyInput('projectKey');
        $data = json_decode(quickInput('data'), TRUE);
        if (!preg_match('/^[a-zA-Z][0-9a-zA-Z_]{3,59}$/', $user_name)) {
            exit('Username Illegal');
        } elseif (!preg_match('/^[0-9a-zA-Z]{32}$/', $user_password)) {
            exit('Password Format Illegal');
        } elseif (!preg_match('/^[1-9][0-9]{0,10}$/', $project_id)) {
            exit('projectKey Illegal');
        } else {
            $module = new AutoGenerateModule();
            if ($user_info = $module->checkProjectPermission($user_name, $user_password, $project_id)) {
                $result = $module->importApi($data, $project_id, $user_info['userID']);
                if ($result) {
                    exit('Import Successfully');
                } else {
                    exit('Import failure');
                }
            } else {
                exit('No Right');
            }
        }
    }
}
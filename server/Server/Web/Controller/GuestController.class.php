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

class GuestController
{
    //Return Json
    private $returnJson = array('type' => 'guest');

    /**
     * Login
     */
    public function login()
    {
        $loginName = securelyInput('loginName');
        $loginPassword = securelyInput('loginPassword');
        $server = new GuestModule;
        if (preg_match('/^[0-9a-zA-Z]{32}$/', $loginPassword)) {
            if (preg_match('/^[0-9a-zA-Z][0-9a-zA-Z_]{3,63}$/', $loginName)) {
                $result = $server->login($loginName, $loginPassword);

                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['userID'] = $_SESSION['userID'];
                } else
                    $this->returnJson['statusCode'] = '120004';
            } else {
                $this->returnJson['statusCode'] = '120001';
                exitOutput(json_encode($this->returnJson));
            }
        } else {
            $this->returnJson['statusCode'] = '120002';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Register
     */
    public function register()
    {
        if (!ALLOW_REGISTER) {
            $this->returnJson['statusCode'] = '130005';
        } else {
            $userName = securelyInput('userName');
            $loginPassword = securelyInput('userPassword');
            $nickNameLen = mb_strlen(quickInput('userNickName'), 'utf8');
            $userNickName = securelyInput('userNickName');

            if (!preg_match('/^[0-9a-zA-Z][0-9a-zA-Z_]{3,63}$/', $userName)) {

                $this->returnJson['statusCode'] = '130001';
            } elseif (!preg_match('/^[0-9a-zA-Z]{32}$/', $loginPassword)) {

                $this->returnJson['statusCode'] = '130002';
            } elseif (!($nickNameLen == 0 || $nickNameLen <= 16)) {

                $this->returnJson['statusCode'] = '130014';
            } else {
                $server = new GuestModule;
                $result = $server->register($userName, $loginPassword, $userNickName);

                if ($result)

                    $this->returnJson['statusCode'] = '000000';
                else

                    $this->returnJson['statusCode'] = '130005';
            }
        }

        exitOutput($this->returnJson);
    }

    /**
     * Check User Name Exist
     */
    public function checkUserNameExist()
    {
        $userName = securelyInput('userName');
        $server = new GuestModule;
        if (preg_match('/^[0-9a-zA-Z][0-9a-zA-Z_]{3,63}$/', $userName)) {
            $result = $server->checkUserNameExist($userName);
            if ($result)
                $this->returnJson['statusCode'] = '000000';
            else
                $this->returnJson['statusCode'] = '130005';
        } else
            $this->returnJson['statusCode'] = '130001';

        exitOutput($this->returnJson);
    }

    /**
     * Check Login Status
     */
    public function checkLogin()
    {
        $server = new GuestModule;
        $result = $server->checkLogin();

        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '120005';
        }
        exitOutput($this->returnJson);
    }

}

?>
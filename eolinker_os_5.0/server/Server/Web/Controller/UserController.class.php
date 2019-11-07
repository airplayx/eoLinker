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

class UserController
{
    // Return json Type
    private $returnJson = array('type' => 'user');

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
     * Logout
     */
    public function logout()
    {
        @session_start();
        @session_destroy();
        $this->returnJson['statusCode'] = '000000';
        exitOutput(json_encode($this->returnJson));
    }

    /**
     * Change Password
     */
    public function changePassword()
    {
        $oldPassword = securelyInput('oldPassword');
        $newPassword = securelyInput('newPassword');

        if (!preg_match('/^[0-9a-zA-Z]{32}$/', $newPassword) || !preg_match('/^[0-9a-zA-Z]{32}$/', $oldPassword)) {
            
            $this->returnJson['statusCode'] = '130002';
        } elseif ($oldPassword == $newPassword) {
            
            $this->returnJson['statusCode'] = '000000';
        } else {
            $server = new UserModule;
            $result = $server->changePassword($oldPassword, $newPassword);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '130006';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Change Nick Name
     */
    public function changeNickName()
    {
        $nickNameLength = mb_strlen(quickInput('nickName'), 'utf8');
        $nickName = securelyInput('nickName');

        if ($nickNameLength > 20) {
            
            $this->returnJson['statusCode'] = '130008';
        } else {
            $server = new UserModule;
            $result = $server->changeNickName($nickName);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '130009';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Confirm User Name
     */
    public function confirmUserName()
    {
        $userName = securelyInput('userName');

        
        if (!preg_match('/^[a-zA-Z][0-9a-zA-Z_]{3,59}$/', $userName)) {
           
            $this->returnJson['statusCode'] = '130001';
        } else {
            $server = new UserModule;
            $result = $server->confirmUserName($userName);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '130010';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get User Info
     */
    public function getUserInfo()
    {
        $server = new UserModule;
        $result = $server->getUserInfo();
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['userInfo'] = $result;
        } else {
            $this->returnJson['statusCode'] = '130013';
        }
        exitOutput($this->returnJson);
    }

}

?>
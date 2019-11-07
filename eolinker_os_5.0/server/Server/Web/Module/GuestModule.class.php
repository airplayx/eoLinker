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
class GuestModule
{
    /**
     * Check Login
     */
    public function checkLogin()
    {
        @session_start();
        if (isset($_SESSION['userID']))
            return $_SESSION['userName'];
        else
            return FALSE;
    }

    /**
     * User Register
     * @param $userName string UserName
     * @param $loginPassword string Password
     * @param $userNickName string UserNickName
     * @return bool
     */
    public function register(&$userName, &$loginPassword, $userNickName)
    {
        $hashPassword = md5($loginPassword);
        $dao = new GuestDao;
        $nameList = array("Aaron", "Abbott", "Bard", "Barlow", "Caesar", "Calvin", "Daniel", "Darren", "Edison", "Elmo", "Fion", "Fitch", "Gale", "Galen", "Haley", "Hamiltion", "Ingemar", "Ignatius", "Jacob", "Jafar", "Kenya", "Lennon", "Malcolm", "Nathaniel", "Oscar", "Parker", "Quennel", "Randolph", "Santiago", "Theobald", "Ulysses", "Valentine", "Waldo", "Xavier", "Yehudi", "Zachary", "Zavier", "Zebulon", "Ziv");
        $nickName = (!empty($userNickName)) ? $userNickName : '[Random]' . $nameList[rand(0, 38)];

        if ($userID = $dao->register($userName, $hashPassword, $nickName)) {
            $demoProject = '{"projectInfo":{"projectID":0,"projectType":0,"projectName":"eoinkerDemo","projectUpdateTime":"2016-11-0823:04:48","projectPassword":"","isLocked":0,"projectVersion":"1.2"},"apiGroupList":[{"groupID":275,"groupName":"demo","projectID":582,"apiList":[{"baseInfo":{"apiID":623,"apiName":"demoApi","apiURI":"www.eolinker.com","apiProtocol":0,"apiFailureMock":"","apiSuccessMock":"{\"type\":\"demo\",\"status\":\"success\",\"desc\":\"\"}","apiRequestType":0,"apiStatus":0,"apiUpdateTime":"2016-11-08 23:04:48","groupID":275,"projectID":582,"starred":0,"removed":0,"removeTime":null,"apiNoteType":1,"apiNoteRaw":"","apiNote":"","apiRequestParamType":0,"apiRequestRaw":"","mockCode":"AbKWQJuEZqQni9Hdpz2wYVEGl9qavDj2"},"headerInfo":[{"headerID":562,"headerName":"Accept-Encoding","headerValue":"compress, gzip"},{"headerID":563,"headerName":"Header Label","headerValue":"Complete header Info"}],"requestInfo":[{"paramID":11288,"paramName":"Param1","paramKey":"param1","paramType":0,"paramLimit":"number、Word、dash","paramValue":"eoapi_2016","paramNotNull":0,"paramValueList":[]},{"paramID":11289,"paramName":"param2","paramKey":"param2","paramType":0,"paramLimit":"Limitation","paramValue":"0","paramNotNull":1,"paramValueList":[{"valueID":1562,"value":"0","valueDescription":"possible 1，x"},{"valueID":1563,"value":"1","valueDescription":"possible 2，y"},{"valueID":1564,"value":"2","valueDescription":"possible 3，z"},{"valueID":1565,"value":"3","valueDescription":"possible 4，α"}]},{"paramID":11290,"paramName":"Second-level params","paramKey":"param2>>param3","paramType":0,"paramLimit":"","paramValue":"","paramNotNull":0,"paramValueList":[]}],"resultInfo":[{"paramID":10665,"paramKey":"desc","paramName":"desc","paramNotNull":1,"paramValueList":[{"valueID":9352,"value":"*","valueDescription":"not require field！"}]},{"paramID":10666,"paramKey":"status","paramName":"Status","paramNotNull":0,"paramValueList":[{"valueID":9353,"value":"error","valueDescription":"failure"},{"valueID":9354,"value":"failure","valueDescription":"failure"},{"valueID":9355,"value":"success","valueDescription":"success！"}]},{"paramID":10667,"paramKey":"type","paramName":"API Type","paramNotNull":0,"paramValueList":[{"valueID":9356,"value":"demo","valueDescription":""}]},{"paramID":10668,"paramKey":"type::secondType","paramName":"Return params","paramNotNull":0,"paramValueList":[{"valueID":9357,"value":"0","valueDescription":"success"},{"valueID":9358,"value":"1","valueDescription":"failure"}]}]}]}]}';
            $demoProject = json_decode($demoProject, TRUE);
            $importDao = new ImportDao;
            $importDao->importEoapi($demoProject, $userID);
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * Check User Name
     * @param $userName string UserName
     * @return bool
     */
    public function checkUserNameExist(&$userName)
    {
        $dao = new GuestDao;
        return $dao->checkUserNameExist($userName);
    }

    /**
     * Login
     * @param $loginName string Login username
     * @param $loginPassword string Login password
     * @return bool
     */
    public function login(&$loginName, &$loginPassword)
    {
        $time = time();
        $dao = new GuestDao;
        $userInfo = $dao->getLoginInfo($loginName);
        if (md5($loginPassword) == $userInfo['userPassword']) {
            @session_start();
            $_SESSION['userID'] = $userInfo['userID'];
            $_SESSION['userName'] = $userInfo['userName'];
            $_SESSION['userNickName'] = $userInfo['userNickName'];
            return TRUE;
        } else
            return FALSE;
    }

}

?>
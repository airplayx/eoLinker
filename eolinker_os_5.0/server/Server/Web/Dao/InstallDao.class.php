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

class InstallDao
{
    /**
     * check DB connect
     */
    public function checkDBConnect()
    {
        $conInfo = DB_TYPE . ':host=' . DB_URL . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8';
        $option = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => "set names 'utf8'",
            \PDO::ATTR_EMULATE_PREPARES => FALSE
        );
        $db_con = new \PDO($conInfo, DB_USER, DB_PASSWORD, $option);
        return $db_con;
    }

    /**
     * install Database
     * @param $sqlArray array create db
     * @return bool
     */
    public function installDatabase(&$sqlArray)
    {
        $db = getDatabase();
        $db->beginTransaction();
        try {
            foreach ($sqlArray as $query) {
                $db->query($query);
                if ($db->getError()) {
                    $db->rollback();
                    return FALSE;
                }
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            $db->rollback();
            return FALSE;
        }

        $db->commit();
        return TRUE;
    }

}

?>
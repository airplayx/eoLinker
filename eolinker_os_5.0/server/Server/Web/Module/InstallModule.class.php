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

class InstallModule
{
    /**
     * Check Env
     * @param $dbURL string
     * @param $dbName string
     * @param $dbUser string
     * @param $dbPassword string
     * @return array
     */
    public function checkoutEnv(&$dbURL, &$dbName, &$dbUser, &$dbPassword)
    {
        $result = array('fileWrite' => 0, 'db' => 0, 'curl' => 0, 'mbString' => 0, 'sessionPath' => 0, 'isInstalled' => 0);
        try {
            !is_dir(PATH_FW . DIRECTORY_SEPARATOR . 'config') && mkdir(PATH_FW . DIRECTORY_SEPARATOR . 'config', 0775);
            if (file_put_contents(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'fileWriteTest.txt', 'ok')) {
                $result['fileWrite'] = 1;
                unlink(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'fileWriteTest.txt');

                !is_dir('./dump') && mkdir('./dump', 0775);
                if (file_put_contents('./dump' . DIRECTORY_SEPARATOR . 'fileWriteTest.txt', 'ok')) {
                    $result['fileWrite'] = 1;
                    unlink('./dump' . DIRECTORY_SEPARATOR . 'fileWriteTest.txt');

                    if (file_put_contents('../fileWriteTest.txt', 'ok')) {
                        $result['fileWrite'] = 1;
                        unlink('../fileWriteTest.txt');
                    } else
                        $result['fileWrite'] = 0;
                } else
                    $result['fileWrite'] = 0;
            } else
                $result['fileWrite'] = 0;
        } catch (\Exception $e) {
            $result['fileWrite'] = '0';
            $result['fileWriteError'] = strval($e->getMessage());
        }
        try {
            $dbURL = explode(':', $dbURL);
            if (empty($dbURL[1]))
                $dbURL[1] = '3306';

            if (!class_exists('PDO')) {
                $result['db'] = 0;
            } else {
                $conInfo = 'mysql:host=' . $dbURL[0] . ';port=' . $dbURL[1] . ';dbname=' . $dbName . ';charset=utf8';
                if ($con = new \PDO($conInfo, $dbUser, $dbPassword)) {
                    $result['db'] = 1;
                    $stat = $con->query("SELECT * FROM eo_user;");
                    if ($stat) {
                        $table_name = $stat->fetch(\PDO::FETCH_ASSOC);
                        if ($table_name) {
                            $result['isInstalled'] = 1;
                        } else {
                            $result['isInstalled'] = 0;
                        }
                    } else {
                        $result['isInstalled'] = 0;
                    }
                } else {
                    $result['db'] = 0;
                }
            }
        } catch (\Exception $e) {
            $result['db'] = 0;
            $result['dbError'] = strval($e->getMessage());
        }
        try {
            if (!function_exists('curl_init')) {
                $result['curl'] = 0;
            } else {
                $ch = curl_init(realpath('./index.php'));
                if ($ch) {
                    curl_close($ch);
                    $result['curl'] = 1;
                } else
                    $result['curl'] = 0;
            }
        } catch (\Exception $e) {
            $result['curl'] = 0;
            $result['curlError'] = strval($e->getMessage());
        }
        try {
            if (!function_exists('mb_strlen')) {
                $result['mbString'] = 0;
            } else {
                $len = mb_strlen('test', 'utf8');
                if ($len) {
                    $result['mbString'] = 1;
                } else {
                    $result['mbString'] = 0;
                }
            }
        } catch (\Exception $e) {
            $result['mbString'] = 0;
            $result['mbStringError'] = strval($e->getMessage());
        }
        try {
            if (session_save_path() == '') {
                $session_path = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'C:/Windows/Temp' : '/tmp';
            } else {
                $session_path = session_save_path();
            }
            if (is_writable($session_path)) {
                $result['sessionPath'] = 1;
            } else {
                $result['sessionPath'] = 0;
            }
        } catch (\Exception $e) {
            $result['sessionPath'] = 0;
            $result['sessionPathError'] = strval($e->getMessage());
        }

        return $result;
    }

    /**
     * Create Config File
     * @param $dbURL string
     * @param $dbName string
     * @param $dbUser string
     * @param $dbPassword string
     * @param $websiteName string
     * @param $language string
     * @return bool
     */
    public function createConfigFile(&$dbURL, &$dbName, &$dbUser, &$dbPassword, &$websiteName, &$language)
    {
        if (file_exists(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php')) {

            unlink(realpath(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php'));
        }

        $dbURL = explode(':', $dbURL);
        if (empty($dbURL[1]))
            $dbURL[1] = '3306';

        $websiteName = isset($websiteName) ? $websiteName : 'eolinker Open Source Version';

        $config = "<?php
//Host Address
defined('DB_URL') or define('DB_URL', '{$dbURL[0]}');

//Host Port, Default mysql is 3306
defined('DB_PORT') or define('DB_PORT', '{$dbURL[1]}');

//Database Username
defined('DB_USER') or define('DB_USER', '{$dbUser}');

//Database Password
defined('DB_PASSWORD') or define('DB_PASSWORD', '{$dbPassword}');

//Database Name
defined('DB_NAME') or define('DB_NAME', '{$dbName}');

//Allow Register
defined('ALLOW_REGISTER') or define('ALLOW_REGISTER', TRUE);

//Allow Update
defined('ALLOW_UPDATE') or define('ALLOW_UPDATE', TRUE);

//Web Site Name
defined('WEBSITE_NAME') or define('WEBSITE_NAME', '{$websiteName}');

// Database Table header
defined('DB_TABLE_PREFIXION') or define('DB_TABLE_PREFIXION', 'eo');

//Language
defined('LANGUAGE') or define ('LANGUAGE', '{$language}');
?>";
        if ($configFile = file_put_contents(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php', $config))
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Install Database
     */
    public
    function installDatabase()
    {

        $sql = file_get_contents(PATH_FW . DIRECTORY_SEPARATOR . 'db/eolinker_os_mysql.sql');
        $sqlArray = array_filter(explode(';', $sql));
        $dao = new InstallDao;
        $result = $dao->installDatabase($sqlArray);

        return $result;
    }

}

?>
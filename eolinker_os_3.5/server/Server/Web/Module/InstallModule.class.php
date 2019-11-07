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
class InstallModule
{
    /**
     * 检测环境
     * @param $dbURL string 数据库主机地址
     * @param $dbName string 数据库名
     * @param $dbUser string 数据库用户名
     * @param $dbPassword string 数据库密码
     * @return array
     */
    public function checkoutEnv(&$dbURL, &$dbName, &$dbUser, &$dbPassword)
    {
        $result = array('fileWrite' => 0, 'db' => 0, 'curl' => 0, 'mbString' => 0, 'sessionPath' => 0, 'isInstalled' => 0);
        //检测配置目录写入权限
        try {
            if (file_put_contents(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'fileWriteTest.txt', 'ok')) {
                $result['fileWrite'] = 1;
                unlink(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'fileWriteTest.txt');

                //检测导出目录写入权限
                if (file_put_contents('./dump' . DIRECTORY_SEPARATOR . 'fileWriteTest.txt', 'ok')) {
                    $result['fileWrite'] = 1;
                    unlink('./dump' . DIRECTORY_SEPARATOR . 'fileWriteTest.txt');

                    //检测根目录写入权限
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
            $result['fileWriteError'] = $e->getMessage();
        }
        //检测数据库连接
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
                    //检测数据库是否有内容(已经安装过)
                    $stat = $con->query("SELECT * FROM eo_user;");
                    if($stat) {
                        $table_name = $stat -> fetch(\PDO::FETCH_ASSOC);
                        if ($table_name) {
                            $result['isInstalled'] = 1;
                        } else {
                            $result['isInstalled'] = 0;
                        }
                    }else{
                        $result['isInstalled'] = 0;
                    }
                } else {
                    $result['db'] = 0;
                }
            }
        } catch (\Exception $e) {
            $result['db'] = 0;
            $result['dbError'] = $e->getMessage();
        }
        //检测CURL
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
            $result['curlError'] = $e->getMessage();
        }
        //检测mbString
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
            $result['mbStringError'] = $e->getMessage();
        }
        //检测session路径写入权限
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
            $result['sessionPathError'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 写入配置文件
     * @param $dbURL string 数据库主机地址
     * @param $dbName string 数据库名
     * @param $dbUser string 数据库用户名
     * @param $dbPassword string 数据库密码
     * @param $websiteName string 网站名称
     * @param $language string 语言
     * @return bool
     */
    public function createConfigFile(&$dbURL, &$dbName, &$dbUser, &$dbPassword, &$websiteName, &$language)
    {
        if (file_exists(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php')) {
            //不存在配置文件，需要跳转至引导页面进行安装
            unlink(realpath(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php'));
        }

        $dbURL = explode(':', $dbURL);
        if (empty($dbURL[1]))
            $dbURL[1] = '3306';

        $websiteName = isset($websiteName) ? $websiteName : 'eolinker开源版';

        $config = "<?php
//主机地址
defined('DB_URL') or define('DB_URL', '{$dbURL[0]}');

//主机端口,默认mysql为3306
defined('DB_PORT') or define('DB_PORT', '{$dbURL[1]}');

//连接数据库的用户名
defined('DB_USER') or define('DB_USER', '{$dbUser}');

//连接数据库的密码，推荐使用随机生成的字符串
defined('DB_PASSWORD') or define('DB_PASSWORD', '{$dbPassword}');

//数据库名
defined('DB_NAME') or define('DB_NAME', '{$dbName}');

//是否允许新用户注册
defined('ALLOW_REGISTER') or define('ALLOW_REGISTER', TRUE);

//是否允许更新项目，如果设置为FALSE，那么自动更新和手动更新都将失效
defined('ALLOW_UPDATE') or define('ALLOW_UPDATE', TRUE);

//网站名称
defined('WEBSITE_NAME') or define('WEBSITE_NAME', '{$websiteName}');

//数据表前缀
defined('DB_TABLE_PREFIXION') or define('DB_TABLE_PREFIXION', 'eo');

//语言
defined('LANGUAGE') or define ('LANGUAGE', '{$language}');
?>";
        if ($configFile = file_put_contents(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php', $config))
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 安装数据库
     */
    public
    function installDatabase()
    {
        //读取数据库文件
        $sql = file_get_contents(PATH_FW . DIRECTORY_SEPARATOR . 'db/eolinker_os_mysql.sql');
        $sqlArray = array_filter(explode(';', $sql));
        $dao = new InstallDao;
        $result = $dao->installDatabase($sqlArray);

        return $result;
    }

}

?>
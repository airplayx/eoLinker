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

namespace RTP;

use RTP\Module;

//是否初次部署，设定为TRUE将在所有用户自行创建的用户目录下新建空白的index.html文件防止部分服务器开启的目录查看功能，上线前设为false提高性能
defined('FIRST_DEPLOYMENT') or define('FIRST_DEPLOYMENT', FALSE);

//定义请求方式(AJAX-Type)，GET/POST/AUTO,默认为POST
defined('AT') or define('AT', 'POST');

defined('DEBUG') or define('DEBUG', TRUE);

//数据库类型，用于PDO数据库连接
defined('DB_TYPE') or define('DB_TYPE', 'mysql');

//数据库是否需要保持长期连接（长连接）,多线程高并发环境下请开启,默认关闭
defined('DB_PERSISTENT_CONNECTION') or define('DB_PERSISTENT_CONNECTION', FALSE);

//框架模块目录名称
defined('PATH_MODULE') or define('PATH_MODULE', '/Module/');

//框架函数目录名称
defined('PATH_COMMON') or define('PATH_COMMON', '/Common/');

//框架特性(Traits)目录名称
defined('PATH_TRAITS') or define('PATH_TRAITS', '/Traits/');

//框架拓展(extend)目录名称
defined('PATH_EXTEND') or define('PATH_EXTEND', './RTP/extend/');

//框架异常(Exception)目录名称
defined('PATH_EXCEPTION') or define('PATH_EXCEPTION', '/Module/Exception/');

//用户控制器目录名称
defined('DIR_CONTROLLER') or define('DIR_CONTROLLER', 'Controller');

//用户模块目录名称
defined('DIR_MODULE') or define('DIR_MODULE', 'Module');

//用户Dao目录名称
defined('DIR_DAO') or define('DIR_DAO', 'Dao');

//用户数据模型目录名称
defined('DIR_MODEL') or define('DIR_MODEL', 'Model');

//框架存放的相对路径（相对于入口文件而言）,默认是'./RTP'
defined('PATH_FW') or define('PATH_FW', './RTP');

//项目代码存放的相对路径（相对于入口文件而言）
defined('PATH_APP') or define('PATH_APP', './Server');

//设置时区
date_default_timezone_set('Asia/Shanghai');

//判断DEBUG模式操作
DEBUG ? error_reporting(E_ALL ^ E_NOTICE) : error_reporting(0);

//引入必要文件文件
require PATH_FW . PATH_COMMON . 'EasyFunction.php';
require PATH_FW . PATH_MODULE . 'AutomaticallyModule.class.php';

//捕获全局信息
try {
    //启动自动化模块
    Module\AutomaticallyModule::start();

    //如果是首次部署项目，则在所有的项目下面新建空白的安全文件
    if (FIRST_DEPLOYMENT)
        Module\FileModule::createSecurityIndex();
} catch (Module\ExceptionModule $e) {
    //传参为True时，遇到异常后即停止程序运行
    $e->printError(FALSE);
} catch (\Exception $e) {
    echo $e->getMessage();
    exit;
}
?>
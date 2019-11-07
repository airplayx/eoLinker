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

use RTP\Module as M;

$filePaths = NULL;

/**
 * 快捷数据库操作函数
 */
function getDatabase($isNewInstance = false)
{
    return $isNewInstance ? M\DatabaseModule::getNewInstance() : M\DatabaseModule::getInstance();
}

/**
 * 快捷完成请求函数，用于一次性按顺序返回所有信息，无须担心Cookie放置位置。
 * 注意，需要配合P()函数使用
 */
function quickFlush()
{
    ob_start();
    $outputFlush = M\OutputStorageModule::getAll();
    if (is_null($outputFlush))
        return;
    foreach ($outputFlush as $value) {
        echo $value;
    }
    //输出缓冲区并且清除缓冲区内容
    ob_end_flush();
    M\OutputStorageModule::clean();
}

/**
 * 快捷输入函数
 */
function quickInput($paramName, $defaultValue = NULL)
{
    switch (strtolower(AT)) {
        case 'auto' :
            {
                if (is_null($_GET[$paramName]) || $_GET[$paramName] == '') {
                    if (is_null($_POST[$paramName] || $_POST[$paramName] == ''))
                        return $defaultValue;
                    else
                        return $_POST[$paramName];
                } else
                    return $_GET[$paramName];
            }
        case 'post' :
            {
                if (is_null($_POST[$paramName]) || $_POST[$paramName] == '')
                    return $defaultValue;
                else
                    return $_POST[$paramName];
            }
        case 'get' :
            {
                if (is_null($_GET[$paramName]) || $_GET[$paramName] == '')
                    return $defaultValue;
                else
                    return $_GET[$paramName];
            }
        default :
            return NULL;
    }
}

/**
 * 安全输入函数,获取参数并且对参数进行过滤
 */
function securelyInput($paramName, $defaultValue = NULL)
{
    switch (strtolower(AT)) {
        case 'auto' :
            {
                if (is_null($_GET[$paramName]) || $_GET[$paramName] == '') {
                    if (is_null($_POST[$paramName]) || $_POST[$paramName] == '')
                        return $defaultValue;
                    else
                        return cleanFormat($_POST[$paramName]);
                } else
                    return cleanFormat($_GET[$paramName]);
            }
        case 'post' :
            {
                if (is_null($_POST[$paramName]) || $_POST[$paramName] == '')
                    return $defaultValue;
                else
                    return cleanFormat($_POST[$paramName]);
            }
        case 'get' :
            {
                if (is_null($_GET[$paramName]) || $_GET[$paramName] == '')
                    return $defaultValue;
                else
                    return cleanFormat($_GET[$paramName]);
            }
        default :
            return NULL;
    }
}

/**
 * 快捷输出函数:output,默认数组输出json,字符串直接输出
 */
function quickOutput($output)
{
    echo is_array($output) ? json_encode($output) : $output;
}

/**
 * 结束输出函数:output,默认数组输出json,字符串直接输出，并且输出之后停止程序
 */
function exitOutput($output)
{
    exit(is_array($output) ? json_encode($output) : $output);
}

/**
 * 快捷序列化输出函数，需要配合quickFlush()函数使用
 */
function serialPrint($output, $distinct = FALSE)
{
    if ($distinct)
        if (M\OutputStorageModule::isExist($output))
            return;
    M\OutputStorageModule::set($output);
}

/**
 * 快速引入文件函数
 */
function quickRequire($filePath)
{
    global $filePaths;
    if (is_null($filePaths))
        $filePaths = array();

    if (!isset($filePaths[$filePath])) {
        if (is_file($filePath)) {
            //require不使用函数形式是因为参数带括号会降低运行速度
            require $filePath;
            $filePaths[$filePath] = TRUE;
        } else {
            $filePaths[$filePath] = FALSE;
        }
    }
}

/**
 * 快捷Session操作函数:session
 */
function quickSession(&$key, &$value)
{
    if (session_status() == 1)
        session_start();
    if (isset($_SESSION[$key])) {
        if (isset($value))
            $_SESSION[$key] = $value;
        return $_SESSION[$key];
    } else
        $_SESSION[$key] = $value;
}

/**
 * 格式清除函数
 */
function cleanFormat(&$value)
{
    return htmlspecialchars(stripcslashes(trim($value)));
}

/**
 * 换行输出数组信息
 */
function printFormatted(array $info)
{
    foreach ($info as $key => $value) {
        echo "$key:$value</br>";
    };
}

/**
 * 判断请求协议是否为https
 * @return bool
 */
function is_https()
{
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        return TRUE;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return TRUE;
    } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
        return TRUE;
    }

    return FALSE;
}

/**
 * 异常处理
 * @param $error_level
 * @param $error_str
 * @throws Exception
 */
function err_handler($error_level, $error_str)
{
    throw new Exception("[$error_level] $error_str", 100001);
}

/**
 * 判断session是否已开启
 * @return bool
 */
function is_session_started()
{
    return function_exists('session_status') ? (PHP_SESSION_ACTIVE == session_status()) : (!empty (session_id()));
}

?>

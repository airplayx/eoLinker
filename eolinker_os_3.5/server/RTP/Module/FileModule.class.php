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

namespace RTP\Module;

class FileModule
{
	/**
	 * 自动在用户目录下面创建空白index.html文件，用于保护文件目录
	 */
	public static function createSecurityIndex()
	{
		$path = PATH_APP;
		$dirs = array();
		$ban_dirs = array(
			'./',
			'.',
			'../',
			'..'
		);
		self::getAllDirs($path, $dirs, $ban_dirs);

		foreach ($dirs as $dir)
		{
			if (file_exists($dir . '/index.html') || file_exists($dir . '/index.php'))
				continue;
			else
			{
				$file = fopen($dir . '/index.html', 'w');
				fwrite($file, '');
				fclose($file);
			}
		}
	}

	/**
	 * 获取路径下的所有目录
	 * @param String $path 目标路径
	 * @param array $dirs 用于储存返回路径的数组
	 * @param array $ban_dirs [可选]需要过滤的目录的相对地址的数组
	 */
	public static function getAllDirs($path, array &$dirs, array &$ban_dirs = array())
	{
		$paths = scandir($path);
		foreach ($paths as $nextPath)
		{
			if (!in_array($nextPath, $ban_dirs) && is_dir($path . DIRECTORY_SEPARATOR . $nextPath))
			{
				$dirs[] = realpath($path . DIRECTORY_SEPARATOR . urlencode($nextPath));
				self::getAllDirs($path . DIRECTORY_SEPARATOR . $nextPath, $dirs, $ban_dirs);
			}
		}
	}

	/**
	 * 获取路径下的所有文件
	 * @param String $path 目标路径
	 * @param array $dirs 用于储存返回路径的数组
	 * @param array $ban_dirs [可选]需要过滤的文件名的数组
	 */
	public static function getAllFiles($path, &$dirs, &$ban_dirs = array())
	{
		$paths = scandir($path);
		foreach ($paths as $nextPath)
		{
			if (!in_array($nextPath, $ban_dirs) && is_file($path . DIRECTORY_SEPARATOR . $nextPath))
			{
				$dirs[] = realpath($path . DIRECTORY_SEPARATOR . urlencode($nextPath));
				self::getAllFiles($path . DIRECTORY_SEPARATOR . $nextPath, $dirs, $ban_dirs);
			}
		}
	}

}
?>
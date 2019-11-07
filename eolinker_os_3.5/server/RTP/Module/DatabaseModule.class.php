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

class DatabaseModule
{
	private static $instance;
	private static $db_con;
	private $db_history;
	//上一次操作结果
	private $last_result;
	private $last_sql;

	/**
	 * 获取实例
	 */
	public static function getInstance()
	{
		//如果已经含有一个实例则直接返回实例
		if (!is_null(self::$instance))
		{
			return self::$instance;
		}
		else
		{
			//如果没有实例则新建
			return self::getNewInstance();
		}
	}

	/**
	 * 获取一个新的实例
	 */
	public static function getNewInstance()
	{
		self::$instance = null;
		self::$instance = new self;
		return self::$instance;
	}

	/**
	 * 创建对象时自动连接数据库
	 */
	protected function __construct()
	{
		self::connect();
	}

	/**
	 * 销毁对象时自动断开数据库连接
	 */
	function __destruct()
	{
		self::close();
	}

	/**
	 * 连接主机
	 */
	private function connect()
	{
		$conInfo = DB_TYPE . ':host=' . DB_URL . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8';

		//是否保持持久化链接
		if (DB_PERSISTENT_CONNECTION)
		{
			$option = array(
				\PDO::MYSQL_ATTR_INIT_COMMAND => "set names 'utf8'",
				\PDO::ATTR_PERSISTENT => TRUE,
				\PDO::ATTR_EMULATE_PREPARES => FALSE,
                \PDO::ATTR_STRINGIFY_FETCHES => FALSE
			);
		}
		else
		{
			$option = array(
				\PDO::MYSQL_ATTR_INIT_COMMAND => "set names 'utf8'",
				\PDO::ATTR_EMULATE_PREPARES => FALSE,
                \PDO::ATTR_STRINGIFY_FETCHES => FALSE
			);
		}

		//尝试连接数据库
		try
		{
			self::$db_con = new \PDO($conInfo, DB_USER, DB_PASSWORD, $option);

		}
		catch(\PDOException $e)
		{
			if (DEBUG)
				print_r($e -> getMessage());
			exit ;
		}
	}

	/**
	 * 关闭主机连接
	 */
	public function close()
	{
		self::$db_con = NULL;
		self::$instance = NULL;
	}

	/**
	 * 执行无返回值的数据库操作并且返回受影响的记录条数
	 */
	public function execute($sql)
	{
		$this -> last_sql = $sql;
		$result = self::$db_con -> exec($sql);
		$this -> getError();
		return $result;
	}

	/**
	 * 执行操作并返回一条数据
	 */
	public function query($sql)
	{
		$this -> last_sql = $sql;
		$this -> db_history = self::$db_con -> query($sql);
		$this -> getError();
		$this -> last_result = $this -> db_history -> fetch(\PDO::FETCH_ASSOC);
		return $this -> last_result;
	}

	/**
	 * 执行操作并返回多条数据(如果可能)
	 */
	public function queryAll($sql)
	{
		$this -> last_sql = $sql;
		$this -> db_history = self::$db_con -> query($sql);
		$this -> getError();
		$this -> last_result = $this -> db_history -> fetchAll(\PDO::FETCH_ASSOC);
		return $this -> last_result;
	}

	/**
	 * prepare方式执行操作，返回一条数据，防止sql注入
	 */
	public function prepareExecute($sql, $params = NULL)
	{
		$this -> last_sql = $sql;
		$this -> db_history = self::$db_con -> prepare($sql);
		$this -> getError();
		if (is_null($params))
		{
			$this -> db_history -> execute();
		}
		else
		{
			$this -> db_history -> execute($params);
		}
		$this -> getError();
		$this -> last_result = $this -> db_history -> fetch(\PDO::FETCH_ASSOC);

		return $this -> last_result;
	}

	/**
	 * prepare方式执行操作，返回多条数据（如果可能），防止sql注入
	 */
	public function prepareExecuteAll($sql, $params = NULL)
	{
		$this -> last_sql = $sql;
		$this -> db_history = self::$db_con -> prepare($sql);
		$this -> getError();
		if (is_null($params))
		{
			$this -> db_history -> execute();
		}
		else
		{
			$this -> db_history -> execute($params);
		}
		$this -> getError();
		$this -> last_result = $this -> db_history -> fetchAll(\PDO::FETCH_ASSOC);

		return $this -> last_result;
	}

	/**
	 * prepare方式，以新的参数重新执行一次查询，返回一条数据
	 */
	public function prepareRexecute($params)
	{
		$this -> db_history -> execute($params);
		$this -> getError();
		$this -> last_result = $this -> db_history -> fetch(\PDO::FETCH_ASSOC);
		return $this -> last_result;
	}

	/**
	 * prepare方式，以新的参数重新执行一次查询，返回多条数据（如果可能）
	 */
	public function prepareRexecuteAll($params)
	{
		$this -> db_history -> execute($params);
		$this -> getError();
		$this -> last_result = $this -> db_history -> fetchAll(\PDO::FETCH_ASSOC);
		return $this -> last_result;
	}

	/**
	 * 获取上一次操作影响的行数
	 */
	public function getAffectRow()
	{
		if (is_null($this -> db_history))
		{
			return 0;
		}
		else
		{
			return $this -> db_history -> rowCount();
		}
	}

	/**
	 * 获取最后执行的SQL语句
	 */
	public function getLastSQL()
	{
		return $this -> last_sql;
	}

	/**
	 * 获取最后插入行的ID或序列值
	 */
	public function getLastInsertID()
	{
		return self::$db_con -> lastInsertId();
	}

	/**
	 * 获取错误信息
	 */
	public function getError()
	{
		$result = self::$db_con -> errorInfo();
		if (DEBUG)
		{
			if ($result[0] != 00000)
			{
				$error = json_encode(self::$db_con -> errorInfo());
				throw new ExceptionModule(12000, "database error in:$error");
			}
		}
		else
		{
			if ($result[0] != 00000)
			{
				return FALSE;
			}
			else
				return TRUE;
		}
	}

	/**
	 * 开始事务
	 */
	public function beginTransaction()
	{
		self::$db_con -> beginTransaction();
	}

	/**
	 * 回滚事务
	 */
	public function rollback()
	{
		self::$db_con -> rollback();
	}

	/**
	 * 提交事务
	 */
	public function commit()
	{
		self::$db_con -> commit();
	}

}
?>

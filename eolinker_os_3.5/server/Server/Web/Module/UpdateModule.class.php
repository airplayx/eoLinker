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
class UpdateModule
{
    /**
     * 自动更新项目
     * @param $updateURI string 更新地址
     * @return bool
     * @throws Exception
     */
    public function autoUpdate($updateURI)
    {
        try {
            set_error_handler('err_handler');
            $ch = curl_init($updateURI);
            //跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            //检查证书中是否设置域名
            @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            file_put_contents('../release.zip', curl_exec($ch));
            curl_close($ch);
            $zip = new ZipArchive;
            if ($zip->open('../release.zip'))
                $zip->extractTo('../');
            $zip->close();

            //备份数据库
            $backupDao = new BackupDao();
            $sql = $backupDao->getDatabaseBackupSql();
            $file_name = "eoLinker_backup_database_" . time() . '.sql';
            if (!file_put_contents(realpath('./dump') . DIRECTORY_SEPARATOR . $file_name, $sql)) {
                return FALSE;
            }
            //接下来开始获取旧数据库的全部结构
            $updateDao = new UpdateDao;
            $updateDao->updateDatabase();

            //执行额外的更新操作，主要用于在版本过渡的过程中，数据以及文件发生变化等情况
            if (file_exists(PATH_FW . DIRECTORY_SEPARATOR . 'Common/UpdateFunction.php'))
                quickRequire(PATH_FW . DIRECTORY_SEPARATOR . 'Common/UpdateFunction.php');

            return TRUE;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 100001);
        }

    }

    /**
     * 手动更新项目
     * @return bool
     * @throws Exception
     */
    public function manualUpdate()
    {
        try {
            //备份数据库
            $backupDao = new BackupDao();
            $sql = $backupDao->getDatabaseBackupSql();
            $file_name = "eoLinker_backup_database_" . time() . '.sql';
            if (!file_put_contents(realpath('./dump') . DIRECTORY_SEPARATOR . $file_name, $sql)) {
                return FALSE;
            }
            //接下来开始获取旧数据库的全部结构
            $updateDao = new UpdateDao;
            $updateDao->updateDatabase();

            //执行额外的更新操作，主要用于在版本过渡的过程中，数据以及文件发生变化等情况
            if (file_exists(PATH_FW . DIRECTORY_SEPARATOR . 'Common/UpdateFunction.php'))
                quickRequire(PATH_FW . DIRECTORY_SEPARATOR . 'Common/UpdateFunction.php');

            return TRUE;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 100001);
        }
    }

}

?>
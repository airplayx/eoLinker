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
class DatabaseModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * get userType from database
     * @param $dbID int DatabaseID
     * @return bool|int
     */
    public function getUserType(&$dbID)
    {
        $dao = new AuthorizationDao();
        $result = $dao->getDatabaseUserType($_SESSION['userID'], $dbID);
        if ($result === FALSE) {
            return -1;
        } else {
            return $result;
        }
    }

    /**
     * add database
     * @param $dbName string Database Name
     * @param $dbVersion string Database version, version 1.0
     * @return bool|int
     */
    public function addDatabase(&$dbName, &$dbVersion = "1.0")
    {
        $databaseDao = new DatabaseDao;
        return $databaseDao->addDatabase($dbName, $dbVersion, $_SESSION['userID']);
    }

    /**
     * delete database
     * @param $dbID int  DATABASEID
     * @return bool
     */
    public function deleteDatabase(&$dbID)
    {
        $databaseDao = new DatabaseDao;
        if ($dbID = $databaseDao->checkDatabasePermission($dbID, $_SESSION['userID'])) {
            return $databaseDao->deleteDatabase($dbID);
        } else
            return FALSE;
    }

    /**
     * get all database list
     * @return bool|array
     */
    public function getDatabaseList()
    {
        $databaseDao = new DatabaseDao;
        return $databaseDao->getDatabaseList($_SESSION['userID']);
    }

    /**
     * Get Database Info
     * @param $dbID
     * @return array|bool
     */
    public function getDatabase(&$dbID)
    {
        $dbDao = new DatabaseDao();
        return $dbDao->getDatabase($dbID, $_SESSION['userID']);
    }

    /**
     * edit database
     * @param $dbID int DatabaseID
     * @param $dbName string Database Name
     * @param $dbVersion string Database version
     * @return bool
     */
    public function editDatabase(&$dbID, &$dbName, &$dbVersion)
    {
        $databaseDao = new DatabaseDao;
        if ($dbID = $databaseDao->checkDatabasePermission($dbID, $_SESSION['userID'])) {
            return $databaseDao->editDatabase($dbID, $dbName, $dbVersion);
        } else
            return FALSE;
    }

    /**
     * Import database table which export from mysql
     * @param $dbName
     * @param $tables array Database table
     * @return bool
     */
    public function importDatabase(&$dbName, &$tables)
    {
        $userID = $_SESSION['userID'];
        $databaseDao = new DatabaseDao;
        $tableList = array();
        foreach ($tables as $table) {
            $fieldList = array();
            preg_match_all('/.+?[\r\n]+/s', $table['tableField'], $fields);

            $primaryKeys = '';
            foreach ($fields[0] as $field) {
                $field = trim($field);
                if (strpos($field, '`') === 0) {
                    $fieldName = substr($field, 1, strpos(substr($field, 1), '`'));
                    preg_match('/`\\s(.+?)\\s/', $field, $type);
                    preg_match("/COMMENT.*'(.*?)'/", $field, $fieldDesc);
                    if (empty($fieldDesc)) {
                        preg_match("/comment.*'(.*?)'/", $field, $fieldDesc);
                    }
                    if (!$type[1]) {
                        $type[1] = substr($field, strlen($fieldName) + 3, strpos(substr($field, strlen($fieldName) + 3), ','));
                    }
                    if (!$type[1]) {
                        $type[1] = substr($field, strlen($fieldName) + 3);
                    }
                    if (strpos($type[1], '(')) {
                        $fieldType = substr($type[1], 0, strpos($type[1], '('));
                        if (preg_match('/\([0-9]{1,10}/', $type[1], $match)) {
                            $fieldLength = substr($match[0], 1);
                        } else {
                            $fieldLength = '0';
                        }
                    } else {
                        $fieldType = $type[1];
                        $fieldLength = '0';
                    }

                    if (strpos($field, 'NOT NULL') !== FALSE) {
                        $isNotNull = 1;
                    } else
                        $isNotNull = 0;

                    $fieldList[] = array(
                        'fieldName' => $fieldName,
                        'fieldDesc' => $fieldDesc[1],
                        'fieldType' => $fieldType,
                        'fieldLength' => $fieldLength,
                        'isNotNull' => $isNotNull
                    );
                }

                if (strpos($field, 'PRIMARY') !== FALSE) {
                    $table['primaryKey'] = $table['primaryKey'] . substr($field, strpos($field, '('));
                }
            }

            foreach ($fieldList as &$tableField) {
                if (strpos($table['primaryKey'], $tableField['fieldName']) !== FALSE) {
                    $tableField['isPrimaryKey'] = 1;
                } else {
                    $tableField['isPrimaryKey'] = 0;
                }
            }
            $tableList[] = array(
                'tableName' => $table['tableName'],
                'tableDesc' => $table['tableDesc'],
                'fieldList' => $fieldList
            );
            unset($fieldList);
        }

        if (isset($tableList[0])) {
            $databaseType = 0;
            return $databaseDao->importDatabase($dbName, $tableList, $databaseType, $userID);
        } else {
            return FALSE;
        }
    }

    /**
     * Import database by database's data which export from the api named exportDatabase
     * @param $data string Database reSource
     * @return bool
     */
    public function importDatabaseByJson(&$data)
    {
        $user_id = $_SESSION['userID'];

        $service = new DatabaseDao;
        $result = $service->importDatabaseByJson($user_id, $data);
        if ($result)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Export database's data
     * @param $dbID int DatabaseID
     * @return bool|string
     */
    public function exportDatabase(&$dbID)
    {
        $dao = new DatabaseDao;
        if ($dao->checkDatabasePermission($dbID, $_SESSION['userID'])) {
            $dumpJson = json_encode($dao->getDatabaseInfo($dbID));
            $fileName = 'eoLinker_export_' . $_SESSION['userName'] . '_' . time() . '.export';
            if (file_put_contents(realpath('./dump') . DIRECTORY_SEPARATOR . $fileName, $dumpJson)) {
                return $fileName;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    public function importOracleDatabase(&$database_name, &$tables)
    {
        $user_id = $_SESSION['userID'];

        $database_dao = new DatabaseDao();

        $table_list = array();
        foreach ($tables as $table) {
            $field_list = array();
            $fields = array();
            preg_match_all('/.+?[\r\n]+/s', $table ['tableField'], $fields);

            foreach ($fields [0] as $field) {
                $field = trim($field);
                if (strpos($field, '"') === 0) {
                    $field_name = substr($field, 1, strpos(substr($field, 1), '"'));
                    $type = array();
                    preg_match('/`\\s(.+?)\\s/', $field, $type);
                    if (!$type [1]) {
                        $type [1] = substr($field, strlen($field_name) + 3, strpos(substr($field, strlen($field_name) + 3), ','));
                    }
                    if (!$type [1]) {
                        $type [1] = substr($field, strlen($field_name) + 3);
                    }
                    if (strpos($type [1], '(')) {
                        $field_type = substr($type [1], 0, strpos($type [1], '('));
                        $match = array();
                        if (preg_match('/\([0-9]{1,10}/', $type [1], $match)) {
                            $field_length = substr($match [0], 1);
                        } else {
                            $field_length = '0';
                        }
                    } else {
                        $field_type = $type [1];
                        $field_length = '0';
                    }

                    if (strpos($field, 'NOT NULL') !== FALSE) {
                        $is_not_null = 1;
                    } else
                        $is_not_null = 0;

                    if (strpos($table ['primaryKeySql'], $field_name) !== FALSE) {
                        $is_primary_key = 1;
                    } else {
                        $is_primary_key = 0;
                    }
                    $field_desc = array();
                    if (strpos($table ['commentSql'], $field_name) !== FALSE) {
                        preg_match('/COMMENT ON COLUMN.*?' . $field_name . '.*?\'(.*?)\'.*?;/', $table ['commentSql'], $field_desc);
                    }

                    $field_list [] = array(
                        'fieldName' => $field_name,
                        'fieldType' => $field_type,
                        'fieldLength' => $field_length,
                        'isNotNull' => $is_not_null,
                        'isPrimaryKey' => $is_primary_key,
                        'fieldDesc' => $field_desc [1]
                    );
                }
            }
            $table_list [] = array(
                'tableName' => $table ['tableName'],
                'tableDesc' => $table ['tableDesc'],
                'fieldList' => $field_list
            );
            unset($field_list);
        }

        if (isset($table_list [0])) {
            $database_type = 1;
            return $database_dao->importDatabase($database_name, $table_list, $database_type, $user_id);
        } else
            return FALSE;
    }
}

?>
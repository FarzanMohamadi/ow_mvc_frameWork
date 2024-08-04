<?php
/**
 * Core database connection class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_core
 * @since 1.0
 */
final class OW_Database
{
    const NO_CACHE_ENTRY = "ow_db_no_cache_entry";

    /**
     * @var array
     */
    private static $classInstances;

    /**
     * Mysql connection object
     *
     * @var PDO
     */
    private $connection;

    /**
     * Number of rows affected by the last SQL statement
     *
     * @var int
     */
    private $affectedRows;

    /**
     * Logger data
     *
     * @var array
     */
    private $queryLog;

    /**
     * Debug option
     *
     * @var boolean
     */
    private $debugMode;

    /**
     * Enter description here...
     *
     * @var boolean
     */
    private $isProfilerEnabled;

    /**
     * Enter description here...
     *
     * @var UTIL_Profiler
     */
    private $profiler;

    /**
     * Last executed query
     *
     * @var int
     */
    private $queryExecTime;

    /**
     * Enter description here...
     *
     * @var int
     */
    private $totalQueryExecTime;

    /**
     *
     * @var int
     */
    private $queryCount;

    /**
     * @var boolean
     */
    private $useCashe;

    /**
     * Getter for $log property
     *
     * @return array
     */
    public function getQueryLog()
    {
        return $this->queryLog;
    }

    /**
     * Getter for $queryExecTime property
     *
     * @return int
     */
    public function getQueryExecTime()
    {
        return $this->queryExecTime;
    }

    /**
     * @return int
     */
    public function getTotalQueryExecTime()
    {
        return $this->totalQueryExecTime;
    }

    /**
     * @return int
     */
    public function getQueryCount()
    {
        return $this->queryCount;
    }

    /**
     * @return bool
     */
    public function getUseCashe()
    {
        return $this->useCashe;
    }

    /**
     * @param bool $useCashe
     */
    public function setUseCashe( $useCashe )
    {
        $this->useCashe = (bool) $useCashe;
    }

    /**
     * Constructor.
     *
     * @param array $params
     */
    private function __construct( $params )
    {
        if (isset($params['reconnect']) && $params['reconnect']) {
            $this->__destruct();
        }

        $port = isset($params['port']) ? (int) $params['port'] : null;
        $socket = isset($params['socket']) ? $params['socket'] : null;

        try
        {
            if ( $socket === null )
            {
                $dsn = "mysql:host={$params['host']};";
                if ( $port !== null )
                {
                    $dsn .= "port={$params['port']};";
                }
            }
            else
            {
                $dsn = "mysql:unix_socket={$socket};";
            }
            $dsn .= "dbname={$params['dbname']}";

            $this->connection = new PDO($dsn, $params['username'], $params['password'],
                array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4;',
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));

            if ( !$this->isMysqlValidVersion() )
            {
                throw new InvalidArgumentException("Cant connect to database. Connection needs MySQL version 5.0 + !");
            }

            $this->prepareMysql();

            if ( !empty($params['profilerEnable']) )
            {
                $this->isProfilerEnabled = true;
                $this->profiler = UTIL_Profiler::getInstance('db');
                $this->queryCount = 0;
                $this->queryExecTime = 0;
                $this->totalQueryExecTime = 0;
                $this->queryLog = array();
            }

            if ( !empty($params['debugMode']) )
            {
                $this->debugMode = true;
            }

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->useCashe = false;
        }
        catch ( PDOException $e )
        {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * Returns the instance of class for $params
     *
     * @param array $params ( host, username, password, dbname, [socket], [port] )
     *
     * @return OW_Database
     *
     */
    public static function getInstance( $params )
    {
        if (isset($params['reconnect']) && $params['reconnect']) {
            self::$classInstances = array();
        }

        if ( !isset(self::$classInstances) )
        {
            self::$classInstances = array();
        }

        ksort($params);

        $connectionKey = serialize($params);

        if ( empty(self::$classInstances[$connectionKey]) )
        {
            if ( !isset($params['host']) || !isset($params['username']) || !isset($params['password']) || !isset($params['dbname']) )
            {
                throw new InvalidArgumentException("Can't connect to database. Please provide valid connection attributes.");
            }

            self::$classInstances[$connectionKey] = new self($params);
        }

        return self::$classInstances[$connectionKey];
    }

    /**
     * @param string $sql
     * @param array $params
     * @param int $cacheLifeTime
     * @param array $tags
     * @return mixed
     */
    public function queryForColumn( $sql, array $params = null, $cacheLifeTime = 0, $tags = array() )
    {
        $dataFromCache = $this->getFromCache($sql, $params, $cacheLifeTime);

        if ( $dataFromCache !== self::NO_CACHE_ENTRY )
        {
            return $dataFromCache;
        }

        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetchColumn(); // (PDO::FETCH_COLUMN);
        $stmt->closeCursor();

        if ( $result === false )
        {
            $result = null;
        }

        $this->saveToCache($result, $sql, $params, $cacheLifeTime, $tags);
        return $result;
    }

    /**
     * Enter description here...
     *
     * @param string $sql
     * @param object|string $className
     * @param array $params
     * @return mixed
     */
    public function queryForObject( $sql, $className, array $params = null, $cacheLifeTime = 0, $tags = array() )
    {
        $dataFromCache = $this->getFromCache($sql, $params, $cacheLifeTime);

        if ( $dataFromCache !== self::NO_CACHE_ENTRY )
        {
            return $dataFromCache;
        }

        $stmt = $this->execute($sql, $params);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $className);
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if ( $result === false )
        {
            $result = null;
        }
        else
        {
            $result->generateFieldsHash();
        }

        $this->saveToCache($result, $sql, $params, $cacheLifeTime, $tags);
        return $result;
    }

    /**
     * 
     * @param string $sql
     * @param string $className
     * @param array $params
     * @param int $cacheLifeTime
     * @param array $tags
     * @return array
     */
    public function queryForObjectList( $sql, $className, array $params = null, $cacheLifeTime = 0, $tags = array() )
    {
        $dataFromCache = $this->getFromCache($sql, $params, $cacheLifeTime);

        if ( $dataFromCache !== self::NO_CACHE_ENTRY )
        {
            return $dataFromCache;
        }

        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetchAll(PDO::FETCH_CLASS, $className);

        foreach ( $result as $item )
        {
            $item->generateFieldsHash();
        }

        $this->saveToCache($result, $sql, $params, $cacheLifeTime, $tags);
        return $result;
    }

    /**
     * Set time zone
     *
     * @return void
     */
    public function setTimezone()
    {
        $date = new DateTime;
        $this->query('SET TIME_ZONE = ?', array(
            $date->format('P')
        ));
    }

    /**
     * @param string $sql
     * @param array $params
     * @param int $cacheLifeTime
     * @param array $tags
     * @return array
     */
    public function queryForRow( $sql, array $params = null, $cacheLifeTime = 0, $tags = array() )
    {
        $dataFromCache = $this->getFromCache($sql, $params, $cacheLifeTime);

        if ( $dataFromCache !== self::NO_CACHE_ENTRY )
        {
            return $dataFromCache;
        }

        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ( $result === false )
        {
            $result = array();
        }

        $this->saveToCache($result, $sql, $params, $cacheLifeTime, $tags);
        return $result;
    }

    /**
     * Enter description here...
     *
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function queryForList( $sql, array $params = null, $cacheLifeTime = 0, $tags = array() )
    {
        $dataFromCache = $this->getFromCache($sql, $params, $cacheLifeTime);

        if ( $dataFromCache !== self::NO_CACHE_ENTRY )
        {
            return $dataFromCache;
        }

        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->saveToCache($result, $sql, $params, $cacheLifeTime, $tags);
        return $result;
    }

    /**
     * @param string $sql
     * @param array $params
     * @param int $cacheLifeTime
     * @param array $tags
     * @return array
     */
    public function queryForColumnList( $sql, array $params = null, $cacheLifeTime = 0, $tags = array() )
    {
        $dataFromCache = $this->getFromCache($sql, $params, $cacheLifeTime);

        if ( $dataFromCache !== self::NO_CACHE_ENTRY )
        {
            return $dataFromCache;
        }

        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $this->saveToCache($result, $sql, $params, $cacheLifeTime, $tags);
        return $result;
    }

    /**
     * Enter description here...
     *
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function query( $sql, array $params = null )
    {
        if(strpos(strtoupper($sql), 'ALTER')!==false) {
            OW::getEventManager()->trigger(new OW_Event(FRMEventManager::BEFORE_ALTER_QUERY_EXECUTED, array('query' => $sql, 'params' => $params)));
        }

        $stmt = $this->execute($sql, $params);
        $rowCount = $stmt->rowCount();
        $stmt->closeCursor();

        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::AFTER_QUERY_EXECUTED, array('query' => $sql, 'params' => $params)));
        return $rowCount;
    }

    /**
     * @param $sql
     * @param array $params
     * @return int
     */
    public function delete( $sql, array $params = null )
    {
        return $this->query($sql, $params);
    }

    /**
     * insert data and return last insert id
     *
     * @param string $sql
     * @param array $params
     * @return int last_insert_id
     */
    public function insert( $sql, array $params = null )
    {
        $stmt = $this->execute($sql, $params);
        $lastInsertId = $this->connection->lastInsertId();
        $stmt->closeCursor();
        return $lastInsertId;
    }

    /**
     * UPDATE query
     *
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function update( $sql, array $params = null )
    {
        return $this->query($sql, $params);
    }

    /**
     * Insert object $obj to table $tableName. Returns last_insert_id
     * throws InvalidArgumentException
     *
     * @param string $tableName
     * @param object $obj
     * @return int
     */
    public function insertObject( $tableName, $obj )
    {
        if ( $obj != null && is_object($obj) )
        {
            $params = get_object_vars($obj);
            $paramNames = array_keys($params);
            $columns = UTIL_String::arrayToDelimitedString($paramNames, ',', '`', '`');
            $values = UTIL_String::arrayToDelimitedString($paramNames, ',', ':');
            $sql = "INSERT INTO `{$tableName}` ({$columns}) VALUES ({$values})";

            return $this->insert($sql, $params);
        }
        else
        {
            throw new InvalidArgumentException('object expected');
        }
    }

    /**
     * @param string $tableName
     * @param OW_Entity $obj
     * @param string $primaryKeyName
     * @return int
     * @throws InvalidArgumentException
     */
    public function updateObject( $tableName, $obj, $primaryKeyName = 'id' )
    {
        if ( $obj != null && is_object($obj) )
        {
            $params = get_object_vars($obj);

            if ( !array_key_exists($primaryKeyName, $params) )
            {
                throw new InvalidArgumentException('object property not found');
            }

            $fieldsToUpdate = $obj->getEntinyUpdatedFields();

            if ( empty($fieldsToUpdate) )
            {
                return true;
            }

            $updateArray = array();
            foreach ( $params as $key => $value )
            {
                if ( $key !== $primaryKeyName )
                {
                    if ( in_array($key, $fieldsToUpdate) )
                    {
                        $updateArray[] = '`' . $key . '`=:' . $key;
                    }
                    else
                    {
                        unset($params[$key]);
                    }
                }
            }

            $updateStmt = UTIL_String::arrayToDelimitedString($updateArray);
            $sql = "UPDATE `{$tableName}` SET {$updateStmt} WHERE {$primaryKeyName}=:{$primaryKeyName}";
            return $this->update($sql, $params);
        }
        else
        {
            throw new InvalidArgumentException('object expected');
        }
    }

    public function mergeInClause( array $valueList )
    {
        if ( $valueList === null )
        {
            return '';
        }

        $result = '';
        foreach ( $valueList as $value )
        {
            $result .= ( '\'' . $this->escapeValue($value) . '\',' ); //"'$value',"
        }

        $result = mb_substr($result, 0, mb_strlen($result) - 1);
        return $result;
    }

    /***
     * @param $tableName
     * @param $objects
     * @param int $batchSize
     * @param int $sleep
     * @throws Exception
     */
    public function batchInsertOrUpdateObjectList( $tableName, $objects, $batchSize = 30, $sleep=0)
    {
        if ( !empty($objects) && is_array($objects) )
        {
            if ( !is_object($objects[0]) )
            {
                throw new InvalidArgumentException('Array of objects expected');
            }

            $params = get_object_vars($objects[0]);
            $paramNames = array_keys($params);
            $columns = UTIL_String::arrayToDelimitedString($paramNames, ',', '`', '`');

            $i = 0;
            $totalInsertsCount = 0;
            $objectsCount = count($objects);
            $batchSize = (int) $batchSize;
            $inserts = array();

            foreach ( $objects as $obj )
            {
                $values = '(';
                foreach ( $paramNames as $property )
                {
                    if ( $obj->$property !== null )
                    {
                        $values .= ( '\'' . $this->escapeValue($obj->$property) . '\',' );
                    }
                    else
                    {
                        $values .= 'NULL,';
                    }
                }
                $values = mb_substr($values, 0, mb_strlen($values) - 1);
                $values .= ')';
                $inserts[] = $values;

                $i++;
                $totalInsertsCount++;

                if ( $i === $batchSize || $totalInsertsCount === $objectsCount )
                {
                    $sql = "REPLACE INTO `{$tableName}` ({$columns}) VALUES" . implode(',', $inserts);
                    $inserts = array();
                    $i = 0;
                    $this->execute($sql)->closeCursor();
                    //$this->connection->query($sql)->closeCursor();
                    sleep($sleep);
                }
            }
        }
        else
        {
            throw new InvalidArgumentException('Array expected');
        }
    }

    /**
     * Escapes SQL value
     *
     * @param string $value
     * @return string
     */
    public function escapeValue($value)
    {
        if(is_numeric($value))
        {
            return (string)$value;
        }
        if(is_bool($value) )
        {
            if(!$value)
            {
                return '0';
            }else{
                return '1';
            }
        }
        $quotedString = $this->connection->quote($value); // real_escape_string( $string );
        return mb_substr($quotedString, 1, mb_strlen($quotedString) - 2); //dirty hack to delete quotes
    }
    /*     * 206.123.0
     * Returns affected rows
     *
     * @return integer
     */

    public function getAffectedRows()
    {
        return $this->affectedRows;
    }

    /**
     * Returns last insert id
     *
     * @return integer
     */
    public function getInsertId( $seqname = null )
    {
        return $this->connection->lastInsertId($seqname);
    }

    /**
     * Class destruct actions
     */
    public function __destruct()
    {
        if ( isset($this->connection) )
        {
            $this->connection = null;
        }
    }

    /**
     * @param $sql
     * @param array|null $params
     * @param $retry
     * @return PDOStatement
     * @throws Exception
     */
    private function execute( $sql, array $params = null, $retry = false)
    {
        if ( $this->isProfilerEnabled )
        {
            $this->profiler->reset();
        }

        /* @var $stmt PDOStatement */
        $stmt = $this->connection->prepare($sql);
        if ( $params !== null )
        {
            foreach ( $params as $key => $value )
            {
                $paramType = PDO::PARAM_STR;
                if ( is_int($value) )
                    $paramType = PDO::PARAM_INT;
                elseif ( is_bool($value) )
                    $paramType = PDO::PARAM_BOOL;

                $stmt->bindValue(is_int($key) ? $key + 1 : $key, $value, $paramType);
            }
        }
        OW::getEventManager()->trigger(new OW_Event("core.sql.exec_query", array("sql" => $sql, "params" => $params)));
        try{
            $stmt->execute(); //TODO setup profiler
        }
        catch (Exception $ex){
            $msg = $ex->getMessage();
            OW::getLogger()->writeLog(OW_Log::ERROR, 'sql_execute_error', ["sql" => $sql, "params" => $params, 'message' => $msg]);

            // Reset connection for socket (Cli clients)
            if (php_sapi_name() === 'cli') {
                if (strstr($msg, 'MySQL server has gone away')) {
                    OW::getLogger()->writeLog(OW_Log::INFO, 'sql_execute_reconnect', ["sql" => $sql, "params" => $params, 'message' => $msg]);
                    if (!$retry) {
                        return OW::getDbo(true)->execute($sql, $params, true);
                    }
                }
            }

            throw $ex;
        }
        if ($retry){
            OW::getLogger()->writeLog(OW_Log::INFO, 'sql_execute_reconnect_successfully', ["sql" => $sql, "params" => $params]);
        }
        $this->affectedRows = $stmt->rowCount();

        if ( $this->isProfilerEnabled )
        {
            $this->queryExecTime = $this->profiler->getTotalTime();
            $this->totalQueryExecTime += $this->queryExecTime;

            $this->queryCount++;
            $this->queryLog[] = array('query' => $sql, 'execTime' => $this->queryExecTime, 'params' => $params);
        }

        return $stmt;
    }

    /**
     * Check if MySQL version is 5+
     *
     * @return boolean
     */
    private function isMysqlValidVersion()
    {
        $verArray = explode('.', $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION));
        return intval($verArray[0]) >= 5;
    }

    /**
     * Set additional MySQL server settings
     */
    private function prepareMysql()
    {
        if ( $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME) == 'mysql' )
        {
            $verArray = explode('.', $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION));

            if ( intval($verArray[0]) == 5 && intval($verArray[1]) >=7 && intval($verArray[2]) >= 9 )
            {
                $this->connection->exec(' SET SESSION sql_mode = ""; ');
            }
        }
    }

    private function getCacheKeyForQuery( $query, $params )
    {
        return 'core.sql.' . md5(trim($query) . serialize($params));
    }

    private function cacheEnabled( $expTime )
    {
        return !OW_DEV_MODE && $this->useCashe && ( $expTime === false || $expTime > 0 );
    }

    /**
     * @return OW_CacheManager
     */
    private function getCacheManager()
    {
        return OW::getCacheManager();
    }

    private function getFromCache( $sql, $params, $cacheLifeTime )
    {
        if ( $this->cacheEnabled($cacheLifeTime) )
        {
            $cacheKey = $this->getCacheKeyForQuery($sql, $params ? $params : array());
            $cacheData = $this->getCacheManager()->load($cacheKey);

            if ( $cacheData !== null )
            {
                return unserialize($cacheData);
            }
        }

        $data = OW::getEventManager()->call("core.sql.get_query_result", array("sql" => $sql, "params" => $params));

        if ( is_array($data) && isset($data["result"]) && $data["result"] === true )
        {
            return $data["value"];
        }

        return self::NO_CACHE_ENTRY;
    }

    private function saveToCache( $result, $sql, $params, $cacheLifeTime, $tags )
    {
        if ( $this->cacheEnabled($cacheLifeTime) )
        {
            $cacheKey = $this->getCacheKeyForQuery($sql, $params ? $params : array());
            $this->getCacheManager()->save(serialize($result), $cacheKey, $tags, $cacheLifeTime);
        }

        OW::getEventManager()->trigger(new OW_Event("core.sql.set_query_result",
            array("sql" => $sql, "params" => $params, "result" => $result)));
    }

    public function tableExist($tableName){
        $table = OW::getDbo()->queryForRow('show tables like :tableName', array('tableName' => $tableName));

        if (empty($table)) {
            return false;
        }

        return true;
    }
}

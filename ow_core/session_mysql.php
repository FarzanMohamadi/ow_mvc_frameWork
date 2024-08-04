<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_core
 * @since 1.0
 */

class OW_SessionMysql {
    private $db;

    private function ensure_session_active()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function __construct(){
        // Instantiate new Database object
        $this->db = new SessionDatabase();

        // Set handler to overide SESSION
        session_set_save_handler(
            array($this, "_open"),
            array($this, "_close"),
            array($this, "_read"),
            array($this, "_write"),
            array($this, "_destroy"),
            array($this, "_gc")
        );

        // Start the session
        $this->ensure_session_active();
    }
    public function _open(){
        if(empty($this->db)){
            // Instantiate new Database object
            $this->db = new SessionDatabase();

            // Start the session
            $this->ensure_session_active();
        }

        // If successful
        if($this->db){
            // Return True
            return true;
        }

        // Return False
        return false;
    }
    public function _close(){
        // Close the database connection
        // If successful
        if(empty($this->db) || $this->db->close()){
            // Return True
            $this->db = null;
            return true;
        }
        // Return False
        return false;
    }
    public function _read($id){
        // Set query
        $this->db->query('SELECT data FROM `' . OW_DB_PREFIX . 'sessions` WHERE id = :id');
        // Bind the Id
        $this->db->bind(':id', $id);
        // Attempt execution
        // If successful
        if($this->db->execute()){
            // Save returned row
            $row = $this->db->single();
            // Return the data
            if(empty($row)){
                return '';
            }
            return $row['data'];
        }else{
            // Return an empty string
            return '';
        }
    }
    public function _write($id, $data){
        // Create time stamp
        $access = time();
        // Set query
        $this->db->query('REPLACE INTO `' . OW_DB_PREFIX . 'sessions` VALUES (:id, :access, :data)');
        // Bind data
        $this->db->bind(':id', $id);
        $this->db->bind(':access', $access);
        $this->db->bind(':data', $data);
        // Attempt Execution
        // If successful
        if($this->db->execute()){
            // Return True
            return true;
        }
        // Return False
        return false;
    }
    public function _destroy($id){
        // Set query
        $this->db->query('DELETE FROM `' . OW_DB_PREFIX . 'sessions` WHERE id = :id');
        // Bind data
        $this->db->bind(':id', $id);
        // Attempt execution
        // If successful
        if($this->db->execute()){
            // Return True
            return true;
        }
        // Return False
        return false;
    }
    public function _gc($max){
        // Calculate what is to be deemed old
        $old = time() - $max;
        // Set query
        $this->db->query('DELETE FROM `' . OW_DB_PREFIX . 'sessions` WHERE access < :old');
        // Bind data
        $this->db->bind(':old', $old);
        // Attempt execution
        if($this->db->execute()){
            // Return True
            return true;
        }
        // Return False
        return false;
    }
}

class SessionDatabase{
    private $host      = OW_DB_HOST;
    private $user      = OW_DB_USER;
    private $pass      = OW_DB_PASSWORD;
    private $dbname    = OW_DB_NAME;
    private $dbh;
    private $stmt;

    public function __construct(){
        // Set DSN
        $dsn = 'mysql:host=' . $this->host .';';
        if ( OW_DB_PORT !== null )
        {
            $dsn .= 'port='.OW_DB_PORT.';';
        }
        $dsn .= 'dbname=' . $this->dbname;
        // Set options
        $options = array(
            PDO::ATTR_PERSISTENT    => false, // change to true for faster page loading with limited users
            PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION
        );
        // Create a new PDO instanace
        $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
    }

    public function query($query){
        $this->stmt = $this->dbh->prepare($query);
    }
    public function bind($param, $value, $type = null){
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }
    public function execute(){
        return $this->stmt->execute();
    }

    public function resultset(){
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function single(){
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function rowCount(){
        return $this->stmt->rowCount();
    }

    public function lastInsertId(){
        return $this->dbh->lastInsertId();
    }

    public function beginTransaction(){
        return $this->dbh->beginTransaction();
    }

    public function endTransaction(){
        return $this->dbh->commit();
    }

    public function cancelTransaction(){
        return $this->dbh->rollBack();
    }

    public function debugDumpParams(){
        return $this->stmt->debugDumpParams();
    }

    public function close(){
        $this->dbh = null;
        return true;
    }
}
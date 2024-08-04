<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmftpmanager
 */
class FRMFTPMANAGER_CLASS_Ftp extends UTIL_Ftp
{
    private function __construct()
    {
    }

    public static function getConnection( array $params )
    {
        $connection = new self();
        return $connection;
    }

    public function getStream()
    {
        return null;
    }

    public function connect( $host, $port = 21 )
    {
        return true;
    }

    public function isConnected()
    {
        return true;
    }

    public function login( $username, $password )
    {
        return true;
    }

    public function __destruct()
    {
    }
}

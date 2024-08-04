<?php
/**
 * The class is a gateway for auth. adapters and provides common API to authenticate users.
 *
 * @package ow_core
 * @since 1.0
 */
interface OW_ICacheBackend
{
    public function save( $data, $key, array $tags = array(), $expTime=0 );
    public function load( $key );
    public function test( $key );
    public function remove( $key );
    public function clean( array $tags, $mode );
}
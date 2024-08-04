<?php
/**
 * The class is a gateway for auth. adapters and provides common API to authenticate users.
 *
 * @package ow_core
 * @since 1.0
 */
interface OW_IAuthenticator
{
    /**
     * Checks if current user is authenticated.
     *
     * @return boolean
     */
    public function isAuthenticated();

    /**
     * Returns current user id.
     * If user is not authenticated 0 returned.
     *
     * @return integer
     */
    public function getUserId();

    /**
     * Logins user by provided user id.
     *
     * @param integer $userId
     */
    public function login( $userId );

    /**
     * Logs out current user.
     */
    public function logout();

    /**
     * Returns auth id
     */
    public function getId();
}
<?php
/**
 * Base abstract class for auth adapters.
 * Used to implement particular type of authentication.
 *
 * @package ow_core
 * @since 1.0
 */
abstract class OW_AuthAdapter
{
    /**
     * Tries to authenticate user.
     *
     * @return OW_AuthResult
     */
    abstract function authenticate();
}
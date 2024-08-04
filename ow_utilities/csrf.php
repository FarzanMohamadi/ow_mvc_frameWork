<?php
/**
 * @package ow_core
 * @since 1.8.3
 */
class UTIL_Csrf
{
    const SESSION_VAR_NAME = "csrf_tokens";

    /**
     * Generates and returns CSRF token
     * 
     * @return string
     */
    public static function generateToken()
    {
        $tokenList = self::getTokenList();
        $token = base64_encode(time() . UTIL_String::getRandomString(32));
        $tokenList[$token] = time();
        self::saveTokenList($tokenList);

        return $token;
    }

    /**
     * Checks if provided token is valid and not expired
     * 
     * @param string $token
     * @return bool
     */
    public static function isTokenValid( $token )
    {
        $tokenList = self::getTokenList();

        return !empty($tokenList[$token]);
    }
    /* -------------------------------------------------------------------------------------------------------------- */

    private static function getTokenList()
    {
        return OW::getSession()->isKeySet(self::SESSION_VAR_NAME) ? OW::getSession()->get(self::SESSION_VAR_NAME) : array();
    }

    private static function saveTokenList( array $list )
    {
        OW::getSession()->set(self::SESSION_VAR_NAME, $list);
    }
}

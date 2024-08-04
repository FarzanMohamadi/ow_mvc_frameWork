<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_utilities
 * @since 1.0
 */
class UTIL_Validator
{
    const PASSWORD_MIN_LENGTH = 4;

    const PASSWORD_MAX_LENGTH = 128;

    const USER_NAME_PATTERN = '/^[\w]{1,32}$/';

    const EMAIL_PATTERN = '/^([\w\-\.\+\%]*[\w])@((?:[A-Za-z0-9\-]+\.)+[A-Za-z]{2,})$/';

    const URL_PATTERN = '/^(http(s)?:\/\/)?((\d+\.\d+\.\d+\.\d+)|(([\w-]+\.)+([a-z,A-Z][\w-]*)))(:[1-9][0-9]*)?(\/?([\w\-.\,\/:%+@&*=~]+[\w\- \,.\/?:%+@&=*|]*)?)?(#(.*))?$/';

    const INT_PATTERN = '/^[-+]?[0-9]+$/';

    const FLOAT_PATTERN = '/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/';

    const ALPHA_NUMERIC_PATTERN = '/^[A-Za-z0-9]+$/';

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $string
     * @return mixed
     */
    public static function convertToEnglishNumbers($string) {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١','٠'];

        $num = range(0, 9);
        $convertedPersianNums = str_replace($persian, $num, $string);
        $englishNumbersOnly = str_replace($arabic, $num, $convertedPersianNums);

        return $englishNumbersOnly;
    }

    public static function isEmailValid( $value )
    {
        $value = self::convertToEnglishNumbers($value);

        $pattern = self::EMAIL_PATTERN;

        if ( !preg_match($pattern, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isUrlValid( $value )
    {
        $pattern = self::URL_PATTERN;

        if ( !preg_match($pattern, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isIntValid( $value )
    {
        if ( !preg_match(self::INT_PATTERN, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isFloatValid( $value )
    {
        if ( !preg_match(self::FLOAT_PATTERN, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isAlphaNumericValid( $value )
    {
        $pattern = self::ALPHA_NUMERIC_PATTERN;

        if ( !preg_match($pattern, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isUserNameValid( $value )
    {
        $value = self::convertToEnglishNumbers($value);

        $pattern = self::USER_NAME_PATTERN;

        if ( !preg_match($pattern, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isDateValid( $month, $day, $year )
    {
        if ( !checkdate($month, $day, $year) )
        {
            return false;
        }

        return true;
    }

    public static function isCaptchaValid( $value )
    {
        if(file_exists(OW_DIR_ROOT.'ow_unittest'.DS.'captchaTest'))
        {
            return true;
        }
        if ( $value === null )
        {
            return false;
        }

        require_once OW_DIR_LIB . 'securimage/securimage.php';
        // Passing array of options to the constructor
        $options = array('no_session'   => false /* dont use sessions */
            //,'use_database' => true /* use sqlite db */
            //,'captcha_type' => Securimage::SI_CAPTCHA_MATHEMATIC /* use math captcha */,
        );
        $img = new Securimage($options);

        if ( !$img->check($value) )
        {
            return false;
        }

        return true;
    }
}
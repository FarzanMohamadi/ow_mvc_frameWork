<?php
/**
 * @package ow_utilities
 * @since 1.0
 */
class UTIL_String
{
    private static $caps = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

    /**
     * Replaces upper case chars in string with delimeter + lowercase chars 
     *
     * @param string $string
     * @param string $divider
     * @return string
     */
    public static function capsToDelimiter( $string, $delimiter = '_' )
    {
        static $delimiters = array();

        if ( !isset($delimiters[$delimiter]) )
        {
            $delimiters[$delimiter]['search'] = array();
            $delimiters[$delimiter]['replace'] = array();

            foreach ( self::$caps as $value )
            {
                $delimiters[$delimiter]['search'][] = $value;
                $delimiters[$delimiter]['replace'][] = $delimiter . mb_strtolower($value);
            }
        }

        return str_replace($delimiters[$delimiter]['search'], $delimiters[$delimiter]['replace'], $string);
    }

    /**
     * Replaces lowercase case chars + delimiter in string uppercase chars
     *
     * @param unknown_type $string
     * @param unknown_type $delimiter
     * @return unknown
     */
    public static function delimiterToCaps( $string, $delimiter = '_' )
    {
        $searchArray = array();
        $replaceArray = array();

        foreach ( self::$caps as $value )
        {
            $searchArray[] = $delimiter . mb_strtolower($value);
            $searchArray[] = $delimiter . $value;

            $replaceArray[] = $value;
            $replaceArray[] = $value;
        }

        return str_replace($searchArray, $replaceArray, $string);
    }

    /**
     * Enter description here...
     *
     * @param array $array
     * @param string $delimiter
     * @param string $left
     * @param string $right
     * @return string
     */
    public static function arrayToDelimitedString( array $array, $delimiter = ',', $left = '', $right = '' )
    {
        $result = '';
        foreach ( $array as $value )
        {
            $result .= ( $left . $value . $right . $delimiter);
        }
        $length = mb_strlen($result);
        if ( $length > 0 )
        {
            $result = mb_substr($result, 0, $length - 1);
        }
        return $result;
    }

    public static function removeFirstAndLastSlashes( $string )
    {
        if ( mb_substr($string, 0, 1) === '/' )
        {
            $string = mb_substr($string, 1);
        }

        if ( mb_substr($string, -1) === '/' )
        {
            $string = mb_substr($string, 0, -1);
        }
        return $string;
    }

    //TODO write description
    public static function replaceVars( $data, array $vars = null )
    {
        if ( !isset($vars) || count($vars) < 1 )
        {
            return $data;
        }

        foreach ( $vars as $key => $var )
        {
            $data = preg_replace('/{\$(' . preg_quote($key) . ')}/i', $var, $data);
        }

        return $data;
    }

    /**
     * @deprecated since version 1.7
     * 
     * @param int $length
     * @param int $strength
     * @return string
     */
    public static function generatePassword( $length = 8, $strength = 3 )
    {
        return self::getRandomString($length, $strength);
    }
    const RND_STR_NUMERIC = 1;
    const RND_STR_ALPHA_NUMERIC = 2;
    const RND_STR_ALPHA_WITH_CAPS_NUMERIC = 3;
    const RND_STR_ALPHA_WITH_CAPS_NUMERIC_SPEC = 4;

    /**
     * Returns random string of provided length and strength.
     * 
     * @since 1.8.1
     * @param string $prefix
     * @param int $length
     * @param int $strength
     * @return string
     */
    public static function getRandomStringWithPrefix( $prefix, $length = 8, $strength = self::RND_STR_ALPHA_WITH_CAPS_NUMERIC )
    {
        return $prefix . self::getRandomString($length, $strength);
    }

    /**
     * Returns random string of provided length and strength.
     * 
     * @since 1.7
     * @param int $length
     * @param int $strength
     * @return string
     */
    public static function getRandomString( $length = 8, $strength = self::RND_STR_ALPHA_WITH_CAPS_NUMERIC )
    {
        list($usec, $sec) = explode(" ", microtime());
        $seed = (float) $sec + ((float) $usec * 100000)*rand();

        srand((int)$seed);

        $chars1 = "1234";
        $chars2 = "56789";

        if ( $strength > 1 )
        {
            $chars1 .= "aeiouy";
            $chars2 .= "bdghjklmnpqrstvwxz";
        }

        if ( $strength > 2 )
        {
            $chars1 .= "AEIOUY";
            $chars2 .= "BDGHJKLMNPQRSTVWXZ";
        }

        if ( $strength > 3 )
        {
            $chars1 .= "@#";
            $chars2 .= "$%";
        }

        $rndString = "";
        $alt = time() % 2;
        $chars1Length = strlen($chars1);
        $chars2Length = strlen($chars2);

        for ( $i = 0; $i < $length; $i++ )
        {
            if ( $alt === 1 )
            {
                $rndString .= $chars2[(rand() % $chars2Length)];
                $alt = 0;
            }
            else
            {
                $rndString .= $chars1[(rand() % $chars1Length)];
                $alt = 1;
            }
        }

        return $rndString;
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $html
     * @param $length
     * @param $ending
     * @param bool $isUtf8
     * @return string
     */
    public static function truncate_html($html, $length, $ending = null, $isUtf8=true)
    {
        $printedLength = 0;
        $position = 0;
        $tags = array();

        $resp = "";

        // For UTF-8, we need to count multibyte sequences as one character.
        $re = $isUtf8
            ? '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;|[\x80-\xFF][\x80-\xBF]*}'
            : '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}';

        while ($printedLength < $length && preg_match($re, $html, $match, PREG_OFFSET_CAPTURE, $position))
        {
            list($tag, $tagPosition) = $match[0];

            // Print text leading up to the tag.
            $str = substr($html, $position, $tagPosition - $position);
            if ($printedLength + strlen($str) > $length)
            {
                $resp .= substr($str, 0, $length - $printedLength);
                $printedLength = $length;
                break;
            }

            $resp .= ($str);
            $printedLength += strlen($str);
            if ($printedLength >= $length) break;

            if ($tag[0] == '&' || ord($tag) >= 0x80)
            {
                // Pass the entity or UTF-8 multibyte sequence through unchanged.
                $resp .= ($tag);
                $printedLength++;
            }
            else
            {
                // Handle the tag.
                $tagName = $match[1][0];
                if ($tag[1] == '/')
                {
                    // This is a closing tag.
                    if(substr($tag,2,-1) == end($tags)){
                        array_pop($tags);
                    }
                    $resp .= ($tag);
                }
                else if ($tag[strlen($tag) - 2] == '/')
                {
                    // Self-closing tag.
                    $resp .= ($tag);
                }
                else
                {
                    // Opening tag.
                    $resp .= ($tag);
                    $tags[] = $tagName;
                }
            }

            // Continue after the tag.
            $position = $tagPosition + strlen($tag);
        }

        // Print any remaining text.
        if ($printedLength < $length && $position < strlen($html))
            $resp .= substr($html, $position, $length - $printedLength);

        // Close any open tags.
        while (!empty($tags))
            $resp .= '</'.array_pop($tags).'>';
        return $resp . (empty($ending) ? '' : $ending);
    }

    public static function truncate( $string, $length, $ending = null )
    {
        $truncate_index = $length-1;
        $ending_characters = array(' ','.',';','،','!','؛');
        while(!in_array(mb_substr($string, $truncate_index,1),$ending_characters) && $truncate_index < mb_strlen($string)){
            $truncate_index++;
        }
        if ( mb_strlen($string) <= $truncate_index )
        {
            return $string;
        }

        return mb_substr($string, 0, $truncate_index) . (empty($ending) ? '' : $ending);
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $string
     * @return mixed
     */
    public static function prettify( $string )
    {
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $string)));
        if (isset($stringRenderer->getData()['string'])) {
            $string = ($stringRenderer->getData()['string']);
        }
        return $string;
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $string
     * @return mixed
     */
    public static function strip_non_numeric( $string )
    {
        return preg_replace("/[^0-9]/", "", $string );
    }

    /**
     *  Split words that longer than $split_length in the $string by $delimiter
     *
     * @param string $string
     * @param string $delimiter
     * @param integer $split_length
     * @return string
     */
    public static function splitWord( $string, $delimiter = ' ', $split_length = 16 )
    {
        $string_array = explode(' ', $string);
        foreach ( $string_array as $id => $word )
        {
            if ( mb_strpos($word, '-') != 0 )
                continue;

            if ( mb_strlen($word) > $split_length )
            {
                $str = mb_substr($word, $split_length / 2);
                $string_array[$id] = mb_substr($word, 0, $split_length / 2) . $delimiter . $str;
            }
        }

        return implode(' ', $string_array);
    }

    /**
     * @param string $xmlString
     * @return array
     */
    public static function xmlToArray( $xmlString )
    {
        $xml = simplexml_load_string($xmlString);

        if ( !$xml )
        {
            return false;
        }

        return self::processXmlObject($xml);
    }

    private static function processXmlObject( SimpleXMLElement $el )
    {
        $result = (array) $el;

        foreach ( $result as $key => $val )
        {
            if ( is_object($val) && $val instanceof SimpleXMLElement )
            {
                $result[$key] = self::processXmlObject($val);
            }
        }

        return $result;
    }

    public static function startsWith( $haystack, $needle ) {
        $length = strlen( $needle );
        return substr( $haystack, 0, $length ) === $needle;
    }

    public static function endsWith( $haystack, $needle ) {
        $length = strlen( $needle );
        if( !$length ) {
            return true;
        }
        return substr( $haystack, -$length ) === $needle;
    }
}

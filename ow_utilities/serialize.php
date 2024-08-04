<?php
/**
 * Serialize utility.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_utilities
 * @since 1.8.1
 */

class UTIL_Serialize
{
    const SERIALIZED_OBJECT_MARK = '#!serialized!#';

    /**
     * Checks if a string is serialized object
     *
     * @param string $serialized
     * @return boolean
     */

    public static function isSerializedObject($serialized) {
        return self::getClassName($serialized) != null;
    }

    /**
     * Returns class name of serialized object
     *
     * @param string $serialized
     * @return string
     */
    public static function getClassName($serialized) {
        if ( preg_match('/^'.self::SERIALIZED_OBJECT_MARK.'(.+?)'.self::SERIALIZED_OBJECT_MARK.'.*$/', $serialized, $matches) )
        {
            return $matches[1];
        }

        return null;
    }

    /**
     * Returns serialized data
     *
     * @param string $serialized
     * @return string
     */
    public static function getSerializedData($serialized) {
        if ( preg_match('/^'.self::SERIALIZED_OBJECT_MARK.'.+?'.self::SERIALIZED_OBJECT_MARK.'(.*)$/', $serialized, $matches) )
        {
            return $matches[1];
        }

        return null;
    }

    /**
     * Serialize object
     *
     * @param string $serialized
     * @return string
     */
    public static function serialize(Serializable $object) {
        return self::SERIALIZED_OBJECT_MARK. get_class($object) . self::SERIALIZED_OBJECT_MARK . $object->serialize();
    }

    /**
     * @param string $serialized
     * @return Serializable
     */
    public static function unserialize($serialized) {
        $className = self::getClassName($serialized);
        $serializedData = self::getSerializedData($serialized);

        if ( $className == null || $serializedData == null )
        {
            return null;
        }

        if( !class_exists($className) )
        {
            return null;
        }

        /* @var $object Serializable */
        $object = new $className;
        $object->unserialize($serializedData);

        return $object;
    }
}
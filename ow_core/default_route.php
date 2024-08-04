<?php
/**
 * The class is responsible for default stratagy of url generation.
 * All URIs (except URIs working with custom routes) are generated and decomposed by default route.
 * DefaultRoute class can be extended and modified to change whole url generation strategy.
 * 
 * @package ow_core
 * @since 1.0
 */
class OW_DefaultRoute
{
    private $controllerNamePrefix = 'CTRL';

    /**
     * @return string
     */
//    public function getControllerNamePrefix()
//    {
//        return $this->controllerNamePrefix;
//    }
//
//    /**
//     * @param string $controllerNamePrefix
//     */
//    public function setControllerNamePrefix( $controllerNamePrefix )
//    {
//        $this->controllerNamePrefix = $controllerNamePrefix;
//    }

    /**
     * Generates URI using provided params.
     *
     * @throws InvalidArgumentException
     * @param string $controller
     * @param string $action
     * @param array $params
     * @return string
     */
    public function generateUri( $controller, $action = null, array $params = array() )
    {
        if ( empty($controller) || ( empty($action) && !empty($params) ) )
        {
            throw new InvalidArgumentException("Can't generate uri for empty controller/action !");
        }

        $ctrlParts = explode('_', $controller);
        $moduleNamePrefix = str_replace('ctrl', '', strtolower($ctrlParts[1]));

        if ( strlen($moduleNamePrefix) > 0 )
        {
            $moduleNamePrefix .= '-';
        }

        $controller = trim($controller);
        $action = ( $action === null ) ? null : trim($action);

        $paramString = '';

        foreach ( $params as $key => $value )
        {
            $paramString .= trim($key) . '/' . urlencode(trim($value)) . '/';
        }

        $className = str_replace(OW::getAutoloader()->getPackagePointer($controller) . '_', '', $controller);

        $plugin = OW::getPluginManager()->getPlugin(OW::getAutoloader()->getPluginKey($controller));

        if ( $action === null )
        {
            return strtolower($plugin->getModuleName()) . '/' . substr(UTIL_String::capsToDelimiter($className, '-'), 1);
        }

        return $moduleNamePrefix . strtolower($plugin->getPluginRoutePath()) . '/' . substr(UTIL_String::capsToDelimiter($className, '-'), 1) . '/' . UTIL_String::capsToDelimiter($action, '-') . '/' . $paramString;
    }

    /**
     * Returns dispatch params (controller, action, vars) for provided URI.
     * 
     * @throws Redirect404Exception
     * @param string $uri
     * @return array
     */
    public function getDispatchAttrs( $uri )
    {//TODO check if method is in try/catch
        $uriString = UTIL_String::removeFirstAndLastSlashes($uri);

        $uriArray = explode('/', $uriString);

        if ( sizeof($uriArray) < 2 )
        {
            throw new Redirect404Exception('Invalid uri was provided for routing!');
        }

        $controllerNamePrefixAdd = '';

        if ( strstr($uriArray[0], '-') )
        {
            $uriPartArray = explode('-', $uriArray[0]);
            $uriArray[0] = $uriPartArray[1];
            $controllerNamePrefixAdd = strtoupper($uriPartArray[0]);
        }

        $dispatchAttrs = array();

        $classPrefix = null;

        $arraySize = sizeof($uriArray);

        for ( $i = 0; $i < $arraySize; $i++ )
        {
            if ( $i === 0 )
            {
                try
                {
                    $classPrefix = strtoupper(OW::getPluginManager()->getPluginKey($uriArray[$i])) . '_' . $controllerNamePrefixAdd . $this->controllerNamePrefix;
                }
                catch ( InvalidArgumentException $e )
                {
                    throw new Redirect404Exception('Invalid uri was provided for routing!');
                }

                continue;
            }

            if ( $i === 1 )
            {
                if ( $classPrefix === null )
                {
                    throw new Redirect404Exception('Invalid uri was provided for routing!');
                }

                $ctrClass = $classPrefix . '_' . UTIL_String::delimiterToCaps('-' . $uriArray[$i], '-');

                try {
                    if (!OW::getStorage()->fileExists(OW::getAutoloader()->getClassPath($ctrClass))) {
                        throw new Redirect404Exception('Invalid uri was provided for routing!');
                    }
                }catch ( Exception $e ) {
                    throw new Redirect404Exception('Invalid uri was provided for routing!');
                }

                $dispatchAttrs['controller'] = $ctrClass;
                continue;
            }

            if ( $i === 2 )
            {
                $dispatchAttrs['action'] = UTIL_String::delimiterToCaps($uriArray[$i], '-');
                continue;
            }

            if ( $i % 2 !== 0 )
            {
                $dispatchAttrs['vars'][$uriArray[$i]] = null;
            }
            else
            {
                $dispatchAttrs['vars'][$uriArray[$i - 1]] = $uriArray[$i];
            }
        }

        return $dispatchAttrs;
    }
}

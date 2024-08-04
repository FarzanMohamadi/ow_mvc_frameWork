<?php
/**
 * The class replaces standard PHP error/exception handlers with custom ones,
 * allowing better error management.
 *
 * @package ow_core
 * @since 1.0
 */
class OW_ErrorManager
{
    /**
     * Singleton instance.
     *
     * @var OW_ErrorManager
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     * @param bool $debugMode
     * @return OW_ErrorManager
     */
    public static function getInstance( $debugMode = true )
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self($debugMode);
        }

        return self::$classInstance;
    }
    /**
     * @var boolean
     */
    private $debugMode;

    /**
     * OW_ErrorManager constructor.
     * @param $debugMode
     */
    private function __construct( $debugMode )
    {
        $this->debugMode = (bool) $debugMode;

        // set custom error and exception interceptors
        set_error_handler(array($this, 'errorHandler'));
        set_exception_handler(array($this, 'exceptionHandler'));

        // set error reporting level
        error_reporting(-1);
    }

    /**
     * Custom error handler.
     *
     * @param integer $errno
     * @param string $errString
     * @param string $errFile
     * @param integer $errLine
     * @return boolean
     */
    public function errorHandler( $errno, $errString, $errFile, $errLine )
    {
        // ignore if line is prefixed by `@`
        if ( error_reporting() === 0 )
        {
            return true;
        }

        $data = array(
            'message' => $errString,
            'file' => $errFile,
            'line' => $errLine
        );

        //temp fix
        $e_depricated = defined('E_DEPRECATED') ? E_DEPRECATED : 0;

        switch ( $errno )
        {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
            case $e_depricated:

                $data['type'] = 'Notice';

                if ( $this->debugMode )
                {
                    $this->handleShow($data);
                }
                else
                {
                    $this->handleIgnore($data);
                }
                break;

            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_CORE_WARNING:
                $data['type'] = 'Warning';

                if ( $this->debugMode )
                {
                    $this->handleShow($data);
                }
                else
                {
                    $this->handleIgnore($data);
                }
                break;

            default:
                $data['type'] = 'Error';

                if ( $this->debugMode )
                {
                    $this->handleDie($data);
                }
                else
                {
                    $this->handleRedirect($data);
                }
                break;
        }

        return true;
    }

    /**
     * Custom exception handler.
     *
     * @param $e
     */
    public function exceptionHandler( $e )
    {
        $data = array(
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'type' => 'Error',
            'class' => get_class($e)
        );

        if($e instanceof InvalidArgumentException){
            $data['message'] = 'Invalid Argument: ' . $data['message'];
        }

        if ( $this->debugMode )
        {
            $this->handleDie($data);
        }
        else
        {
            $this->handleRedirect($data);
        }
    }


    private function handleShow( $data )
    {
        UTIL_Debug::printDebugMessage($data);
        $this->handleLog($data);
    }

    private function handleDie( $data )
    {
        UTIL_Debug::printDebugMessage($data);
        $this->handleLog($data);

        OW::getEventManager()->trigger(new OW_Event('core.emergency_exit', $data));
        exit;
    }

    private function handleRedirect( $data )
    {
        $this->handleLog($data);
        OW::getEventManager()->trigger(new OW_Event('core.emergency_exit', $data));

        header("HTTP/1.1 500 Internal Server Error");
        header('Location: ' . OW_URL_HOME . 'e500.php');
    }

    private function handleIgnore( $data )
    {
        $this->handleLog($data);
        return;
    }

    private function handleLog( $data )
    {
        $trace = !empty($data['trace']) ? ' Trace: [' . str_replace(PHP_EOL, ' | ', $data['trace']) . ']' : "";
        $message = 'Message: ' . $data['message'] . ' File: ' . $data['file'] . ' Line:' . $data['line'] . $trace;
        OW::getLogger()->writeLog( $data['type'], $message);
    }

    public function debugBacktrace( )
    {
        $stack = '';
        $i = 1;
        $trace = debug_backtrace();
        unset($trace[0]);

        foreach ( $trace as $node )
        {
            $stack .=  "#$i " . (isset($node['file']) ? $node['file'] : '') . (isset($node['line']) ? "(" . $node['line'] . "): " : '');
            if ( isset($node['class']) )
            {
                $stack .= $node['class'] . "->";
            }
            $stack .= $node['function'] . "()" . PHP_EOL;
            $i++;
        }

        return $stack;
    }
}

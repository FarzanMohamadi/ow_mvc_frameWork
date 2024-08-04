<?php
/**
 * @package ow_utilities
 * @since 1.0
 */
final class UTIL_Debug
{
    private static $pvOutput;
    private static $pvObjects;
    private static $pvDepth = 10;
    private static $showed_once=false;

    public static function varDump( $var, $exit = false )
    {
        self::addDebugStyles();

        self::$pvOutput = '';
        self::$pvObjects = array();
        self::dumper($var, 0);

        $debugString = '
    	<div class="ow_debug_cont">
    		<div class="ow_debug_body">
    			<div class="ow_debug_cap vardump">OW Debug - Vardump</div>
    			<div>
    				<pre class="vardumper">' . self::$pvOutput .
            "\n\n" . '<b>Type:</b> <span style="color:red;">' . ucfirst(gettype($var)) . "</span>" . '
    				</pre>
    			</div>
    		</div>
    	</div>
    	';

        echo $debugString;

        if ( $exit )
        {
            exit;
        }
    }

    private static function dumper( $var, $level )
    {
        switch ( gettype($var) )
        {
            case 'boolean':
                self::$pvOutput .= '<span class="bool">' . ( $var ? 'true' : 'false' ) . '</span>';
                break;

            case 'integer':
                self::$pvOutput .= '<span class="number">' . $var . '</span>';
                break;

            case 'double':
                self::$pvOutput .= '<span class="number">' . $var . '</span>';
                break;

            case 'string':
                self::$pvOutput .= '<span class="string">' . htmlspecialchars($var) . '</span>';
                break;

            case 'resource':
                self::$pvOutput .= '{resource}';
                break;

            case 'NULL':
                self::$pvOutput .= '<span class="null">null</span>';
                break;

            case 'unknown type':
                self::$pvOutput .= '{unknown}';
                break;

            case 'array':
                if ( self::$pvDepth <= $level )
                {
                    self::$pvOutput .= '<span class="array">array(...)</span>';
                }
                else if ( empty($var) )
                {
                    self::$pvOutput .= '<span class="array">array()</span>';
                }
                else
                {
                    $keys = array_keys($var);
                    $spaces = str_repeat(' ', ($level * 4));
                    self::$pvOutput .= '<span class="array">array</span>' . "\n" . $spaces . '(';

                    foreach ( $keys as $key )
                    {
                        self::$pvOutput .= "\n" . $spaces . "    [" . $key . "] => ";
                        self::$pvOutput .= self::dumper($var[$key], ($level + 1));
                    }
                    self::$pvOutput .= "\n" . $spaces . ')';
                }
                break;

            case 'object':
                if ( ( $id = array_search($var, self::$pvObjects, true)) !== false )
                {
                    self::$pvOutput .= get_class($var) . '#' . ($id + 1) . '(...)';
                }
                else if ( self::$pvDepth <= $level )
                {
                    self::$pvOutput .= get_class($var) . '(...)';
                }
                else
                {
                    $id = array_push(self::$pvObjects, $var);
                    $className = get_class($var);
                    $members = (array) $var;
                    $keys = array_keys($members);
                    $spaces = str_repeat(' ', ($level * 4));
                    self::$pvOutput .= '<span class="class">' . "$className</span>#$id\n" . $spaces . '(';

                    foreach ( $keys as $key )
                    {
                        $keyDisplay = strtr(trim($key) . '</span>', array("\0" => ':<span class="class_prop">'));
                        self::$pvOutput .= "\n" . $spaces . "    [$keyDisplay] => ";
                        self::$pvOutput .= self::dumper($members[$key], ($level + 1));
                    }

                    self::$pvOutput .= "\n" . $spaces . ')';
                }
                break;
        }
    }

    public static function printDebugMessage( $data )
    {
        if(OW::getRequest()->isAjax()){
            OW::getLogger()->writeLog(OW_Log::WARNING, 'ajax_debug_notice', $data);
            return;
        }
        self::addDebugStyles();

        $url=OW_Log::getCurrentURL();
        $post_data=OW_Log::getPostData();
        if(empty($data['trace'])){
            $data['trace']=OW_Log::getShortStackTrace();
        }

        $debugString = '
    		<div class="ow_debug_cont">
    			<div class="ow_debug_body">
    				<div class="ow_debug_cap ' . strtolower($data['type']) . '">OW Debug - ' . $data['type'] . '</div>
    				<table>
    					<tr><td>Message:</td><td>' . $data['message'] . '</td></tr>
    					<tr><td>File:</td><td>' . $data['file'] . '</td></tr>
    					<tr><td>Line:</td><td>' . $data['line'] . '</td></tr>
    					<tr><td>URL:</td><td>' . $url . '</td></tr>
    					<tr><td>POST data:</td><td>' .print_r($post_data, true) . '    </td></tr>'
    					. (!empty($data['trace'])?'
                        <tr><td>Trace:</td><td><pre>' . $data['trace'] . '</pre></td></tr>':'')
                        . (!empty($data['class'])?'
                        <tr><td>Type:</td><td><pre>' . $data['class'] . '</pre></td></tr>':'').'
    				</table>
    			</div>
    		</div>
    		';

        echo $debugString;
    }

    private static function addDebugStyles()
    {
        if(self::$showed_once){
            return;
        }
        self::$showed_once=true;
        echo '
    	<style>
    		.ow_debug_cont{padding:15px 0;width:80%;margin:0 auto;}
    		.ow_debug_body{direction: ltr;z-index: 1000;position: relative;background:#fff;border:4px double;padding:5px;border-radius: 14px;}
    		.ow_debug_cap{font:bold 13px Tahoma;color:#fff;padding:5px;border:1px solid #000;width:250px;margin-top:-20px;border-radius: 10px;margin-bottom: 10px;}
    		.ow_debug_body .debug, .ow_debug_body .info, .ow_debug_body .notice{background:#fdf403;color:#555;}
    		.ow_debug_body .warning{background:#f8b423;color:#555;}
    		.ow_debug_body .error{background:#C13F32;color:#fff;}
    		.ow_debug_body .critical, .ow_debug_body .alert, .ow_debug_body .emergency{background:#f00;color:#fff;}
    		.ow_debug_body .vardump{background:#333;color:#fff;}
    		.vardumper .string{color:green}
    		.vardumper .null{color:blue}
    		.vardumper .array{color:blue}
            .vardumper .bool{color:blue}
    		.vardumper .property{color:brown}
    		.vardumper .number{color:red}
            .vardumper .class{color:black;}
            .vardumper .class_prop{color:brown;}
    	</style>
    	';
    }
}

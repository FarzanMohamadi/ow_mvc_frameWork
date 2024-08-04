<?php
define('_OW_', true);

define('DS', DIRECTORY_SEPARATOR);

define('OW_DIR_ROOT', dirname(__FILE__) . DS);

require_once(OW_DIR_ROOT . 'ow_includes' . DS . 'init.php');
$session = OW_Session::getInstance();
$session->start();
$errorDetails = '';

if ( $session->isKeySet('errorData') )
{
    $errorData = unserialize($session->get('errorData'));
    $trace = '';

    if ( !empty($errorData['trace']) )
    {
        $trace = '<tr>
                        <td class="lbl">Trace:</td>
                        <td class="cnt">' . $errorData['trace'] . '</td>
                </tr>';
    }

    $errorDetails = '<div style="margin-top: 30px;">
            <b>Error details</b>:
            <table style="font-size: 13px;">
                <tbody>
                <tr>
                        <td class="lbl">Type:</td>
                        <td class="cnt">' . $errorData['type'] . '</td>
                </tr>
                <tr>
                        <td class="lbl">Message:</td>
                        <td class="cnt">' . $errorData['message'] . '</td>
                </tr>
                <tr>
                        <td class="lbl">File:</td>
                        <td class="cnt">' . $errorData['file'] . '</td>
                </tr>
                <tr>
                        <td class="lbl">Line:</td>
                        <td class="cnt">' . $errorData['line'] . '</td>
                </tr>
                ' . $trace . '
        </tbody></table>
        </div>';
}

$output = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  </head>
  <body style="padding-top:10px; padding-right: 10px; font:12px Tahoma; direction: rtl; text-align: right;">
  <div style="border-radius: 10px; -moz-border-radius: 10px; -webkit-border-radius: 10px; text-align: center; padding: 15px; display: block; background-color: lightblue">
  <a style="background-color: #0042b5; color: white; text-decoration: none; padding: 10px; border-radius: 10px; -moz-border-radius: 10px; -webkit-border-radius: 10px;" href="'.OW_URL_HOME.'">
    بازگشت به صفحه اصلی
    </a>
    <br/>
    <div style="text-align: right; margin-top: 15px; display: block; border-bottom: 1px solid #666; padding-bottom: 6px; margin-bottom: 8px;">
    خطایی در سامانه رخ داده است. (خطای 500)
    </div><br/>
    <div style="text-align: right; font-size: 13px; margin-bottom: 4px;">
    اگر کاربر مدیر هستید،
    <a href="javascript://" onclick="getElementById(\'hiddenNode\').style.display=\'block\'">
        برای مشاهده خطا اینجا را کلیک کنید.
    </a></div>
    <div style="text-align: right; font-size: 13px; display: none;" id="hiddenNode">
        <div style="margin-top: 30px;">
    	<b style="line-height: 24px;">
    	    خطایی در سامانه وجود دارد
        </b>!<br/>
    	به منظور شناسایی خطا، مراحل زیر را طی کنید:
    	<br/>
        - فایل <i>config.php</i> را باز کرده و مد دیباگ را روشن نمایید.<br/>
 		- سناریو خطا را مجددا بررسی نمایید.
       </div>
        ' . $errorDetails . '
    </div>
    </div>
  </body>
</html>
';

echo $output;


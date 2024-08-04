<?php
define('_OW_', true);

define('DS', DIRECTORY_SEPARATOR);

define('OW_DIR_ROOT', dirname(__FILE__) . DS);

require_once 'ow_includes/config.php';
require_once 'ow_libraries/securimage/securimage.php';

require_once(OW_DIR_ROOT . 'ow_includes' . DS . 'init.php');

OW::getSession()->start();

try {
    // Passing array of options to the constructor
    $options = array('no_session'   => false /* dont use sessions */
        //,'use_database' => true /* use sqlite db */
        //,'captcha_type' => Securimage::SI_CAPTCHA_MATHEMATIC /* use math captcha */,
    );
    $img = new securimage($options);
}catch (Exception $e){
    header('Location: '.OW_URL_HOME.'404');
    exit();
}
//Change some settings 
$img->image_width = !empty($_GET['width']) && (int) $_GET['width'] < 500 ? (int) $_GET['width'] : 200;
$img->image_height = !empty($_GET['height']) &&(int) $_GET['height'] < 200 ? (int) $_GET['height'] : 68;
$img->ttf_file =  OW_DIR_ROOT.'ow_libraries/securimage/AHGBold.ttf';
$img->perturbation = 0.45;
$img->image_bg_color = new Securimage_Color(0xf6, 0xf6, 0xf6);
$img->text_angle_minimum = -5;
$img->text_angle_maximum = 5;
$img->use_transparent_text = true;
$img->text_transparency_percentage = 30; // 100 = completely transparent
$img->num_lines = 7;
$img->line_color = new Securimage_Color("#B0D1F2");
$img->signature_color = new Securimage_Color("#B0D1F2");
$img->text_color = new Securimage_Color("#000");
$img->use_wordlist = true;

$img->show();


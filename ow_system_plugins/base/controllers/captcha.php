<?php
/**
 * Captcha controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_Captcha extends OW_ActionController
{
    const CAPTCHA_WIDTH = 200;
    const CAPTCHA_HEIGHT = 68;

    public function __construct()
    {
        parent::__construct();

        require_once OW_DIR_LIB . 'securimage/securimage.php';
    }

    public function index( $params )
    {
        // Passing array of options to the constructor
        $options = array('no_session'   => false /* dont use sessions */
            //,'use_database' => true /* use sqlite db */
            //,'captcha_type' => Securimage::SI_CAPTCHA_MATHEMATIC /* use math captcha */,
        );
        $img = new securimage($options);

        //Change some settings
        $img->image_width = !empty($_GET['width']) ? (int) $_GET['width'] : self::CAPTCHA_WIDTH;
        $img->image_height = !empty($_GET['height']) ? (int) $_GET['height'] : self::CAPTCHA_HEIGHT;
        $img->perturbation = 0.45;
        $img->image_bg_color = new Securimage_Color(0xf6, 0xf6, 0xf6);
        $img->text_angle_minimum = -5;
        $img->text_angle_maximum = 5;
        $img->use_transparent_text = true;
        $img->text_transparency_percentage = 30; // 100 = completely transparent
        $img->num_lines = 7;
        $img->line_color = new Securimage_Color("#7B92AA");
        $img->signature_color = new Securimage_Color("#7B92AA");
        $img->text_color = new Securimage_Color("#7B92AA");
        $img->use_wordlist = true;

        $img->show();
        exit;
    }

    public function ajaxResponder()
    {
        if ( empty($_POST["command"]) || !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = (string) $_POST["command"];

        switch ( $command )
        {
            case 'checkCaptcha':

                $value = $_POST["value"];

                $result = UTIL_Validator::isCaptchaValid($value);

                if ( $result )
                {
                    OW::getSession()->set('securimage_code_value', $value);
                }

                echo json_encode(array('result' => $result));

                break;
        }
        
        exit();
    }
}


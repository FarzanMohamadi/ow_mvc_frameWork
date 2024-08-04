<?php
//require_once(OW_DIR_LIB . 'smarty3' . DS . 'Smarty.class.php');


/**
 * Smarty class.
 *
 * @package ow_core
 * @since 1.0
 */
class OW_Smarty extends Smarty
{

    public function __construct()
    {
        parent::__construct();

        $this->compile_check = false;
        $this->force_compile = false;
        $this->caching = false;
        $this->debugging = false;

        if ( OW_DEV_MODE )
        {
            $this->compile_check = true;
            $this->force_compile = true;
        }

        $this->cache_dir = OW_DIR_SMARTY . 'cache' . DS;
        $this->compile_dir = OW_DIR_SMARTY . 'template_c' . DS;
        $this->addPluginsDir(OW_DIR_SMARTY . 'plugin' . DS);
        $this->enableSecurity('OW_Smarty_Security');
    }
}

class OW_Smarty_Security extends Smarty_Security
{

    public function __construct( $smarty )
    {
        parent::__construct($smarty);
        $this->secure_dir = array(OW_DIR_THEME, OW_DIR_SYSTEM_PLUGIN, OW_DIR_PLUGIN);
        $this->php_functions = array('array', 'list', 'isset', 'empty', 'count', 'sizeof', 'in_array', 'is_array', 'true', 'false', 'null', 'strstr');
        $this->php_modifiers = array('count');
        $this->allow_constants = false;
        $this->allow_super_globals = false;
        $this->static_classes = null;
    }
}
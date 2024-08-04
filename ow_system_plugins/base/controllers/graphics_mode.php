<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class BASE_CTRL_GraphicsMode extends OW_ActionController
{

    public function reset_static( )
    {
        FRMSecurityProvider::updateStaticFiles(true);
        exit(json_encode(['reload'=>true]));
    }

    public function reset_template_c( )
    {
        FRMSecurityProvider::updateSmartyTemplates();
        exit(json_encode(['reload'=>true]));
    }

    public function reset_translations( )
    {
        FRMSecurityProvider::updateLanguages(true);
        exit(json_encode(['reload'=>true]));
    }
}
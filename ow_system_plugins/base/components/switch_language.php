<?php
class BASE_CMP_SwitchLanguage extends OW_Component
{
    /**
     * Constructor.
     *
     */
    public function __construct($languages)
    {
        parent::__construct();

        $this->assign('languages', $languages);

    }

}

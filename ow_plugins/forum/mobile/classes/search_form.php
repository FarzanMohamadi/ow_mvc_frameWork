<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.mobile
 * @since 1.7.2
 */
class FORUM_MCLASS_SearchForm extends Form
{
    /**
     * Class constructor
     * 
     * @param string $name
     * @param string $keywordInvitation
     * @param string $url
     */
    public function __construct( $name, $keywordInvitation, $url ) 
    {
        parent::__construct($name);

        $this->setMethod(self::METHOD_GET);
        $this->setAction($url);

        // keyword
        $keywordField = new TextField('keyword');
        $keywordField->setHasInvitation(true);
        $keywordField->setInvitation($keywordInvitation);
        $this->addElement($keywordField);
    }
}
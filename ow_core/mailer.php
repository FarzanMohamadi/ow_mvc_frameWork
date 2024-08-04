<?php
/**
 * Mailer
 *
 * @package ow_core
 * @method static OW_Mailer getInstance()
 * @since 1.0
 */
class OW_Mailer
{
    use OW_Singleton;
    
    /**
     * 
     * @var BOL_MailService
     */
    private $maliService;
    
	/**
     * Constructor.
     *
     */
    private function __construct()
    {
        $this->maliService = BOL_MailService::getInstance();
    }
    
    /**
     * 
     * @param $state
     * @return BASE_CLASS_Mail
     */
    public function createMail()
    {
        return $this->maliService->createMail();
    }
    
    public function addToQueue( BASE_CLASS_Mail $mail )
    {
        $this->maliService->addToQueue($mail);
    }
    
    public function addListToQueue( array $list )
    {
        $this->maliService->addListToQueue($list);
    }
    
    public function send( BASE_CLASS_Mail $mail )
    {
        if ( $this->maliService->getTransfer() == BOL_MailService::TRANSFER_SMTP )
        {
            $this->maliService->addToQueue($mail);
        }
        else
        {
            $this->maliService->send($mail);    
        }
    }
    
    public function getEmailDomain()
    {
        return $this->maliService->getEmailDomain();
    }
}
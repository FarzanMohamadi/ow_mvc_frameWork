<?php
class FRMMASSMAILING_BOL_Service
{
    const CONF_MAIL_COUNT_ON_PAGE = 5;
    const ON_SEND_MASS_MAIL='massmailing.on.send.mass.mail';

    private static $classInstance;
    
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private $mailingDetailsDao;
    
    private function __construct()
    {
        $this->mailingDetailsDao = FRMMASSMAILING_BOL_MailingDetailsDao::getInstance();
    }

    public function onSendMassMail(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['roles']) && isset($params['title']) && isset($params['body'])){
            $roles= "";
            $title = $params['title'];
            $body = $params['body'];
            foreach($params['roles'] as $role){
                $roleLabel = OW::getLanguage()->text('base', 'authorization_role_' . $role);
                $roles = $roles . $roleLabel." - ";
            }
            $roles = substr($roles, 0, -3);
            $this->mailingDetailsDao->addMassMailingDetails($title,$body,$roles);
        }
    }

    public function getMassMailingDetailsData($page,$mailDataCount)
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $mailDataCount;
        }
        else
        {
            $config =  OW::getConfig();
            $count = $config->getValue('frmmassmailing', 'mail_view_count');
            $page = ( $page === null ) ? 1 : (int) $page;
            $first = ( $page - 1 ) * $count;
        }
        return $this->mailingDetailsDao->getMassMailingDetailsData($first,$count);
    }

    public function getMassMailingDetailsDataCount()
    {
        return $this->mailingDetailsDao->getMassMailingDetailsDataCount();
    }

    /**
     * @param $sectionId
     * @return array
     */
    public function getAdminSections($sectionId)
    {
        $sections = array();

        for ($i = 1; $i <= 2; $i++) {
            $sections[] = array(
                'sectionId' => $i,
                'active' => $sectionId == $i ? true : false,
                'url' => OW::getRouter()->urlForRoute('frmmassmailing.admin.section-id', array('sectionId' => $i)),
                'label' => $this->getPageHeaderLabel($i)
            );
        }
        return $sections;
    }

    public function getPageHeaderLabel($sectionId)
    {
        if ($sectionId == 1) {
            return OW::getLanguage()->text('frmmassmailing', 'mailingDetailsInfo');
        }
        else if ($sectionId == 2) {
            return OW::getLanguage()->text('frmmassmailing', 'viewCountSetting');
        }
    }
}

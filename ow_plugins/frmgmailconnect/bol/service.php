<?php
require_once OW_DIR_PLUGIN.'frmgmailconnect'.DS.'lib'.DS.'httpcurl.php';

class FRMGMAILCONNECT_BOL_Service
{
    private static $classInstance;

    /*
     *
     * Returns class instance
     *
     * @return FRMGMAILCONNECT_BOL_Service
     *
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    private $httpcurl;
    public $props;

    protected function __construct ()
    {
     $this->httpcurl = new HTTPCurl();
     $this->props = $this->getProperties ();
     $this->httpcurl->setUserAgent ('(Google connect/FRM)');
     $this->httpcurl->setSSLVerify (false);
     $this->httpcurl->setCache (false);
     $this->httpcurl->setHeaderBody (false);
    }

    public function findValue ($scan_array, $find_key)
    {
        $result = null;
        foreach ( $scan_array as $key => $val )
        {
            if (!strcasecmp($find_key,$key))
            {
              $result = $val;
              break;
            }
            else
            {
              if (is_array($val)) $result = $this->findValue ($val,$find_key);
            }
        }
        return $result;
    }



   public function getProperties ()
    {
     $owconfig = OW::getConfig();
     $props = new FRMGMAILCONNECT_BOL_Config ();
     $props->client_id = $owconfig->getValue ('frmgmailconnect','client_id');
     $props->client_secret = $owconfig->getValue ('frmgmailconnect','client_secret');
     $props->redirect_uri = OW::getRouter()->urlForRoute('frmgmailconnect_oauth');
     return $props;
    }

   public function saveProperties (FRMGMAILCONNECT_BOL_Config $props)
    {
     $owconfig = OW::getConfig();
     $owconfig->saveConfig ('frmgmailconnect','client_id',$props->client_id);
     $owconfig->saveConfig ('frmgmailconnect','client_secret',$props->client_secret);
     return true;
    }

    public function generateOAuthUri ()
    {
     $data = array (
       'scope'=>$this->getScope(),
       'redirect_uri'=>$this->props->redirect_uri,
       'response_type'=>'code',
       'client_id'=>$this->props->client_id
       );
     return $this->props->endpoint.'?'.http_build_query ($data);
    }

    private function getToken ($data)
    {
     $this->httpcurl->setUrl ($this->props->tokenpoint);
     $this->httpcurl->setPostData ($data);
     $this->httpcurl->execute();
     return json_decode ($this->httpcurl->content,true);
    }

    public function getUserInfo ($data)
    {
     $token = $this->findValue($this->getToken($data),'access_token');
     $this->httpcurl->setUrl ($this->props->userinfopoint.$token);
     $this->httpcurl->setPostMethod (false);
     $this->httpcurl->execute();
     return json_decode ($this->httpcurl->content,true);
    }

    public function getScope()
    {
     $email = 'https://www.googleapis.com/auth/userinfo.email';
     $profile= 'https://www.googleapis.com/auth/userinfo.profile';
     return $email.' '.$profile;
    }




    public function connectEventAddButton( BASE_CLASS_EventCollector $event )
    {
        $cssUrl = OW::getPluginManager()->getPlugin('frmgmailconnect')->getStaticCssUrl() . 'frmgmailconnect.css';
        OW::getDocument()->addStyleSheet($cssUrl);
        $button = new FRMGMAILCONNECT_CMP_ConnectButton();
        $event->add(array('iconClass' => 'ow_ico_signin_g', 'markup' => $button->render()));
    }

    public function connectAddAdminNotification( BASE_CLASS_EventCollector $e )
    {
        $language = OW::getLanguage();
        $configs = OW::getConfig()->getValues('frmgmailconnect');
        if ( empty($configs['client_id']) || empty($configs['client_secret']) )
        {
            $e->add($language->text('frmgmailconnect', 'admin_configuration_required_notification', array( 'href' => OW::getRouter()->urlForRoute('frmgmailconnect_admin_main') )));
        }
    }
    public function connectAddAccessException( BASE_CLASS_EventCollector $e ) {
        $e->add(array('controller' => 'FRMGMAILCONNECT_CTRL_Connect', 'action' => 'oauth'));

    }

    public function afterUserRegistered( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['method'] != 'google' )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $event = new OW_Event('feed.action', array(
            'pluginKey' => 'base',
            'entityType' => 'user_join',
            'entityId' => $userId,
            'userId' => $userId,
            'replace' => true,
        ), array(
            'string' => OW::getLanguage()->text('frmgmailconnect', 'feed_user_join'),
            'view' => array(
                'iconClass' => 'ow_ic_user'
            )
        ));
        OW::getEventManager()->trigger($event);
    }

    public function afterUserSynchronized( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !OW::getPluginManager()->isPluginActive('activity') || $params['method'] !== 'google' )
        {
            return;
        }
        $event = new OW_Event(OW_EventManager::ON_USER_EDIT, array('method' => 'native', 'userId' => $params['userId']));
        OW::getEventManager()->trigger($event);
    }

}
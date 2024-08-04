<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.1
 */
class BIRTHDAYS_CMP_AvatarUserList extends BASE_CMP_AvatarUserList
{
    protected $key;
    
    public function __construct( array $idList = array(), $key )
    {
        parent::__construct($idList);
        $this->key = $key;

        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir().'avatar_user_list.html');
    }
    
    public function onBeforeRender()
    {
        $this->customCssClass .= 'birthdays_avatar_list';
        
        parent::onBeforeRender();
    }
    
    public function getAvatarInfo( $idList )
    {
        $data = parent::getAvatarInfo($idList);
        
        $birthdays = BOL_QuestionService::getInstance()->getQuestionData($idList, array('birthdate'));
        
        foreach ( $data as $userId => $item )
        {
            $yearOld = '';
            
            if ( !empty($birthdays[$userId]['birthdate']) )
            {
                
                switch ( $this->key )
                {
                    case 'birthdays_today':
                        
                        $date = UTIL_DateTime::parseDate($birthdays[$userId]['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                        $yearOld =  UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']) . " " . OW::getLanguage()->text('base', 'questions_age_year_old');
                        
                    break;
                
                    case 'birthdays_this_week':    
                        
                        $date = UTIL_DateTime::parseDate($birthdays[$userId]['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                        $yearOld =  OW::getLanguage()->text('birthdays', 'birthday') . ' '. UTIL_DateTime::formatBirthdate($date['year'], $date['month'], $date['day']) . " ";
                        
                    break;
                }
            }
            
            if ( !empty($data[$userId]['title']) )
            {
                $data[$userId]['attrs'] = ' data-birthday="' . ((!empty($yearOld)) ? $yearOld : '') . '"';
            }
            else if( !empty($yearOld) )
            {
                $data[$userId]['attrs'] =  ' data-birthday="' . $yearOld . '"';
            }
        }
        
        OW::getDocument()->addOnloadScript("
                $('*[title]', $('.birthdays_avatar_list') ).each( function(i, o){
                    $(o).off('mouseenter');
                    $(o).on('mouseenter', function(){ 
                        var title = $(this).attr('title');
                        var birthday = $(this).data('birthday');
                        
                        if ( !birthday )
                        {
                            OW.showTip($(this), {timeout:200});
                        }
                        else if ( !title && birthday )
                        {
                            birthday = '<span class=\"ow_small\" style=\"font-weight:normal;\">' + birthday + '</span>';
                            
                            OW.showTip($(this), {timeout:200, show:birthday});
                        }
                        else
                        {
                            birthday = '<br><span class=\"ow_small\" style=\"font-weight:normal;\">' + birthday + '</span>';
                            
                            OW.showTip($(this), {timeout:200, show:title + birthday});
                        }
                     });
                    $(o).off('mouseleave');
                    $(o).on('mouseleave', function(){ OW.hideTip($(this)); });
            });" );
        
        return $data;
    }
}
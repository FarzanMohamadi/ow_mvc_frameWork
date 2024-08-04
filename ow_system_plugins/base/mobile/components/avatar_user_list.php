<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_MCMP_AvatarUserList extends BASE_CMP_AvatarUserList
{
    public function __construct( array $idList = array() )
    {
        parent::__construct($idList);
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCmpViewDir() . 'avatar_user_list.html');
    }

//    public function getAvatarInfo( $idList )
//    {
//        $data = parent::getAvatarInfo( $idList );
//
//        foreach ( $data as $userId => $value )
//        {
//            if ( !empty($data[$userId]['label']) )
//            {
//                $data[$userId]['label'] = mb_substr($data[$userId]['label'], 0, 1);
//            }
//        }
//
//        return $data;
//    }
}
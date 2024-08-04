<?php
class FRMGMAILCONNECT_CLASS_AuthAdapter extends OW_RemoteAuthAdapter
{

    public function __construct( $remoteId )
    {
        parent::__construct($remoteId, 'google');
    }
}

?>
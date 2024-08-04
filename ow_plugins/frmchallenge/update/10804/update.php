<?php
try {
    $authorization = OW::getAuthorization();
    $authorization->addAction('frmchallenge', 'add_solitary_challenge');
}
catch ( Exception $e ) { }

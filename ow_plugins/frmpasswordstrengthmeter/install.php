<?php
/**
 * 
 * All rights reserved.
 */

$config = OW::getConfig();

if ( !$config->configExists('frmpasswordstrengthmeter', 'minimumCharacter') )
{
    $config->addConfig('frmpasswordstrengthmeter', 'minimumCharacter', 8);
}
if ( !$config->configExists('frmpasswordstrengthmeter', 'minimumRequirementPasswordStrength') )
{
    $config->addConfig('frmpasswordstrengthmeter', 'minimumRequirementPasswordStrength', 3);
}

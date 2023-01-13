<?php
// -----
// Part of the "Image Handler" plugin for Zen Cart 1.5.5 and later.
// Copyright (c) 2017 Vinos de Frutas Tropicales
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// ----
// Initialize the plugin's observer ...
// 
$autoLoadConfig[0][] = array(
    'autoType' => 'class',
    'loadFile' => 'observers/admin.zcObserverEnvelope.php',
    'classPath'=>DIR_WS_CLASSES
);
$autoLoadConfig[199][] = array(
    'autoType' => 'classInstantiate',
    'className' => 'envelopeAdminObserver',
    'objectName' => 'envelopeAdminObserver'
);

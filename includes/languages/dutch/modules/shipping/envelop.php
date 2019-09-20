<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: envelop.php 1969 2005-09-13 06:57:21Z drbyte $
//

define('MODULE_SHIPPING_ENVELOP_TEXT_TITLE', 'Envelop'); // shows at checkout and invoice
define('MODULE_SHIPPING_ENVELOP_TEXT_DESCRIPTION', 'Wordt alleen getoond wanneer het totale volume van de producten in een envelop past (en het totale gewicht tussen het min en max gewicht ligt).');

// add description to catalog side :: paulm
define('MODULE_SHIPPING_ENVELOP_TEXT_DESCRIPTION_CATALOG', 'Zonder tracking en onverzekerd, wordt dus geheel voor uw eigen risico verzonden.'); 

define('MODULE_SHIPPING_ENVELOP_TEXT_WAY', 'brievenbus post'); // shows at checkout and invoice
define('MODULE_SHIPPING_ENVELOP_TEXT_WEIGHT', 'Gewicht');
define('MODULE_SHIPPING_ENVELOP_TEXT_AMOUNT', 'Aantal');
?>
<?php
/**
 * @package shippingMethod
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: envelop.php$
 */
/**
 * Enter description here...
 *
 */
class envelop extends base {
  /**
   * Enter description here...
   *
   * @var string
   */
  var $code;
  /**
   * Enter description here...
   *
   * @var string
   */
  var $title;
  /**
   * Enter description here...
   *
   * @var string
   */
  var $description;
  /**
   * Enter description here...
   *
   * @var string
   */
  var $icon;
  /**
   * Enter description here...
   *
   * @var boolean
   */
  var $enabled;
  /**
   * Enter description here...
   *
   * @return envelop
   */
  function __construct() {
    global $order, $db;
    //global $shipping_weight;
    $this->code = 'envelop';
    $this->title = MODULE_SHIPPING_ENVELOP_TEXT_TITLE;
    $this->description = MODULE_SHIPPING_ENVELOP_TEXT_DESCRIPTION;
    $this->sort_order = defined('MODULE_SHIPPING_ENVELOP_SORT_ORDER') ? MODULE_SHIPPING_ENVELOP_SORT_ORDER : null;
    if (null === $this->sort_order) return false;

    $this->icon = '';
    $this->tax_class = MODULE_SHIPPING_ENVELOP_TAX_CLASS;
    $this->tax_basis = MODULE_SHIPPING_ENVELOP_TAX_BASIS;
    // disable only when entire cart is free shipping
    if (zen_get_shipping_enabled($this->code)) {
      $this->enabled = ((MODULE_SHIPPING_ENVELOP_STATUS == 'True') ? true : false);
    }

    if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_ENVELOP_ZONE > 0) ) {
      $check_flag = false;
      $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . "
                             where geo_zone_id = '" . MODULE_SHIPPING_ENVELOP_ZONE . "'
                             and zone_country_id = " . (int)$order->delivery['country']['id'] . "
                             order by zone_id");
      while (!$check->EOF) {
        if ($check->fields['zone_id'] < 1) {
          $check_flag = true;
          break;
        } elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
          $check_flag = true;
          break;
        }
        $check->MoveNext();
      }

      if ($check_flag == false) {
        $this->enabled = false;
      }
    }

    //MODULE_SHIPPING_ENVELOP_UPPER_WEIGHT_LIMIT
    if($_SESSION['cart']->weight > MODULE_SHIPPING_ENVELOP_UPPER_WEIGHT_LIMIT){
        $this->enabled = false;
    }

    if(!($this->cart_fits_envelope())){
        $this->enabled = false;
    }

  }

  /**
   * Disables this module if the total volume of items in the cart exceeds envelope volume
   *
   * @param unknown_type $method
   * @return boolean
   */

  function cart_fits_envelope(){
    global $db;
    // products_qty_envelope = VARCHAR allows it to be easily updated to '' (for MODULE_SHIPPING_ENVELOP_ENABLE_BY_DEFAULT)
    //todo: add MODULE_SHIPPING_ENVELOP_ENABLE_BY_DEFAULT 'True' / 'False' setting to module admin settings
    //define('MODULE_SHIPPING_ENVELOP_ENABLE_BY_DEFAULT','False');

    //$products_qty_envelope = '';
    $contents = $_SESSION['cart']->contents;
    // the volume of all products in cart relative to envelope volume (envelope volume = 1)
    $volume = 0;

    if(!(count($contents) > 0)) return false;

    foreach($contents as $products_id => $value){
      // get number that fits envelope for this product from db
      if(($value['qty'] > 0)&&($products_id > 0)){
        $result = $db->Execute("select products_qty_envelope from " . TABLE_PRODUCTS . " where products_id = '" . $products_id . "'");

        $products_qty_envelope = $result->fields['products_qty_envelope'];

        if((MODULE_SHIPPING_ENVELOP_ENABLE_BY_DEFAULT == 'False')&&((int)$products_qty_envelope == 0)){
          // we found a product that is not set or does not fit
          return false;
        }

        // add checks for virtual products etc?
        if(($products_qty_envelope === '0')){
          // we found a product that is set but does not fit, no need to query more
          return false;
        }

        if($products_qty_envelope > 0){
          $products_volume = $value['qty']/(int)$products_qty_envelope;
          $volume = $volume + $products_volume;
        }
      }
      // no need to query more products if the volume already exceeds the max value
      if($volume > 1) return false;

    }
    //echo $volume; // debug
    if($volume <= 1){
      // the total volume fits envelope
      return true;
    }
    // ??
    return false;
  }


  /**
   * Enter description here...
   *
   * @param unknown_type $method
   * @return unknown
   */
  function quote($method = '') {
    global $order, $shipping_num_boxes, $total_count;
    // we use envelope weight, not box weight
    //if(!defined('MODULE_SHIPPING_ENVELOP_WEIGHT')) define('MODULE_SHIPPING_ENVELOP_WEIGHT','40');
    $shipping_weight = $_SESSION['cart']->weight + (int)MODULE_SHIPPING_ENVELOP_WEIGHT;
    // shipping adjustment
    switch (MODULE_SHIPPING_ENVELOP_MODE) {
      case ('price'):
        $order_total = $_SESSION['cart']->show_total() - $_SESSION['cart']->free_shipping_prices() ;
        break;
      case ('weight'):
        $order_total = $shipping_weight;
        break;
      case ('item'):
        $order_total = $total_count - $_SESSION['cart']->free_shipping_items();
        break;
    }

    $envelop_cost = preg_split("/[:,]/" , MODULE_SHIPPING_ENVELOP_COST);
    $size = sizeof($envelop_cost);
    for ($i=0, $n=$size; $i<$n; $i+=2) {
      if (round($order_total,9) <= $envelop_cost[$i]) {
        $shipping = $envelop_cost[$i+1];
        break;
      }
    }

    if (MODULE_SHIPPING_ENVELOP_MODE == 'weight') {
      $shipping = $shipping * $shipping_num_boxes;
      // show boxes if weight
      switch (SHIPPING_BOX_WEIGHT_DISPLAY) {
        case (0):
        $show_box_weight = '';
        break;
        case (1):
        $show_box_weight = ' (' . $shipping_num_boxes . ' ' . TEXT_SHIPPING_BOXES . ')';
        break;
        case (2):
        $show_box_weight = ' (' . number_format($shipping_weight * $shipping_num_boxes,2) . TEXT_SHIPPING_WEIGHT . ')';
        break;
        default:
        $show_box_weight = ' (' . $shipping_num_boxes . ' x ' . number_format($shipping_weight,2) . TEXT_SHIPPING_WEIGHT . ')';
        break;
      }
    }

    $this->quotes = array('id' => $this->code,
    'module' => MODULE_SHIPPING_ENVELOP_TEXT_TITLE . $show_box_weight,
    'methods' => array(array('id' => $this->code,
    'title' => MODULE_SHIPPING_ENVELOP_TEXT_WAY,
    'cost' => $shipping + MODULE_SHIPPING_ENVELOP_HANDLING)));

    if ($this->tax_class > 0) {
      $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
    }

    if (zen_not_null($this->icon)) $this->quotes['icon'] = zen_image($this->icon, $this->title);
    // bof add description to catalog side :: paulm
    if(defined('MODULE_SHIPPING_ENVELOP_TEXT_DESCRIPTION_CATALOG')) $this->quotes['helptext'] = MODULE_SHIPPING_ENVELOP_TEXT_DESCRIPTION_CATALOG;
    // eof add description to catalog side :: paulm
    return $this->quotes;
  }
  /**
   * Enter description here...
   *
   * @return unknown
   */
  function check() {
    global $db;
    if (!isset($this->_check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_ENVELOP_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    return $this->_check;
  }
  /**
   * Enter description here...
   *
   */
  function install() {
    global $db;
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Table Method', 'MODULE_SHIPPING_ENVELOP_STATUS', 'False', 'Do you want to offer envelop rate shipping?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

    //MODULE_SHIPPING_ENVELOP_WEIGHT
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Envelope weight', 'MODULE_SHIPPING_ENVELOP_WEIGHT', '40', 'The weight of an envelope + invoice + label etc.', '6', '0', now())");

    //MODULE_SHIPPING_ENVELOP_UPPER_WEIGHT_LIMIT
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Upper weight limit', 'MODULE_SHIPPING_ENVELOP_UPPER_WEIGHT_LIMIT', '2500', 'The maximum total weight of products in the cart (module is disabled if the total weight exceeds this limit).', '6', '0', now())");

    //define('MODULE_SHIPPING_ENVELOP_ENABLE_BY_DEFAULT','False');
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable by default.', 'MODULE_SHIPPING_ENVELOP_ENABLE_BY_DEFAULT', 'False', 'When set to True this module will stay enabled even if products_qty_envelope of (some of) the products in the cart is not set.', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Shipping Table', 'MODULE_SHIPPING_ENVELOP_COST', '100:2.1008,250:3.319,500:3.5714,3000:4.6218', 'The shipping cost is based on the total cost or weight of items or count of the items. Example: 25:8.50,50:5.50,etc.. Up to 25 charge 8.50, from there to 50 charge 5.50, etc', '6', '0', 'zen_cfg_textarea(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Table Method', 'MODULE_SHIPPING_ENVELOP_MODE', 'weight', 'The shipping cost is based on the order total or the total weight of the items ordered or the total number of items orderd.', '6', '0', 'zen_cfg_select_option(array(\'weight\', \'price\', \'item\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Fee', 'MODULE_SHIPPING_ENVELOP_HANDLING', '0', 'Handling fee for this shipping method.', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_ENVELOP_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tax Basis', 'MODULE_SHIPPING_ENVELOP_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_ENVELOP_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_ENVELOP_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
  }
  /**
   * Enter description here...
   *
   */
  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }
  /**
   * Enter description here...
   *
   * @return unknown
   */
  function keys() {
    //MODULE_SHIPPING_ENVELOP_UPPER_WEIGHT_LIMIT
    //MODULE_SHIPPING_ENVELOP_WEIGHT
    return array('MODULE_SHIPPING_ENVELOP_STATUS', 'MODULE_SHIPPING_ENVELOP_ENABLE_BY_DEFAULT', 'MODULE_SHIPPING_ENVELOP_WEIGHT', 'MODULE_SHIPPING_ENVELOP_UPPER_WEIGHT_LIMIT', 'MODULE_SHIPPING_ENVELOP_COST', 'MODULE_SHIPPING_ENVELOP_MODE', 'MODULE_SHIPPING_ENVELOP_HANDLING', 'MODULE_SHIPPING_ENVELOP_TAX_CLASS', 'MODULE_SHIPPING_ENVELOP_TAX_BASIS', 'MODULE_SHIPPING_ENVELOP_ZONE', 'MODULE_SHIPPING_ENVELOP_SORT_ORDER');
  }
}

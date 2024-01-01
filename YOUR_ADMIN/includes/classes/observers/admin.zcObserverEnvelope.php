<?php

if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG == false) {
    die('Invalid access.');
}

class envelopeAdminObserver extends base
{

    function __construct()
    {
        $this->attach($this, array('NOTIFY_MODULES_UPDATE_PRODUCT_END', 'NOTIFY_ADMIN_PRODUCT_COLLECT_INFO_EXTRA_INPUTS'));
    }

    function update(&$class, $eventID, $p1, &$p2, &$p3, &$p4)
    {
        switch ($eventID) {
            case 'NOTIFY_MODULES_UPDATE_PRODUCT_END':
                global $db;

                $products_id = $p1['products_id'];

                $action = $p1['action'];
                if ($action === 'insert_product') {
                    $sql_data_array = [
                        'products_qty_envelope' => (int)$_POST['products_qty_envelope']
                    ];
                    zen_db_perform(TABLE_PRODUCTS, $sql_data_array,);
                } elseif ($action === 'update_product') {
                    $sql_data_array = [
                        'products_qty_envelope' => (int)$_POST['products_qty_envelope']
                    ];
                    zen_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = " . (int)$products_id);
                }
                break;
            case 'NOTIFY_ADMIN_PRODUCT_COLLECT_INFO_EXTRA_INPUTS':
                $p2[] = [
                    'label' => [
                        'text' => 'TEXT_PRODUCTS_QTY_ENVELOPE',
                        'field_name' => 'products_qty_envelope',
                        'addl_class' => '',
                        'parms' => ''
                    ],
                    'input' => zen_draw_input_field('products_qty_envelope', (!empty($p1->products_qty_envelope) ? $p1->products_qty_envelope : 0), zen_set_field_length(TABLE_PRODUCTS, 'products_qty_envelope') . ' class="form-control" id="products_qty_envelope"')
                ];
                break;

            default:
                break;
        }
    }
}

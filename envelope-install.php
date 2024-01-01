// ALTER TABLE products ADD products_qty_envelope VARCHAR( 32 ) NULL AFTER products_weight;
<?php

$products = $db->Execute("SELECT prducts_id, products_qty_envelope
                          FROM " . TABLE_PRODUCTS);

foreach ($products as $product) {
  
}
?>
ALTER TABLE `products` CHANGE `products_qty_envelope` `products_qty_envelope` INT NOT NULL DEFAULT '0';

ALTER TABLE `products` ADD `products_qty_envelope` INT NOT NULL DEFAULT '0' AFTER `products_weight`;
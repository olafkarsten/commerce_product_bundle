<?php

/**
 * @file
 * Rules integration for product bundle.
 *
 * @addtogroup rules
 * @{
 */


/**
 * Implements hook_rules_action_info().
 */
function commerce_product_bundle_rules_action_info() {
  $actions = array();

  $actions['commerce_product_bundle_calculate_price'] = array(
    'label' => t('Calculate the price of a bundle product'),
    'parameter' => array(
      'line_item' => array(
        'type' => 'commerce_line_item',
        'label' => t('Line item'),
      ),
      'multiply_by_quantity' => array(
        'type' => 'boolean',
        'label' => t('Multiply by Quantity'),
        'description' => t('Should the price be multiply by the added quantity of the sub product.'),
      ),
    ),
    'group' => t('Commerce Product Bundle'),
  );



  return $actions;
}

/**
 * Rules action: Calculate the price for a bundle line item
 */
function commerce_product_bundle_calculate_price($line_item, $multiply_by_quantity) {
  $original_line_item_wrapper = entity_metadata_wrapper('commerce_line_item', $line_item);
  $original_line_item = $line_item;
  
  $order_id = $line_item->order_id;
  
  $order = commerce_order_load($order_id);
  
  if (empty($order)) {
    return;
  }
  $order_wrapper = entity_metadata_wrapper('commerce_order', $order);
  
  $unitprice = $original_line_item_wrapper->commerce_unit_price->amount->value();
  
  $found_bundle = false;
  
  foreach ($order_wrapper->commerce_line_items as $delta => $line_item_wrapper) {
    if ($line_item_wrapper->type->value() == 'bundle'
       && isset($line_item_wrapper->commerce_parent_line_item)
       && $line_item_wrapper->commerce_parent_line_item->line_item_id->value() == $original_line_item->line_item_id
    ) {
      if ($multiply_by_quantity) {
        $component_total = commerce_price_component_total($line_item_wrapper->commerce_total->value());
      }
      else {
        $component_total = commerce_price_component_total($line_item_wrapper->commerce_unit_price->value());
      }
      
      // TODO: Implement the calculation method here (from display settings)
      $unitprice += commerce_currency_convert(
        $component_total['amount'],
        $component_total['currency_code'],
        $original_line_item_wrapper->commerce_unit_price->currency_code->value()
      );
      
      // Combine the line item total's component prices into the unit price total.
      $original_line_item_wrapper->commerce_unit_price->data = commerce_price_components_combine(
        $original_line_item_wrapper->commerce_unit_price->value(),
        $line_item_wrapper->commerce_total->value()
      );
      $found_bundle = true;
    }
  }
  
  if ($found_bundle) {
    $original_line_item_wrapper->commerce_unit_price->amount = $unitprice;
  }
}



/**
 * @}
 */

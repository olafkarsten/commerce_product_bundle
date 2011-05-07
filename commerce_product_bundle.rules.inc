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
    
  //$unitprice = $original_line_item_wrapper->commerce_unit_price->amount->value();
  
  $unitprice = 0;
  
  $found_bundle = false;
  
  
  $sub_items = commerce_product_bundle_get_sub_line_items($line_item);
  
  // Reset the unit price to the product price
  $original_line_item_wrapper->commerce_unit_price = $original_line_item_wrapper->commerce_product->commerce_price->value();
    
  foreach ($sub_items as $item) {
    $line_item_wrapper = entity_metadata_wrapper('commerce_line_item', $item);
    
    if ($multiply_by_quantity) {
      $line_item_wrapper->commerce_total->amount = $item->quantity * $line_item_wrapper->commerce_unit_price->amount->value();
      $line_item_wrapper->commerce_total->currency_code = $line_item_wrapper->commerce_unit_price->currency_code->value();

      // Add the components multiplied by the quantity to the data array.
      $data = $line_item_wrapper->commerce_unit_price->data->value();

      if (empty($data['components'])) {
        $data['components'] = array();
      }
      else {
        foreach ($data['components'] as $key => $component) {
          $data['components'][$key]['price']['amount'] *= $item->quantity;
        }
      }

      // Set the updated data array to the total price.
      $line_item_wrapper->commerce_total->data = $data;

      $component_total = commerce_price_component_total($line_item_wrapper->commerce_total->value());
    }
    else {
      $component_total = commerce_price_component_total($line_item_wrapper->commerce_unit_price->value());
    }
    
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
  

  
  if ($found_bundle) {
    $original_line_item_wrapper->commerce_unit_price->amount = $unitprice;
  }
}

/**
 * @}
 */

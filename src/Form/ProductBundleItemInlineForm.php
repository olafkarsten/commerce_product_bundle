<?php

namespace Drupal\commerce_product_bundle\Form;

use Drupal\inline_entity_form\Form\EntityInlineForm;

/**
 * Defines the inline form for product bundle items.
 */
class ProductBundleItemInlineForm extends EntityInlineForm {

  /**
   * {@inheritdoc}
   */
  public function getTableFields($bundles) {
    $fields = parent::getTableFields($bundles);
    $fields['product'] = [
      'type' => 'field',
      'label' => t('Product'),
      'weight' => 2,
    ];
    $fields['unit_price'] = [
      'type' => 'field',
      'label' => t('Unit Price'),
      'weight' => 4,
    ];
    $fields['min_quantity'] = [
      'type' => 'field',
      'label' => t('Min Quantity'),
      'weight' => 5,
    ];
    $fields['max_quantity'] = [
      'type' => 'field',
      'label' => t('Max Quantity'),
      'weight' => 6,
    ];

    return $fields;
  }

}

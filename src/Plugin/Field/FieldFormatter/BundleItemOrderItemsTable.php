<?php

namespace Drupal\commerce_product_bundle\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'commerce_product_bundle_item_order_items_table' formatter.
 *
 * @FieldFormatter(
 *   id = "commerce_product_bundle_item_order_items_table",
 *   label = @Translation("commerce product bundle item order items table"),
 *   field_types = {
 *     "entity_reference",
 *   },
 * )
 */
class BundleItemOrderItemsTable extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
    $orderItem = $items->getEntity();
    $elements = [];
    $elements[0] = [
      '#type' => 'view',
      // @todo Allow the view to be configurable.
      '#name' => 'cbp_item_order_items_table',
      '#arguments' => [$orderItem->id()],
      '#embed' => TRUE,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $entity_type = $field_definition->getTargetEntityTypeId();
    $field_name = $field_definition->getName();
    return $entity_type == 'commerce_order_item' && $field_name == 'bundle_item_order_items';
  }

}

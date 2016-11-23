<?php

namespace Drupal\commerce_product_bundle\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'commerce_product_bundle_item_selection' field type.
 *
 * @FieldType(
 *   id = "commerce_product_bundle_item_selection",
 *   label = @Translation("Bundle item selection"),
 *   description = @Translation("Stores selections from the parent bundle item's options."),
 *   category = @Translation("Commerce"),
 *   default_widget = "commerce_product_bundle_item_selection_default",
 *   default_formatter = "commerce_product_bundle_item_selection_default",
 * )
 */
class BundleItemSelection extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['bundle_item'] = DataDefinition::create('string')
      ->setLabel(t('Bundle item'))
      ->setRequired(FALSE);

    $properties['selected_qty'] = DataDefinition::create('string')
      ->setLabel(t('Selected tuantity'))
      ->setRequired(FALSE);

    $properties['selected_entity'] = DataDefinition::create('string')
      ->setLabel(t('Selected entity'))
      ->setRequired(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'bundle_item' => [
          'description' => 'The bundle item.',
          'type' => 'numeric',
          'precision' => 19,
          'scale' => 0,
        ],
        'selected_qty' => [
          'description' => 'The quantity.',
          'type' => 'numeric',
          'precision' => 19,
          'scale' => 0,
        ],
        'selected_entity' => [
          'description' => 'The selected entity id.',
          'type' => 'numeric',
          'precision' => 19,
          'scale' => 0,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->bundle_item) || empty($this->selected_qty) || empty($this->selected_entity);
  }

}

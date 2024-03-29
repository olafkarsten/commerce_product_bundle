<?php

namespace Drupal\commerce_product_bundle\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Plugin implementation of the 'commerce_product_bundle_item_selection' field type.
 *
 * @FieldType(
 *   id = "commerce_product_bundle_item_selection",
 *   label = @Translation("Bundle item selection"),
 *   description = @Translation("Represents a selection from the parent bundle item's options."),
 *   category = @Translation("Commerce"),
 *   default_widget = "commerce_product_bundle_item_selection_default",
 *   default_formatter = "commerce_product_bundle_item_selection_default",
 * )
 */
class BundleItemSelection extends FieldItemBase {

  /**
   * The product variation storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $variationStorage;

  /**
   * The product bundle item storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $bundleItemStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(DataDefinitionInterface $definition, $name = NULL, TypedDataInterface $parent = NULL) {
    parent::__construct($definition, $name, $parent);
    $this->variationStorage = \Drupal::service('entity_type.manager')->getStorage('commerce_product_variation');
    $this->bundleItemStorage = \Drupal::service('entity_type.manager')->getStorage('commerce_product_bundle_i');
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['bundle_item'] = DataDefinition::create('string')
      ->setLabel(t('Bundle item'))
      ->setRequired(FALSE);

    $properties['selected_qty'] = DataDefinition::create('string')
      ->setLabel(t('Selected quantity'))
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
          'description' => 'The selected quantity.',
          'type' => 'numeric',
          'precision' => 17,
          'scale' => 2,
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
  public static function mainPropertyName() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->bundle_item) || empty($this->selected_qty) || empty($this->selected_entity);
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {

    /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundleItem */
    $bundleItem = $this->bundleItemStorage->load($values['bundle_item']);

    /** @var \Drupal\commerce\PurchasableEntityInterface $purchasableEntity */
    $purchasableEntity = $this->variationStorage->load($values['purchasable_entity']);

    $bundleItem->setCurrentVariation($purchasableEntity);
    parent::setValue($values, $notify);
  }

}

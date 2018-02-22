<?php

namespace Drupal\commerce_product_bundle\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;

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
  public function __construct(DataDefinitionInterface $definition, $name = NULL, TypedDataInterface $parent = NULL){
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

    $properties['qty'] = DataDefinition::create('string')
      ->setLabel(t('Quantity'))
      ->setRequired(FALSE);

    $properties['title'] = DataDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(FALSE);

    $properties['purchasable_entity'] = DataDefinition::create('string')
      ->setLabel(t('purchasable Entity'))
      ->setRequired(FALSE);

    $properties['unit_price_number'] = DataDefinition::create('string')
      ->setLabel(t('Unit Price'))
      ->setRequired(FALSE);

    $properties['unit_price_currency_code'] = DataDefinition::create('string')
      ->setLabel(t('Currency code'))
      ->setRequired(FALSE);

    $properties['data'] = MapDataDefinition::create()
      ->setLabel(t('Data'))
      ->setDescription(t('A serialized array of additional data.'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'bundle_item' => [
          'description' => 'The product bundle item id.',
          'type' => 'numeric',
          'precision' => 19,
          'scale' => 0,
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'qty' => [
          'description' => 'The selected quantity.',
          'type' => 'numeric',
          'precision' => 17,
          'scale' => 2,
          'unsigned' => TRUE,
        ],
        'title' => [
          'description' => 'The title of the purchasable entity.',
          'type' => 'varchar',
          'length' => 512,
        ],
        'purchasable_entity' => [
          'description' => 'The purchasable entity ID.',
          'type' => 'numeric',
          'precision' => 19,
          'scale' => 0,
          'unsigned' => TRUE,
        ],
        'unit_price_number' => [
          'description' => 'The unit price.',
          'type' => 'numeric',
          'precision' => 19,
          'scale' => 6,
        ],
        'unit_price_number_currency_code' => [
          'description' => 'The currency code.',
          'type' => 'varchar',
          'length' => 3,
        ],
        'data' => [
          'description' => 'Escape hatch to keep a serialized array of additional data',
          'type' => 'blob',
          'size' => 'big',
          'serialize' => TRUE,
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
    return empty($this->bundle_item) || empty($this->qty) || empty($this->purchasable_entity);
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE){
    /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundleItem */
    $bundleItem = $this->bundleItemStorage->load($values['bundle_item']);
    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $bundle */
    $bundle = $bundleItem->getBundle();
    $bundlePrice = $bundle->getPrice();
    /** @var \Drupal\commerce\PurchasableEntityInterface $purchasableEntity */
    $purchasableEntity = $this->variationStorage->load($values['purchasable_entity']);
    $values['title'] = $bundleItem->getTitle();
    // In case the bundle has a static price, there is nothing we have to keep here.
    $values['unit_price_number'] = $bundlePrice ? null : $purchasableEntity->getPrice()->getNumber();
    $values['unit_price_currency_code'] = $bundlePrice ? null : $purchasableEntity->getPrice()->getCurrencyCode();
    parent::setValue($values, $notify);
  }

}

<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\commerce\Entity\CommerceContentEntityBase;
use Drupal\commerce_price\Price;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Bundle Item Order Item entity.
 *
 * @ingroup commerce_product_bundle
 *
 * @ContentEntityType(
 *   id = "cpb_order_item",
 *   label = @Translation("Product bundle item order item"),
 *   label_singular = @Translation("product bundle item order item"),
 *   label_plural = @Translation("product bundle item order items"),
 *   label_count = @PluralTranslation(
 *     singular = "@count product bundle item order item",
 *     plural = "@count product bundle item order items",
 *   ),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" =
 *     "Drupal\commerce_product_bundle\Entity\BundleItemOrderItemViewsData",
 *     "access" = "Drupal\commerce\EmbeddedEntityAccessControlHandler",
 *     "form" = {
 *        "default" = "Drupal\Core\Entity\ContentEntityForm",
 *     },
 *     "inline_form" = "Drupal\commerce_product_bundle\Form\BundleItemOrderItemInlineForm",
 *   },
 *   base_table = "cpb_item_order_item",
 *   admin_permission = "administer commerce_order",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "title",
 *   },
 * )
 */
class BundleItemOrderItem extends CommerceContentEntityBase implements BundleItemOrderItemInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBundleItem() {
    return $this->get('bundle_item')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getPurchasedEntity() {
    return $this->getTranslatedReferencedEntity('purchased_entity');
  }

  /**
   * {@inheritdoc}
   */
  public function getPurchasedEntityId() {
    return $this->get('purchased_entity')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * Sets the order item unit price.
   *
   * Drupal\commerce_price\Price $unit_price
   *   The bundle item order item unit price.
   *
   * @return $this
   */
  public function setUnitPrice(Price $unit_price) {
    $this->set('unit_price', $unit_price);
    $this->recalculateTotalPrice();
    return $this;
  }

  /**
   * Recalculates the bundle item order item total price.
   */
  protected function recalculateTotalPrice() {
    if ($unit_price = $this->getUnitPrice()) {
      $rounder = \Drupal::service('commerce_price.rounder');
      $total_price = $unit_price->multiply($this->getQuantity());
      $this->total_price = $rounder->round($total_price);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getUnitPrice() {
    if (!$this->get('unit_price')->isEmpty()) {
      return $this->get('unit_price')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getQuantity() {
    return (string) $this->get('quantity')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getTotalPrice() {
    if (!$this->get('total_price')->isEmpty()) {
      return $this->get('total_price')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderItem() {
    return $this->get('order_item_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderItemId() {
    return $this->get('order_item_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    $this->recalculateTotalPrice();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the Bundle Item Order Item entity.'))
      ->setSettings([
        'max_length' => 512,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // The order item backreference, populated by XXX.
    $fields['order_item_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Order Item'))
      ->setDescription(t('The parent order item.'))
      ->setSetting('target_type', 'commerce_order_item')
      ->setCardinality(1)
      // ->setSetting('handler_settings', ['target_bundles' => ['commerce_product_bundle_item' => 'commerce_product_bundle_item']])
      ->setReadOnly(TRUE);

    $fields['purchased_entity'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Purchased entity'))
      ->setDescription(t('The purchased entity.'))
      ->setSetting('target_type', 'commerce_product_variation')
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -1,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
          'match_limit' => 10,
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['bundle_item'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Bundle item'))
      ->setDescription(t('The bundle item the purchased entity belongs to.'))
      ->setSetting('target_type', 'commerce_product_bundle_i')
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -1,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'match_limit' => 10,
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['quantity'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Quantity'))
      ->setDescription(t('The number of purchased units.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setSetting('min', 0)
      ->setDefaultValue(1)
      ->setDisplayOptions('form', [
        'type' => 'commerce_quantity',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['unit_price'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Unit price'))
      ->setDescription(t('The price of a single unit.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_unit_price',
        'weight' => 2,
        'settings' => [
          'require_confirmation' => TRUE,
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['total_price'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Total price'))
      ->setDescription(t('The total price of the bundle item order item.'))
      ->setReadOnly(TRUE)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time when the bundle item order item was created.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time when the bundle item order item was last edited.'))
      ->setRequired(TRUE);

    return $fields;
  }

}

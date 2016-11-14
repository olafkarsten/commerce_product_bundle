<?php

namespace Drupal\commerce_static_bundle\Entity;


use Drupal\commerce_product_bundle\Entity\BundleItemTypeInterface;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Static bundle item type entity.
 *
 * @ConfigEntityType(
 *   id = "static_bundle_item_type",
 *   label = @Translation("Static bundle item type"),
 *   label_singular = @Translation("static bundle item type"),
 *   label_plural = @Translation("static bundle item types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count static bundle item type",
 *     plural = "@count static bundle item types",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_static_bundle\StaticBundleItemTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_static_bundle\Form\StaticBundleItemTypeForm",
 *       "edit" = "Drupal\commerce_static_bundle\Form\StaticBundleItemTypeForm",
 *       "delete" = "Drupal\commerce_static_bundle\Form\StaticBundleItemTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_static_bundle\StaticBundleItemTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "static_bundle_item_type",
 *   admin_permission = "Administer Static bundle item types",
 *   bundle_of = "static_bundle_item",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/config/product-bundle/static_bundle_item_type/{static_bundle_item_type}",
 *     "add-form" = "/admin/commerce/config/product-bundle/static_bundle_item_type/add",
 *     "edit-form" = "/admin/commerce/config/product-bundle/static_bundle_item_type/{static_bundle_item_type}/edit",
 *     "delete-form" = "/admin/commerce/config/product-bundle/static_bundle_item_type/{static_bundle_item_type}/delete",
 *     "collection" = "/admin/commerce/config/product-bundle/static_bundle_item_type"
 *   }
 * )
 */
class StaticBundleItemType extends ConfigEntityBundleBase implements BundleItemTypeInterface {

  /**
   * The Static bundle item type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Static bundle item type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The order item type ID.
   *
   * @var string
   */
  protected $orderItemType;

  /**
   * {@inheritdoc}
   */
  public function getOrderItemTypeId() {
    return $this->orderItemType;
  }

  /**
   * {@inheritdoc}
   */
  public function setOrderItemTypeId($order_item_type_id) {
    $this->orderItemType = $order_item_type_id;
    return $this;
  }

  /**
   * Get the referenced purchasable entity.
   *
   * @return \Drupal\commerce\PurchasableEntityInterface;
   */
  public function getReferencedEntity(){
      return $this->get('purchasable_entity')->getTarget();
  }

  /**
   * Get the Id of the referenced purchasable entity.
   *
   * @return int
   */
  public function getReferencedEntityId(){
    $this->get('purchasable_entity')->getTargetIdentifier();
  }

}

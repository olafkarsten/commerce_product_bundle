<?php

namespace Drupal\commerce_product_bundle\Entity;


use Drupal\commerce_product_bundle\Entity\BundleItemTypeInterface;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Product bundle item type entity.
 *
 * @ConfigEntityType(
 *   id = "commerce_product_bundle_item_type",
 *   label = @Translation("Product bundle item type"),
 *   label_singular = @Translation("product bundle item type"),
 *   label_plural = @Translation("product bundle item types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count product bundle item type",
 *     plural = "@count product bundle item types",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_product_bundle\ProductBundleItemTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_product_bundle\Form\ProductBundleItemTypeForm",
 *       "edit" = "Drupal\commerce_product_bundle\Form\ProductBundleItemTypeForm",
 *       "delete" = "Drupal\commerce_product_bundle\Form\ProductBundleItemTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_product_bundle\ProductBundleItemTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "commerce_product_bundle_item_type",
 *   admin_permission = "Administer Product bundle item types",
 *   bundle_of = "commerce_product_bundle_item",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/config/product-bundles/item-types/{commerce_product_bundle_item_type}",
 *     "add-form" = "/admin/commerce/config/product-bundles/item-types/add",
 *     "edit-form" = "/admin/commerce/config/product-bundles/item-types/{commerce_product_bundle_item_type}/edit",
 *     "delete-form" = "/admin/commerce/config/product-bundles/item-types/{commerce_product_bundle_item_type}/delete",
 *     "collection" = "/admin/commerce/config/product-bundles/item-types"
 *   }
 * )
 */
class ProductBundleItemType extends ConfigEntityBundleBase implements BundleItemTypeInterface {

  /**
   * The Product bundle item type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Product bundle item type label.
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

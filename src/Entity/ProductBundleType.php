<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the product bundle type entity.
 *
 * @ConfigEntityType(
 *   id = "commerce_product_bundle_type",
 *   label = @Translation("Product bundle type"),
 *   label_collection = @Translation("Product bundle types"),
 *   label_singular = @Translation("Product bundle type"),
 *   label_plural = @Translation("Product bundle types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count product bundle type",
 *     plural = "@count product bundle types",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_product_bundle\ProductBundleTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_product_bundle\Form\ProductBundleTypeForm",
 *       "edit" = "Drupal\commerce_product_bundle\Form\ProductBundleTypeForm",
 *       "duplicate" = "Drupal\commerce_product_bundle\Form\ProductBundleTypeForm",
 *       "delete" = "Drupal\commerce_product_bundle\Form\ProductBundleTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *       "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *     "permission_provider" = "Drupal\commerce_product_bundle\EntityPermissionProvider",
 *   },
 *   config_prefix = "commerce_product_bundle_type",
 *   admin_permission = "administer commerce_product_bundle_type",
 *   bundle_of = "commerce_product_bundle",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "locked",
 *     "description",
 *     "bundleItemType",
 *     "orderItemType",
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/config/product-bundle-types/{commerce_product_bundle_type}",
 *     "add-form" = "/admin/commerce/config/product-bundle-types/add",
 *     "edit-form" = "/admin/commerce/config/product-bundle-types/{commerce_product_bundle_type}/edit",
 *    "duplicate-form" = "/admin/commerce/config/product-bundle-types/{commerce_product_bundle_type}/duplicate",
 *     "delete-form" = "/admin/commerce/config/product-bundle-types/{commerce_product_bundle_type}/delete",
 *     "collection" = "/admin/commerce/config/product-bundle-types"
 *   }
 * )
 */
class ProductBundleType extends ConfigEntityBundleBase implements BundleTypeInterface {

  /**
   * The product bundle type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The product bundle type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The product bundle type description.
   *
   * @var string
   */
  protected $description;

  /**
   * The product bundle item type id.
   *
   * @var string
   */
  protected $bundleItemType;

  /**
   * The order item type id.
   *
   * @var string
   */
  protected $orderItemType;

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setBundleItemTypeId($bundle_item_type_id) {
    $this->bundleItemType = $bundle_item_type_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getBundleItemTypeId() {
    return $this->bundleItemType;
  }

  /**
   * {@inheritdoc}
   */
  public function setOrderItemTypeId($order_item_type_id) {
    $this->orderItemType = $order_item_type_id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderItemTypeId() {
    return $this->orderItemType;
  }

}

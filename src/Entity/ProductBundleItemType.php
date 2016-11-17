<?php

namespace Drupal\commerce_product_bundle\Entity;


use Drupal\commerce_product_bundle\Entity\BundleItemTypeInterface;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the product bundle item type entity.
 *
 * @ConfigEntityType(
 *   id = "commerce_product_bundle_i_type",
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
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *       "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *     "permission_provider" = "Drupal\commerce_product_bundle\EntityPermissionProvider",
 *   },
 *   config_prefix = "commerce_product_bundle_i_type",
 *   admin_permission = "administer commerce_product_bundle_i_type",
 *   bundle_of = "commerce_product_bundle_i",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/config/product-bundles/item-types/{commerce_product_bundle_i_type}",
 *     "add-form" = "/admin/commerce/config/product-bundles/item-types/add",
 *     "edit-form" = "/admin/commerce/config/product-bundles/item-types/{commerce_product_bundle_i_type}/edit",
 *     "delete-form" = "/admin/commerce/config/product-bundles/item-types/{commerce_product_bundle_i_type}/delete",
 *     "collection" = "/admin/commerce/config/product-bundles/item-types"
 *   }
 * )
 */
class ProductBundleItemType extends ConfigEntityBundleBase implements BundleItemTypeInterface {

  /**
   * The product bundle item type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The product bundle item type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The product bundle item type description.
   *
   * @var string
   */
  protected $description;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
  }

}

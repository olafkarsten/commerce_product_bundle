<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\commerce_product_bundle\Entity\BundleTypeInterface;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Product bundle type entity.
 *
 * @ConfigEntityType(
 *   id = "commerce_product_bundle_type",
 *   label = @Translation("Product bundle type"),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_product_bundle\ProductBundleTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_product_bundle\Form\ProductBundleTypeForm",
 *       "edit" = "Drupal\commerce_product_bundle\Form\ProductBundleTypeForm",
 *       "delete" = "Drupal\commerce_product_bundle\Form\ProductBundleTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_product_bundle\ProductBundleTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "commerce_product_bundle_type",
 *   admin_permission = "Administer Product bundle types",
 *   bundle_of = "commerce_product_bundle",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/config/product-bundles/bundle-types/{commerce_product_bundle_type}",
 *     "add-form" = "/admin/commerce/config/product-bundles/bundle-types/add",
 *     "edit-form" = "/admin/commerce/config/product-bundles/bundle-types/{commerce_product_bundle_type}/edit",
 *     "delete-form" = "/admin/commerce/config/product-bundles/bundle-types/{commerce_product_bundle_type}/delete",
 *     "collection" = "/admin/commerce/config/product-bundles/bundle-types"
 *   }
 * )
 */
class ProductBundleType extends ConfigEntityBundleBase implements BundleTypeInterface {

  /**
   * The Product bundle type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Product bundle type label.
   *
   * @var string
   */
  protected $label;

}

<?php

namespace Drupal\commerce_static_bundle\Entity;

use Drupal\commerce_product_bundle\Entity\BundleTypeInterface;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Static bundle type entity.
 *
 * @ConfigEntityType(
 *   id = "commerce_static_bundle_type",
 *   label = @Translation("Static bundle type"),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_static_bundle\StaticBundleTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_static_bundle\Form\StaticBundleTypeForm",
 *       "edit" = "Drupal\commerce_static_bundle\Form\StaticBundleTypeForm",
 *       "delete" = "Drupal\commerce_static_bundle\Form\StaticBundleTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_static_bundle\StaticBundleTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "commerce_static_bundle_type",
 *   admin_permission = "Administer Static bundle types",
 *   bundle_of = "commerce_static_bundle",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/config/product-bundle/static_bundle_type/{commerce_static_bundle_type}",
 *     "add-form" = "/admin/commerce/config/product-bundle/static_bundle_type/add",
 *     "edit-form" = "/admin/commerce/config/product-bundle/static_bundle_type/{commerce_static_bundle_type}/edit",
 *     "delete-form" = "/admin/commerce/config/product-bundle/static_bundle_type/{commerce_static_bundle_type}/delete",
 *     "collection" = "/admin/commerce/config/product-bundle/static-bundle/static-bundle-types/static_bundle_type"
 *   }
 * )
 */
class StaticBundleType extends ConfigEntityBundleBase implements BundleTypeInterface {

  /**
   * The Static bundle type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Static bundle type label.
   *
   * @var string
   */
  protected $label;

}

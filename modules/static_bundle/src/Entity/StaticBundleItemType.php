<?php

namespace Drupal\commerc_static_bundle\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Static bundle item type entity.
 *
 * @ConfigEntityType(
 *   id = "static_bundle_item_type",
 *   label = @Translation("Static bundle item type"),
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
 *   admin_permission = "administer site configuration",
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
class StaticBundleItemType extends ConfigEntityBundleBase implements StaticBundleItemTypeInterface {

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

}

<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;

/**
 * Provides an interface for defining product bundle type entities.
 */
interface BundleTypeInterface extends ConfigEntityInterface, EntityDescriptionInterface {

  /**
   * Gets the product bundle type's matching bundle item type ID.
   *
   * @return string
   *   The bundle item type ID.
   */
  public function getBundleItemTypeId();

  /**
   * Sets the product bundle type's matching bundle item type ID.
   *
   * @param string $bundle_item_type_id
   *   The bundle item type ID.
   *
   * @return $this
   */
  public function setBundleItemTypeId($bundle_item_type_id);

  /**
   * Gets the product bundle type's order item type ID.
   *
   * Used for finding/creating the appropriate order item when purchasing a
   * product bundle (adding it to an order).
   *
   * @return string
   *   The order item type ID.
   */
  public function getOrderItemTypeId();

  /**
   * Sets the product bundle type's order item type ID.
   *
   * @param string $order_item_type_id
   *   The order item type ID.
   *
   * @return $this
   */
  public function setOrderItemTypeId($order_item_type_id);

}

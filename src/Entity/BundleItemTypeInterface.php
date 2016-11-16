<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Product bundle item type entities.
 */
interface BundleItemTypeInterface extends ConfigEntityInterface {

  /**
   * Gets the bundle items type's order item type ID.
   *
   * Used for finding/creating the appropriate order item when purchasing a
   * bundle item (adding it to an order).
   *
   * @return string
   *   The order item type ID.
   */
  public function getOrderItemTypeId();

  /**
   * Sets the bundle item type's order item type ID.
   *
   * @param string $order_item_type_id
   *   The order item type ID.
   *
   * @return $this
   */
  public function setOrderItemTypeId($order_item_type_id);
}

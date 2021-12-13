<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface for defining Bundle Item Order Item entities.
 *
 * @ingroup commerce_product_bundle
 */
interface BundleItemOrderItemInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Gets the Bundle Item.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface|null
   *   The bundle item, or NULL.
   */
  public function getBundleItem();

  /**
   * Gets the purchased entity.
   *
   * @return \Drupal\commerce\PurchasableEntityInterface|null
   *   The purchased entity, or NULL.
   */
  public function getPurchasedEntity();

  /**
   * Gets the purchased entity ID.
   *
   * @return int
   *   The purchased entity ID.
   */
  public function getPurchasedEntityId();

  /**
   * Gets the parent order item.
   *
   * @return \Drupal\commerce_order\Entity\OrderItemInterface|null
   *   The order item, or NULL.
   */
  public function getOrderItem();

  /**
   * Gets the parent order item ID.
   *
   * @return int|null
   *   The order item ID, or NULL.
   */
  public function getOrderItemId();

  /**
   * Sets the bundle item order item title.
   *
   * @param string $title
   *   The order item title
   */
  public function setTitle($title);

  /**
   * Gets the bundle item order item title.
   *
   * @return string
   *   The order item title
   */
  public function getTitle();

  /**
   * Gets the bundle item order item quantity.
   *
   * @return string
   *   The order item quantity
   */
  public function getQuantity();

  /**
   * Gets the bundle item order item unit price.
   *
   * @return \Drupal\commerce_price\Price|null
   *   The order item unit price, or NULL.
   */
  public function getUnitPrice();

  /**
   * Gets the bundle item order item total price.
   *
   * @return \Drupal\commerce_price\Price|null
   *   The order item total price, or NULL.
   */
  public function getTotalPrice();

  /**
   * Gets the order item creation timestamp.
   *
   * @return int
   *   The bundle item order item creation timestamp.
   */
  public function getCreatedTime();

}

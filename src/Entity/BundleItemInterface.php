<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Product bundle item entities.
 *
 * @ingroup commerce_product_bundle
 */
interface BundleItemInterface extends RevisionableInterface, EntityChangedInterface, EntityOwnerInterface, PurchasableEntityInterface  {

  // @ToDo Add get/set methods for your configuration properties here.

  /**
   * Gets the Product bundle item type.
   *
   * @return string
   *   The Product bundle item type.
   */
  public function getType();

  /**
   * Gets the Product bundle item title.
   *
   * @return string
   *   Title of the Product bundle item.
   */
  public function getTitle();

  /**
   * Sets the Product bundle item title.
   *
   * @param string $title
   *   The Product bundle item title.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called Product bundle item entity.
   */
  public function setTitle($title);

  /**
   * Gets the Product bundle item creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Product bundle item.
   */
  public function getCreatedTime();

  /**
   * Sets the Product bundle item creation timestamp.
   *
   * @param int $timestamp
   *   The Product bundle item creation timestamp.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called Product bundle item entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Product bundle item published status indicator.
   *
   * Unpublished Product bundle item are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Product bundle item is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Product bundle item.
   *
   * @param bool $published
   *   TRUE to set this Product bundle item to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called Product bundle item entity.
   */
  public function setPublished($published);

  /**
   * Gets the Product bundle item revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Product bundle item revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called Product bundle item entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Product bundle item revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionAuthor();

  /**
   * Sets the Product bundle item revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called Product bundle item entity.
   */
  public function setRevisionAuthorId($uid);

  /**
   * Sets the quantity  of the referenced
   * purchasable entity.
   * Sets the order item quantity.
   *
   * @param string $quantity
   *   The order item quantity.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called Product bundle item entity.
   */
  public function setQuantity($quantity);

  /**
   * Get the quantity of the purchasable entity.
   *
   * @return float
   */
  public function getQuantity();

  /**
   * Get the referenced purchasable entity.
   *
   * @return PurchasableEntityInterface
   *    The referenced purchasable entity.
   */
  public function getReferencedEntity();

  /**
   * Sets the price of one unit of the referenced
   * purchasable entity.
   *
   * @param Price $unit_price
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called Product bundle item entity.
   */
  public function setUnitPrice(Price $unit_price);

  /**
   * Gets the price of one unit of the referenced
   * purchasable entity.
   *
   * @return Price $unit_price
   */
  public function getUnitPrice();

}

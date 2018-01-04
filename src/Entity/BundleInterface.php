<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining product bundle entities.
 *
 * @ingroup commerce_product_bundle
 */
interface BundleInterface extends EntityChangedInterface, EntityOwnerInterface, PurchasableEntityInterface {

  /**
   * Gets the product bundle type.
   *
   * @return string
   *   The product bundle type.
   */
  public function getType();

  /**
   * Sets the product bundle price.
   *
   * @param \Drupal\commerce_price\Price $price
   *   The price.
   *
   * @return $this
   */
  public function setPrice(Price $price);

  /**
   * Gets the product bundle title.
   *
   * @return string
   *   Title of the product bundle.
   */
  public function getTitle();

  /**
   * Sets the product bundle title.
   *
   * @param string $title
   *   The product bundle title.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleInterface
   *   The called product bundle entity.
   */
  public function setTitle($title);

  /**
   * Gets the product bundle creation timestamp.
   *
   * @return int
   *   Creation timestamp of the product bundle.
   */
  public function getCreatedTime();

  /**
   * Sets the product bundle creation timestamp.
   *
   * @param int $timestamp
   *   The product bundle creation timestamp.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleInterface
   *   The called product bundle entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the product bundle published status indicator.
   *
   * Unpublished product bundle are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the product bundle is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a product bundle.
   *
   * @param bool $published
   *   TRUE to set this product bundle to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleInterface
   *   The called product bundle entity.
   */
  public function setPublished($published);

  /**
   * Returns the bundle items of that bundle.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface[]
   *   The referenced bundle items of this product bundle.
   */
  public function getBundleItems();

  /**
   * Sets the bundle items of that bundle.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleItemInterface[] $bundle_items
   *   The bundle items to add.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleInterface
   *   The called bundle entity.
   */
  public function setBundleItems(array $bundle_items);

  /**
   * Adds a bundle item to the bundle.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundle_item
   *   The bundle item to add.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleInterface[]
   *   The called bundle entity.
   */
  public function addBundleItem(BundleItemInterface $bundle_item);

  /**
   * Removes a bundle item.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundle_item
   *   The bundle item to remove.
   *
   * @return $this
   */
  public function removeBundleItem(BundleItemInterface $bundle_item);

  /**
   * Checks whether the bundle has any bundle items.
   *
   * @return bool
   *   True if the bundle has any bundle items, false other wise.
   */
  public function hasBundleItems();

  /**
   * Checks whether the bundle has a given bundle item.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundle_item
   *   The bundle item to check for.
   *
   * @return bool
   *   True if the given bundle item is referenced, false otherwise.
   */
  public function hasBundleItem(BundleItemInterface $bundle_item);

  /**
   * Gets the bundle item IDs.
   *
   * @return int[]
   *   The bundle item IDs.
   */
  public function getBundleItemIds();

}

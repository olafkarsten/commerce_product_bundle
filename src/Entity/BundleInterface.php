<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining product bundle entities.
 *
 * @ingroup commerce_product_bundle
 */
interface BundleInterface extends RevisionableInterface, EntityChangedInterface, EntityOwnerInterface, PurchasableEntityInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the product bundle type.
   *
   * @return string
   *   The product bundle type.
   */
  public function getType();

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
   * Gets the product bundle revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the product bundle revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleInterface
   *   The called product bundle entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the product bundle revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionAuthor();

  /**
   * Sets the product bundle revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return  \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called product bundle entity.
   */
  public function setRevisionAuthorId($uid);

  /**
   * Returns the bundle items of that bundle.
   *
   * @return array of  \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *    Array of the bundle items.
   */
  public function getBundleItems();

  /**
   * Sets the bundle items of that bundle.
   *
   * @param array $bundleItems
   *    Array of  \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleInterface
   *    The called bundle entity.
   */
  public function setBundleItems(array $bundleItems);

  /**
   * Adds a bunde item to the bundle
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundleItem
   *
   * @return  \Drupal\commerce_product_bundle\Entity\BundleInterface
   *    The called bundle entity.
   */
  public function addBundleItem(BundleItemInterface $bundleItem);

  /**
   * Adds a bunde item to the bundle
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundleItem
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleInterface
   *    The called bundle entity.
   */
  public function removeBundleItem(BundleItemInterface $bundleItem);

}

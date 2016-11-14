<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Static bundle entities.
 *
 * @ingroup commerce_static_bundle
 */
interface BundleInterface extends RevisionableInterface, EntityChangedInterface, EntityOwnerInterface, PurchasableEntityInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Static bundle type.
   *
   * @return string
   *   The Static bundle type.
   */
  public function getType();

  /**
   * Gets the Static bundle title.
   *
   * @return string
   *   Title of the Static bundle.
   */
  public function getTitle();

  /**
   * Sets the Static bundle title.
   *
   * @param string $title
   *   The Static bundle title.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleInterface
   *   The called Static bundle entity.
   */
  public function setTitle($title);

  /**
   * Gets the Static bundle creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Static bundle.
   */
  public function getCreatedTime();

  /**
   * Sets the Static bundle creation timestamp.
   *
   * @param int $timestamp
   *   The Static bundle creation timestamp.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleInterface
   *   The called Static bundle entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Static bundle published status indicator.
   *
   * Unpublished Static bundle are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Static bundle is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Static bundle.
   *
   * @param bool $published
   *   TRUE to set this Static bundle to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleInterface
   *   The called Static bundle entity.
   */
  public function setPublished($published);

  /**
   * Gets the Static bundle revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Static bundle revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleInterface
   *   The called Static bundle entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Static bundle revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionAuthor();

  /**
   * Sets the Static bundle revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return  \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called Static bundle entity.
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

<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Static bundle item entities.
 *
 * @ingroup commerce_static_bundle
 */
interface BundleItemInterface extends RevisionableInterface, EntityChangedInterface, EntityOwnerInterface, PurchasableEntityInterface  {

  // @ToDo Add get/set methods for your configuration properties here.

  /**
   * Gets the Static bundle item type.
   *
   * @return string
   *   The Static bundle item type.
   */
  public function getType();

  /**
   * Gets the Static bundle item title.
   *
   * @return string
   *   Title of the Static bundle item.
   */
  public function getTitle();

  /**
   * Sets the Static bundle item title.
   *
   * @param string $title
   *   The Static bundle item title.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called Static bundle item entity.
   */
  public function setTitle($title);

  /**
   * Gets the Static bundle item creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Static bundle item.
   */
  public function getCreatedTime();

  /**
   * Sets the Static bundle item creation timestamp.
   *
   * @param int $timestamp
   *   The Static bundle item creation timestamp.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called Static bundle item entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Static bundle item published status indicator.
   *
   * Unpublished Static bundle item are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Static bundle item is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Static bundle item.
   *
   * @param bool $published
   *   TRUE to set this Static bundle item to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called Static bundle item entity.
   */
  public function setPublished($published);

  /**
   * Gets the Static bundle item revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Static bundle item revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called Static bundle item entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Static bundle item revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionAuthor();

  /**
   * Sets the Static bundle item revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called Static bundle item entity.
   */
  public function setRevisionAuthorId($uid);

}

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
   * Gets the Static bundle name.
   *
   * @return string
   *   Name of the Static bundle.
   */
  public function getName();

  /**
   * Sets the Static bundle name.
   *
   * @param string $name
   *   The Static bundle name.
   *
   * @return \Drupal\commerce_static_bundle\Entity\StaticBundleInterface
   *   The called Static bundle entity.
   */
  public function setName($name);

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
   * @return \Drupal\commerce_static_bundle\Entity\StaticBundleInterface
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
   * @return \Drupal\commerce_static_bundle\Entity\StaticBundleInterface
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
   * @return \Drupal\commerce_static_bundle\Entity\StaticBundleInterface
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
   * @return \Drupal\commerce_static_bundle\Entity\StaticBundleInterface
   *   The called Static bundle entity.
   */
  public function setRevisionAuthorId($uid);


}

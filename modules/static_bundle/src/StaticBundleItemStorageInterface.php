<?php

namespace Drupal\commerce_static_bundle;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\commerce_product_bundle\Entity\BundleItemInterface;

/**
 * Defines the storage handler class for Static bundle item entities.
 *
 * This extends the base storage class, adding required special handling for
 * Static bundle item entities.
 *
 * @ingroup commerce_static_bundle
 */
interface StaticBundleItemStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Static bundle item revision IDs for a specific Static bundle item.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleItemInterface $entity
   *   The Static bundle item entity.
   *
   * @return int[]
   *   Static bundle item revision IDs (in ascending order).
   */
  public function revisionIds(BundleItemInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Static bundle item author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Static bundle item revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleItemInterface $entity
   *   The Static bundle item entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(BundleItemInterface $entity);

  /**
   * Unsets the language for all Static bundle item with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}

<?php

namespace Drupal\commerce_static_bundle;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\commerce_product_bundle\Entity\BundleInterface;

/**
 * Defines the storage handler class for Static bundle entities.
 *
 * This extends the base storage class, adding required special handling for
 * Static bundle entities.
 *
 * @ingroup commerce_static_bundle
 */
interface StaticBundleStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Static bundle revision IDs for a specific Static bundle.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleInterface $entity
   *   The Static bundle entity.
   *
   * @return int[]
   *   Static bundle revision IDs (in ascending order).
   */
  public function revisionIds(BundleInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Static bundle author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Static bundle revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleInterface $entity
   *   The Static bundle entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(BundleInterface $entity);

  /**
   * Unsets the language for all Static bundle with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}

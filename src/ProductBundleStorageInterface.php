<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\commerce_product_bundle\Entity\BundleInterface;

/**
 * Defines the storage handler class for Product bundle entities.
 *
 * This extends the base storage class, adding required special handling for
 * Product bundle entities.
 *
 * @ingroup commerce_product_bundle
 */
interface ProductBundleStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Product bundle revision IDs for a specific Product bundle.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleInterface $entity
   *   The Product bundle entity.
   *
   * @return int[]
   *   Product bundle revision IDs (in ascending order).
   */
  public function revisionIds(BundleInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Product bundle author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Product bundle revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleInterface $entity
   *   The Product bundle entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(BundleInterface $entity);

  /**
   * Unsets the language for all Product bundle with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}

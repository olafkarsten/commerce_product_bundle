<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\commerce_product_bundle\Entity\BundleItemInterface;

/**
 * Defines the storage handler class for Product bundle item entities.
 *
 * This extends the base storage class, adding required special handling for
 * Product bundle item entities.
 *
 * @ingroup commerce_product_bundle
 */
interface ProductBundleItemStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Product bundle item revision IDs for a specific Product bundle item.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleItemInterface $entity
   *   The Product bundle item entity.
   *
   * @return int[]
   *   Product bundle item revision IDs (in ascending order).
   */
  public function revisionIds(BundleItemInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Product bundle item author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Product bundle item revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleItemInterface $entity
   *   The Product bundle item entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(BundleItemInterface $entity);

  /**
   * Unsets the language for all Product bundle item with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}

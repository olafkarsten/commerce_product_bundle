<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class ProductBundleBundleStorage extends SqlContentEntityStorage implements ProductBundleStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(BundleInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {commerce_product_bundle_revision} WHERE id=:id ORDER BY vid',
      array(':id' => $entity->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {commerce_product_bundle_field_revision} WHERE uid = :uid ORDER BY vid',
      array(':uid' => $account->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(BundleInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {commerce_product_bundle_field_revision} WHERE id = :id AND default_langcode = 1', array(':id' => $entity->id()))
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('commerce_product_bundle_revision')
      ->fields(array('langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED))
      ->condition('langcode', $language->getId())
      ->execute();
  }

}

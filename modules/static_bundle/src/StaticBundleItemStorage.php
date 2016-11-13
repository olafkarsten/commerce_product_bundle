<?php

namespace Drupal\commerce_static_bundle;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class StaticBundleItemStorage extends SqlContentEntityStorage implements StaticBundleItemStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(BundleItemInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {static_bundle_item_revision} WHERE id=:id ORDER BY vid',
      array(':id' => $entity->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {static_bundle_item_field_revision} WHERE uid = :uid ORDER BY vid',
      array(':uid' => $account->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(BundleItemInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {static_bundle_item_field_revision} WHERE id = :id AND default_langcode = 1', array(':id' => $entity->id()))
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('static_bundle_item_revision')
      ->fields(array('langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED))
      ->condition('langcode', $language->getId())
      ->execute();
  }

}

<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\commerce_product_bundle\Entity\BundleItemInterface;

/**
 * Defines the storage handler class for product bundle item entities.
 *
 * This extends the base storage class, adding required special handling for
 * product bundle item entities.
 *
 * @ingroup commerce_product_bundle
 */
class ProductBundleItemStorage extends SqlContentEntityStorage implements ProductBundleItemStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function loadRevision($revision_id) {
    $revision = $this->doLoadRevisionFieldItems($revision_id);

    if ($revision) {
      $entities = [$revision->id() => $revision];
      $this->invokeStorageLoadHook($entities);
      $this->postLoad($entities);
    }

    return $revision;
  }

  /**
   * {@inheritdoc}
   */
  protected function doLoadRevisionFieldItems($revision_id) {
    $revision = NULL;

    // Build and execute the query.
    $query_result = $this->buildQuery(array(), $revision_id)->execute();
    $records = $query_result->fetchAllAssoc($this->idKey);

    if (!empty($records)) {
      // Convert the raw records to entity objects.
      $entities = $this->mapFromStorageRecords($records, TRUE);
      $revision = reset($entities) ?: NULL;
    }

    return $revision;
  }

  /**
   * {@inheritdoc}
   */
  public function revisionIds(BundleItemInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {commerce_product_bundle_item_revision} WHERE id=:id ORDER BY vid',
      array(':id' => $entity->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {commerce_product_bundle_item_field_revision} WHERE uid = :uid ORDER BY vid',
      array(':uid' => $account->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(BundleItemInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {commerce_product_bundle_item_field_revision} WHERE id = :id AND default_langcode = 1', array(':id' => $entity->id()))
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('commerce_product_bundle_item_revision')
      ->fields(array('langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED))
      ->condition('langcode', $language->getId())
      ->execute();
  }

}

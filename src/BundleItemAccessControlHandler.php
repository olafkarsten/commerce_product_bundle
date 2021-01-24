<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity\EntityAccessControlHandlerBase;

/**
 * Controls access based on the Commerce Product Bundle permissions.
 *
 * @see \Drupal\commerce_product_bundle\EntityPermissionProvider
 */
class BundleItemAccessControlHandler extends EntityAccessControlHandlerBase {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(
    EntityInterface $entity,
    $operation,
    AccountInterface $account
  ) {
    if ($account->hasPermission($this->entityType->getAdminPermission())) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $entity */
    $product_bundle = $entity->getBundle();
    if (!$product_bundle) {
      // The bundle item is malformed.
      return AccessResult::forbidden()->addCacheableDependency($entity);
    }

    if ($operation === 'view') {
      $result = $product_bundle->access('view', $account, TRUE);
      assert($result instanceof AccessResult);
      $result->addCacheableDependency($entity);
    }
    else {
      $bundle = $entity->bundle();
      $result = AccessResult::allowedIfHasPermission($account,
        "manage $bundle commerce_product_bundle_i",
                  'access commerce_product_bundle overview'
      )

        ->cachePerPermissions();
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(
    AccountInterface $account,
    array $context,
    $entity_bundle = NULL
  ) {
    // Create access depends on the "manage" permission because the full entity
    // is not passed, making it impossible to determine the parent product.
    $result = AccessResult::allowedIfHasPermissions($account, [
      $this->entityType->getAdminPermission(),
      "manage $entity_bundle commerce_product_bundle_i",
    ], 'OR');

    return $result;
  }

}

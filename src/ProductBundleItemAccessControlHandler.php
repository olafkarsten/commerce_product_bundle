<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity\EntityAccessControlHandler as BaseEntityAccessControlHandler;

/**
 * Controls access based on the Commerce Product Bundle permissions.
 *
 * @see \Drupal\commerce_product_bundle\EntityPermissionProvider
 */
class ProductBundleItemAccessControlHandler extends BaseEntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($operation == 'view') {
      return AccessResult::allowed();
    }
    return parent::checkAccess($entity, $operation, $account);
  }

}

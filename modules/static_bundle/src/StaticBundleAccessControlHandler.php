<?php

namespace Drupal\commerce_static_bundle;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Static bundle entity.
 *
 * @see \Drupal\commerce_static_bundle\Entity\StaticBundle.
 */
class StaticBundleAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished static bundle entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published static bundle entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit static bundle entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete static bundle entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add static bundle entities');
  }

}

<?php

namespace Drupal\commerce_product_bundle\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;

/**
 * Defines an access checker for product bundle item creation.
 *
 * Takes the product bundle item type ID from the product bundle type, since a
 * product bundle is always present in bundle item routes.
 */
class BundleItemCreateAccessCheck implements AccessInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new BundleItemCreateAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Checks access to create the product bundle item.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $product_bundle */
    $product_bundle = $route_match->getParameter('commerce_product_bundle');
    if (!$product_bundle) {
      return AccessResult::forbidden();
    }
    $access_control_handler = $this->entityTypeManager->getAccessControlHandler('commerce_product_bundle_i');
    $product_bundle_type_storage = $this->entityTypeManager->getStorage('commerce_product_bundle_type');
    /** @var \Drupal\commerce_product_bundle\Entity\BundleTypeInterface $product_bundle_type */
    $product_bundle_type = $product_bundle_type_storage->load($product_bundle->bundle());
    $bundle_item_type_id = $product_bundle_type->getBundleItemTypeId();

    return $access_control_handler->createAccess($bundle_item_type_id, $account, [], TRUE);
  }

}

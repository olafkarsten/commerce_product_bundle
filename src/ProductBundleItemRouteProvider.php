<?php

namespace Drupal\commerce_product_bundle;

use Drupal\commerce_product_bundle\Controller\ProductBundleItemController;
use Drupal\entity\Routing\AdminHtmlRouteProvider;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for the product bundle item entity.
 */
class ProductBundleItemRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  protected function getAddFormRoute(EntityTypeInterface $entity_type) {
    $route = new Route($entity_type->getLinkTemplate('add-form'));
    $route
      ->setDefaults([
        '_entity_form' => 'commerce_product_bundle_i.add',
        'entity_type_id' => 'commerce_product_bundle_i',
        '_title_callback' => ProductBundleItemController::class . '::addTitle',
      ])
      ->setRequirement('_bundle_item_create_access', 'TRUE')
      ->setOption('parameters', [
        'commerce_product_bundle' => [
          'type' => 'entity:commerce_product_bundle',
        ],
      ])
      ->setOption('_admin_route', TRUE);

    return $route;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditFormRoute(EntityTypeInterface $entity_type) {
    $route = parent::getEditFormRoute($entity_type);
    if (!$route) {
      return NULL;
    }
    $route->setDefault('_title_callback', ProductBundleItemController::class . '::editTitle');
    $route->setOption('parameters', [
      'commerce_product_bundle' => [
        'type' => 'entity:commerce_product_bundle',
      ],
      'commerce_product_bundle_i' => [
        'type' => 'entity:commerce_product_bundle_i',
      ],
    ]);
    $route->setOption('_admin_route', TRUE);

    return $route;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDeleteFormRoute(EntityTypeInterface $entity_type) {
    $route = parent::getDeleteFormRoute($entity_type);
    $route->setDefault('_title_callback', ProductBundleItemController::class . '::deleteTitle');
    $route->setOption('parameters', [
      'commerce_product_bundle' => [
        'type' => 'entity:commerce_product_bundle',
      ],
      'commerce_product_bundle_i' => [
        'type' => 'entity:commerce_product_bundle_i',
      ],
    ]);
    $route->setOption('_admin_route', TRUE);

    return $route;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCollectionRoute(EntityTypeInterface $entity_type) {
    $route = new Route($entity_type->getLinkTemplate('collection'));
    $route
      ->addDefaults([
        '_entity_list' => 'commerce_product_bundle_i',
        '_title_callback' => ProductBundleItemController::class . '::collectionTitle',
      ])
      ->setRequirement('_bundle_item_collection_access', 'TRUE')
      ->setOption('parameters', [
        'commerce_product_bundle' => [
          'type' => 'entity:commerce_product_bundle',
        ],
      ])
      ->setOption('_admin_route', TRUE);

    return $route;
  }

}

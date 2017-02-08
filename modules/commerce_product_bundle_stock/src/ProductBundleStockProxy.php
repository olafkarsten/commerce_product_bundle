<?php

namespace Drupal\commerce_product_bundle_stock;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_product_bundle\Entity\BundleInterface;
use Drupal\commerce_stock\StockCheckInterface;
use Drupal\commerce_stock\StockServiceManagerInterface;
use Drupal\commerce_stock\StockUpdateInterface;

/**
 * Provides a stock service for product bundles.
 */
class ProductBundleStockProxy implements StockCheckInterface, StockUpdateInterface {

  /**
   * The stock service manager.
   *
   * @var \Drupal\commerce_stock\StockServiceManagerInterface
   */
  protected $stockServiceManager;

  /**
   * Constructs a new ProductBundleStockProxy object.
   *
   * @param \Drupal\commerce_stock\StockServiceManagerInterface $stock_service_manager
   *   The stock service manager.
   */
  public function __construct(StockServiceManagerInterface $stock_service_manager) {
    $this->stockServiceManager = $stock_service_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function createTransaction(PurchasableEntityInterface $bundle, $location_id, $zone, $quantity, $unit_cost, $transaction_type_id, array $metadata) {
    /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $item */
    foreach ($bundle->getBundleItems() as $item) {
      $entity = $item->getCurrentVariation();
      $service = $this->stockServiceManager->getService($entity);
      $updater = $service->getStockUpdater();
      $item_quantity = $quantity * $item->getQuantity();
      $updater->createTransaction($entity, $location_id, $zone, $item_quantity, $unit_cost, $transaction_type_id, $metadata);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTotalStockLevel(PurchasableEntityInterface $bundle, array $locations) {
    $levels = array_map(function ($bundleItem) use ($locations) {
      /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundleItem */
      $quantity = $bundleItem->getQuantity() ?: 1;
      $entity = $bundleItem->getCurrentVariation();
      /** @var \Drupal\commerce\PurchasableEntityInterface $entity */
      $service = $this->stockServiceManager->getService($entity);
      $level = $service->getStockChecker()->getTotalStockLevel($entity, $locations);
      return floor($level / $quantity);
    }, $bundle->getBundleItems());

    return min($levels);
  }

  /**
   * {@inheritdoc}
   */
  public function getIsInStock(PurchasableEntityInterface $bundle, array $locations) {
    /** @var \Drupal\commerce\PurchasableEntityInterface $entity */
    foreach ($this->getAllPurchasableEntities($bundle) as $entity) {
      $service = $this->stockServiceManager->getService($entity);
      $checker = $service->getStockChecker();
      if (!$checker->getIsInStock($entity, $locations)) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getIsAlwaysInStock(PurchasableEntityInterface $bundle) {
    /** @var \Drupal\commerce\PurchasableEntityInterface $entity */
    foreach ($this->getAllPurchasableEntities($bundle) as $entity) {
      $service = $this->stockServiceManager->getService($entity);
      $checker = $service->getStockChecker();
      if (!$checker->getIsAlwaysInStock($entity)) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getIsStockManaged(PurchasableEntityInterface $entity) {
    // @todo Rethink this.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getLocationList($return_active_only = TRUE) {
    $services = $this->stockServiceManager->listServices();
    $locations = [];
    /** @var \Drupal\commerce_stock\StockServiceInterface $service */
    foreach ($services as $service) {
      $locations += $service->getStockChecker()->getLocationList();
    }
    return $locations;
  }

  /**
   * Gets the currently selected variation of each bundle item.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleInterface $product_bundle
   *   The product bundle.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface[]
   *   All purchasable entities.
   */
  protected function getAllPurchasableEntities(BundleInterface $product_bundle) {
    return array_map(function ($item) {
      /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $item */
      return $item->getCurrentVariation();
    }, $product_bundle->getBundleItems());
  }

}

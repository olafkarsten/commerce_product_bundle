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
   * @var int[]
   *   Array of transaction ids in case we did call createTransaction.
   */
  protected $transactionIds = [];

  /**
   * Constructs a new ProductBundleStockProxy object.
   *
   * @param \Drupal\commerce_stock\StockServiceManagerInterface $stock_service_manager
   *   The stock service manager.
   */
  public function __construct(
    StockServiceManagerInterface $stock_service_manager
  ) {
    $this->stockServiceManager = $stock_service_manager;
  }

  /**
   * {@inheritdoc}
   *
   * Note: The $bundle parameter must implement the Drupal\commerce_product_bundle\Entity\BundleInterface.
   * We can't change the signature for the constructor, as the interface is defined in
   * commerce stock.
   *
   * We don't have a single transaction id to return, like the StockUpdaterInterface requested. You can access the transaction ids from
   * the latest createTransaction call through $this->transactionIds.
   */
  public function createTransaction(
    PurchasableEntityInterface $bundle,
    $location_id,
    $zone,
    $quantity,
    $unit_cost,
    $currency_code,
    $transaction_type_id,
    array $metadata
  ) {
    $this->assertBundleInterface($bundle);
    $this->transactionIds = [];
    /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $item */
    foreach ($bundle->getBundleItems() as $item) {
      /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $entity */
      $entity = $item->getCurrentVariation();
      $service = $this->stockServiceManager->getService($entity);
      $updater = $service->getStockUpdater();
      $item_quantity = $quantity * $item->getQuantity();
      $this->transactionIds[] = $updater->createTransaction($entity, $location_id, $zone, $item_quantity, $unit_cost, $currency_code, $transaction_type_id, $metadata);
    }
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getTotalStockLevel(
    PurchasableEntityInterface $bundle,
    array $locations
  ) {
    $this->assertBundleInterface($bundle);
    /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundleItem */
    $levels = array_map(function ($bundleItem) use ($bundle, $locations) {
      $quantity = $bundleItem->getQuantity() ?: 1;
      /** @var \Drupal\commerce\PurchasableEntityInterface $entity */
      $entity = $bundleItem->getCurrentVariation();
      $service = $this->stockServiceManager->getService($entity);
      $level = $service->getStockChecker()
        ->getTotalStockLevel($entity, $locations);
      return floor($level / $quantity);
    }, $bundle->getBundleItems());

    return min($levels);
  }

  /**
   * {@inheritdoc}
   */
  public function getIsInStock(
    PurchasableEntityInterface $bundle,
    array $locations
  ) {
    $this->assertBundleInterface($bundle);
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
    $this->assertBundleInterface($bundle);
    $entities = $this->getAllPurchasableEntities($bundle);
    /** @var \Drupal\commerce\PurchasableEntityInterface $entity */
    foreach ($entities as $entity) {
      $service = $this->stockServiceManager->getService($entity);
      $checker = $service->getStockChecker();
      if (!$checker->getIsAlwaysInStock($entity)) {
        return FALSE;
      }
    }
    return TRUE;
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

  /**
   * We can't change the signature of the above methods,
   * so we need to check explicit for the BundleInterface.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *
   * @throws \InvalidArgumentException
   *   In case the entity has not implemented the Drupal\commerce_product_bundle\Entity\BundleInterface.
   */
  protected function assertBundleInterface(PurchasableEntityInterface $entity) {
    if (!($entity instanceof BundleInterface)) {
      throw new \InvalidArgumentException('Bundle must implement Drupal\commerce_product_bundle\Entity\BundleInterface');
    }
  }

}

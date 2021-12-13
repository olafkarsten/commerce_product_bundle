<?php

namespace Drupal\commerce_product_bundle_stock;

use Drupal\commerce_stock\StockServiceConfig;
use Drupal\commerce_stock\StockServiceInterface;

/**
 * A stock service for always in stock products.
 */
class ProductBundleStockProxyService implements StockServiceInterface {

  /**
   * The stock checker.
   *
   * @var \Drupal\commerce_stock\StockCheckInterface
   */
  protected $stockChecker;

  /**
   * The stock updater.
   *
   * @var \Drupal\commerce_stock\StockUpdateInterface
   */
  protected $stockUpdater;

  /**
   * The stock service configuration.
   *
   * @var \Drupal\commerce_stock\StockServiceConfigInterface
   */
  protected $stockServiceConfig;

  /**
   * Constructs a new ProductBundleStockProxyService object.
   */
  public function __construct() {
    // The service manager is not injected into the constructor because it
    // needs to load before being used here.
    $stock_service_manager = \Drupal::service('commerce_stock.service_manager');
    $this->stockChecker = new ProductBundleStockProxy($stock_service_manager);
    $this->stockUpdater = $this->stockChecker;
    $this->stockServiceConfig = new StockServiceConfig();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'Product bundle stock proxy service';
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return 'commerce_product_bundle_stock';
  }

  /**
   * {@inheritdoc}
   */
  public function getStockChecker() {
    return $this->stockChecker;
  }

  /**
   * {@inheritdoc}
   */
  public function getStockUpdater() {
    return $this->stockUpdater;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->stockServiceConfig;
  }

}

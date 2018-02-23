<?php

namespace Drupal\Tests\commerce_product_bundle_stock\Functional;

use Drupal\commerce_store\StoreCreationTrait;
use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\Tests\commerce_product_bundle\Functional\ProductBundleBrowserTestBase;

/**
 * Defines base class for commerce stock test cases.
 */
abstract class ProductBundleStockBrowserTestBase extends ProductBundleBrowserTestBase {

  use EntityReferenceTestTrait;
  use StoreCreationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_stock',
    'commerce_stock_local',
    'commerce_stock_ui',
    'commerce_product_bundle',
    'commerce_product_bundle_stock',
  ];

  /**
   * The stock service manager.
   *
   * @var \Drupal\commerce_stock\StockServiceManager
   */
  protected $stockServiceManager;

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return array_merge([
      'administer commerce_product',
      'administer commerce_product_type',
      'administer commerce_product fields',
      'administer commerce_product_variation fields',
      'administer commerce_product_variation display',
      'administer commerce_stock',
      'administer commerce stock location entities',
      'administer commerce stock location types',
      'add commerce stock location entities',
      'delete commerce stock location entities',
      'view commerce stock location entities',
      'access commerce_product overview',
    ], parent::getAdministratorPermissions());
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->markTestSkipped('Broken Due to commerce_stock update. #2947320');
    parent::setUp();

    $this->stockServiceManager = $this->container->get('commerce_stock.service_manager');
  }

}

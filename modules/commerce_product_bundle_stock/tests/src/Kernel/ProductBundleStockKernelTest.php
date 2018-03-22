<?php

namespace Drupal\Tests\commerce_product_bundle_stock\Kernel;

/**
 * Tests the product bundle stock service manager.
 *
 * @requires module commerce_stock
 * @group commerce_product_bundle
 */
class ProductBundleStockKernelTest extends ProductBundleStockKernelTestBase {

  /**
   * Wether the service gets collected by the StockServiceManager.
   */
  public function testServiceIsRegistered() {
    /** @var \Drupal\commerce_stock\StockServiceManagerInterface $serviceManager */
    $serviceManager = \Drupal::service('commerce_stock.service_manager');
    self::assertContains('commerce_product_bundle_stock', array_keys($serviceManager->listServiceIds()));
  }

}

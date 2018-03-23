<?php

namespace Drupal\Tests\commerce_product_bundle_stock\Kernel;

/**
 * Tests the product bundle stock service manager.
 *
 * //@group commerce_product_bundle
 *
 * @requires module commerce_stock
 */
class ProductBundleStockKernelTest extends ProductBundleStockKernelTestBase {

  /**
   * Wether the service gets collected by the StockServiceManager.
   *
   * @requires module commerce_stock
   */
  public function testServiceIsRegistered() {
    /** @var \Drupal\commerce_stock\StockServiceManagerInterface $serviceManager */
    $serviceManager = \Drupal::service('commerce_stock.service_manager');
    self::assertContains('commerce_product_bundle_stock', array_keys($serviceManager->listServiceIds()));
  }

}

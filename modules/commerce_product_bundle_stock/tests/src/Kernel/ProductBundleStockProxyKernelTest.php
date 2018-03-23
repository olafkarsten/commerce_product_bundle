<?php

namespace Drupal\Tests\commerce_product_bundle_stock\Kernel;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product_bundle\Entity\ProductBundle;
use Drupal\commerce_product_bundle\Entity\ProductBundleItem;
use Drupal\commerce_product_bundle_stock\ProductBundleStockProxy;

/**
 * Tests the product bundle stock proxy.
 *
 * @coversDefaultClass \Drupal\commerce_product_bundle_stock\ProductBundleStockProxy
 *
 * @group commerce_product_bundle
 *
 */
class ProductBundleStockProxyKernelTest extends ProductBundleStockKernelTestBase {

  /**
   * The product bundle.
   *
   * @var \Drupal\commerce_product_bundle\Entity\ProductBundle
   */
  protected $bundle;

  /**
   * Sets up the the product bundle we need for test.
   *
   * @ToDo Try to mock at least parts of it, instead of relying on real objects.
   */
  public function setup() {
    parent::setup();

    $variations = [];
    for ($i = 1; $i <= 5; $i++) {
      $variation = ProductVariation::create([
        'type' => 'default',
        'sku' => strtolower($this->randomMachineName()),
        'title' => $this->randomString(),
        'status' => $i % 2,
      ]);
      $variation->save();
      $variations[] = $variation;
    }
    $variations = array_reverse($variations);
    $product = Product::create([
      'type' => 'default',
      'variations' => $variations,
    ]);
    $product->save();
    $product1 = $product;

    $variations = [];
    for ($i = 1; $i <= 3; $i++) {
      $variation = ProductVariation::create([
        'type' => 'default',
        'sku' => strtolower($this->randomMachineName()),
        'title' => $this->randomString(),
        'status' => TRUE,
      ]);
      $variation->save();
      $variations[] = $variation;
    }
    $variations = array_reverse($variations);
    $product = Product::create([
      'type' => 'default',
      'variations' => $variations,
    ]);
    $product->save();
    $product2 = $product;

    $bundleItem1 = ProductBundleItem::create([
      'type' => 'default',
      'uid' => $this->user->id(),
      'title' => 'testBundle',
      'status' => TRUE,
    ]);
    $bundleItem1->setProduct($product1);
    $bundleItem2 = ProductBundleItem::create(['type' => 'default']);
    $bundleItem2->setProduct($product2);

    $bundle = ProductBundle::create(['type' => 'default']);
    $bundle->addBundleItem($bundleItem1);
    $bundle->addBundleItem($bundleItem2);
    $bundle->save();
    $this->reloadEntity($bundle);
    $this->bundle = $bundle;
  }

  /**
   * Tests the product bundle proxy.
   *
   * @covers ::getIsStockManaged
   * @covers ::getIsAlwaysInStock
   * @covers ::getIsInStock
   *
   * @ToDo Add tests with real stock checking.
   */
  public function testProductBundleStockProxy() {
    $stockServiceManager = \Drupal::service('commerce_stock.service_manager');
    $proxy = new ProductBundleStockProxy($stockServiceManager);
    $this->assertTrue($proxy->getIsStockManaged($this->bundle));
    $this->assertTrue($proxy->getIsAlwaysInStock($this->bundle));
    $this->assertTrue($proxy->getIsInStock($this->bundle, []));
  }

}

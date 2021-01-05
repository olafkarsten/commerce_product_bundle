<?php

namespace Drupal\Tests\commerce_product_bundle\Kernel;

use Drupal\commerce_product_bundle\Entity\ProductBundle;
use Drupal\commerce_product_bundle\Entity\ProductBundleItem;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the product bundle item storage.
 *
 * @group commerce_product_bundle
 */
class BundleItemStorageTest extends CommerceProductBundleKernelTestBase {

  /**
   * The product bundlte item storage.
   *
   * @var \Drupal\commerce_product_bundle\ProductBundleItemStorageInterface
   */
  protected $bundleItemStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->bundleItemStorage = $this->container->get('entity_type.manager')
      ->getStorage('commerce_product_bundle_i');

    $user = $this->createUser([], ['administer commerce_product_bundle']);
    $this->container->get('current_user')->setAccount($user);
  }

  /**
   * Tests loadEnabled() function.
   */
  public function testLoadEnabled() {
    $bundle_items = [];
    for ($i = 1; $i <= 3; $i++) {
      $bundle_item = ProductBundleItem::create([
        'type' => 'default',
        'title' => $this->randomString(),
        'status' => $i % 2,
      ]);
      $bundle_item->save();
      $bundle_items[] = $bundle_item;
    }
    $bundle_items = array_reverse($bundle_items);
    $product_bundle = ProductBundle::create([
      'type' => 'default',
      'bundle_items' => $bundle_items,
    ]);
    $product_bundle->save();

    $itemsFiltered = $this->bundleItemStorage->loadEnabled($product_bundle);
    $this->assertEquals(2, count($itemsFiltered), '2 out of 3 bundle items are enabled');
  }

  /**
   * Tests loadFromContext() method.
   */
  public function testLoadFromContext() {
    $bundle_items = [];
    for ($i = 1; $i <= 3; $i++) {
      $bundle_item = ProductBundleItem::create([
        'type' => 'default',
        'title' => $this->randomString(),
        'status' => $i % 2,
      ]);
      $bundle_item->save();
      $bundle_items[] = $bundle_item;
    }
    $bundle_items = array_reverse($bundle_items);
    $product_bundle = ProductBundle::create([
      'type' => 'default',
      'bundle_items' => $bundle_items,
    ]);
    $product_bundle->save();
    $request = Request::create('');
    $request->query->add([
      'v' => end($bundle_items)->id(),
    ]);
    // Push the request to the request stack so `current_route_match` works.
    $this->container->get('request_stack')->push($request);
    $context_bundle_item = $this->bundleItemStorage->loadFromContext($product_bundle);
    $this->assertEquals($request->query->get('v'), $context_bundle_item->id());

    // Invalid bundle item id returns first bundle item.
    $request = Request::create('');
    $request->query->add([
      'v' => '1111111',
    ]);
    // Push the request to the request stack so `current_route_match` works.
    $this->container->get('request_stack')->push($request);
    $context_bundle_item = $this->bundleItemStorage->loadFromContext($product_bundle);
    $this->assertEquals($bundle_items[0]->id(), $context_bundle_item->id());
  }

}

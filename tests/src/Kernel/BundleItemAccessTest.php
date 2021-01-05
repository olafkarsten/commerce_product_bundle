<?php

namespace Drupal\Tests\commerce_product_bundle\Kernel;

use Drupal\commerce_product_bundle\Entity\ProductBundle;
use Drupal\commerce_product_bundle\Entity\ProductBundleItem;

/**
 * Tests the product bundle item access control.
 *
 * @coversDefaultClass \Drupal\commerce_product_bundle\BundleItemAccessControlHandler
 * @group commerce_product_bundle
 */
class BundleItemAccessTest extends CommerceProductBundleKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_product_bundle_test',
  ];

  /**
   * @covers ::checkAccess
   */
  public function testAccess() {

    /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundle_item */
    $bundle_item = ProductBundleItem::create([
      'type' => 'default',
      'title' => $this->randomString(),
      'status' => 1,
    ]);
    $bundle_item->save();
    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $product */
    $product_bundle = ProductBundle::create([
      'type' => 'default',
      'title' => 'My Product Bundle Title',
      'bundle_items' => [$bundle_item],
    ]);
    $product_bundle->save();
    $bundle_item = $this->reloadEntity($bundle_item);

    $account = $this->createUser([], ['access administration pages']);
    $this->assertFalse($bundle_item->access('view', $account));
    $this->assertFalse($bundle_item->access('update', $account));
    $this->assertFalse($bundle_item->access('delete', $account));

    $account = $this->createUser([], ['view commerce_product_bundle']);
    $this->assertTrue($bundle_item->access('view', $account));
    $this->assertFalse($bundle_item->access('update', $account));
    $this->assertFalse($bundle_item->access('delete', $account));

    $account = $this->createUser([], ['update any default commerce_product_bundle']);
    $this->assertFalse($bundle_item->access('view', $account));
    $this->assertFalse($bundle_item->access('update', $account));
    $this->assertFalse($bundle_item->access('delete', $account));

    $account = $this->createUser([], [
      'manage default commerce_product_bundle_i',
    ]);
    $this->assertFalse($bundle_item->access('view', $account));
    $this->assertTrue($bundle_item->access('update', $account));
    $this->assertTrue($bundle_item->access('delete', $account));

    $account = $this->createUser([], ['administer commerce_product_bundle']);
    $this->assertTrue($bundle_item->access('view', $account));
    $this->assertTrue($bundle_item->access('update', $account));
    $this->assertTrue($bundle_item->access('delete', $account));

    // Broken product bundle reference.
    $bundle_item->set('bundle_id', '999');
    $account = $this->createUser([], ['manage default commerce_product_bundle_i']);
    $this->assertFalse($bundle_item->access('view', $account));
    $this->assertFalse($bundle_item->access('update', $account));
    $this->assertFalse($bundle_item->access('delete', $account));
  }

  /**
   * @covers ::checkCreateAccess
   */
  public function testCreateAccess() {
    $access_control_handler = \Drupal::entityTypeManager()
      ->getAccessControlHandler('commerce_product_bundle_i');

    $account = $this->createUser([], ['access content']);
    $this->assertFalse($access_control_handler->createAccess('test', $account));

    $account = $this->createUser([], ['administer commerce_product_bundle']);
    $this->assertTrue($access_control_handler->createAccess('default', $account));

    $account = $this->createUser([], ['manage default commerce_product_bundle_i']);
    $this->assertTrue($access_control_handler->createAccess('default', $account));
  }

  /**
   * Tests that bundle items without access are not available on the frontend.
   */
  public function testFrontendFiltering() {
    /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundle_item */
    $bundle_item = ProductBundleItem::create([
      'type' => 'default',
      'title' => $this->randomString(),
      'status' => 1,
    ]);
    $bundle_item->save();
    /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundle_item */
    $bundle_item_denied = ProductBundleItem::create([
      'type' => 'default',
      'title' => 'DENY_' . $this->randomMachineName(),
      'status' => 0,
    ]);
    $bundle_item_denied->save();
    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $product_bundle */
    $product_bundle = ProductBundle::create([
      'type' => 'default',
      'title' => 'My Product Bundle Title',
      'bundle_items' => [$bundle_item, $bundle_item_denied],
    ]);
    $product_bundle->save();
    $product_bundle = $this->reloadEntity($product_bundle);

    /** @var \Drupal\commerce_product_bundle\ProductBundleItemStorageInterface $bundle_item_storage */
    $bundle_item_storage = $this->container->get('entity_type.manager')
      ->getStorage('commerce_product_bundle_i');
    $this->container->get('request_stack')
      ->getCurrentRequest()->query->set('v', $bundle_item_denied->id());
    $context = $bundle_item_storage->loadFromContext($product_bundle);
    $this->assertNotEquals($bundle_item_denied->id(), $context->id());
    $this->assertEquals($bundle_item->id(), $context->id());

    $enabled = $bundle_item_storage->loadEnabled($product_bundle);
    $this->assertEquals(1, count($enabled));
  }

  /**
   * Tests route access for variations.
   */
  public function testRouteAccess() {
    /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundle_item */
    $bundle_item = ProductBundleItem::create([
      'type' => 'default',
      'title' => $this->randomString(),
      'status' => 1,
      'locked' => 0,
    ]);
    $bundle_item->save();
    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $product */
    $product_bundle = ProductBundle::create([
      'type' => 'default',
      'title' => 'My Product Bundle Title',
      'bundle_items' => [$bundle_item],
      'bundleItemType' => 'default',
      'locked' => 0,
    ]);
    $product_bundle->save();
    $bundle_item = $this->reloadEntity($bundle_item);

    $account = $this->createUser([], ['administer commerce_product_bundle']);
    $this->assertTrue($bundle_item->toUrl('collection')->access($account));
    $this->assertTrue($bundle_item->toUrl('add-form')->access($account));
    $this->assertTrue($bundle_item->toUrl('edit-form')->access($account));
    $this->assertTrue($bundle_item->toUrl('delete-form')->access($account));

    $account = $this->createUser([], ['manage default commerce_product_bundle_i']);
    $this->assertTrue($bundle_item->toUrl('collection')->access($account));
    $this->assertTrue($bundle_item->toUrl('add-form')->access($account));
    $this->assertTrue($bundle_item->toUrl('edit-form')->access($account));
    $this->assertTrue($bundle_item->toUrl('delete-form')->access($account));

    $account = $this->createUser([], ['access commerce_product_bundle overview']);
    $this->assertTrue($bundle_item->toUrl('collection')->access($account));
    $this->assertFalse($bundle_item->toUrl('add-form')->access($account));
    $this->assertFalse($bundle_item->toUrl('edit-form')->access($account));
    $this->assertFalse($bundle_item->toUrl('delete-form')->access($account));
  }

}

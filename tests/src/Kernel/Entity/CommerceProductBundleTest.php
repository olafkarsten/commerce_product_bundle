<?php

namespace Drupal\Tests\commerce_product_bundle\Kernel\Entity;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product_bundle\Entity\Productbundle;
use Drupal\commerce_product_bundle\Entity\ProductBundleItem;
use Drupal\field\Entity\FieldConfig;
use Drupal\Tests\commerce_product_bundle\Kernel\CommerceProductBundleKernelTestBase;
use Drupal\user\UserInterface;

/**
 * Test the Product Bundle Item entity.
 *
 * @coversDefaultClass \Drupal\commerce_product_bundle\Entity\ProductBundle
 *
 * @group commerce_product_bundle
 */
class CommerceProductBundleTest extends CommerceProductBundleKernelTestBase {

  /**
   * @covers ::getTitle
   * @covers ::setTitle
   * @covers ::isPublished
   * @covers ::setPublished
   * @covers ::getCreatedTime
   * @covers ::setCreatedTime
   * @covers ::postDelete
   * @covers ::setBundleItems
   * @covers ::addBundleItem
   * @covers ::removeBundleItem
   * @covers ::getBundleItemIds
   * @covers ::hasBundleItem
   * @covers ::hasBundleItems
   */
  public function testBundle() {

    $variations = [];
    for ($i = 1; $i <= 5; $i++) {
      $variation = ProductVariation::create([
        'type' => 'default',
        'sku' => strtolower($this->randomMachineName()),
        'title' => $this->randomString(),
        'status' => $i % 2,
        'uid' => $this->user->id(),
      ]);
      $variation->save();
      $variations[] = $variation;
    }
    $variations = array_reverse($variations);
    $product = Product::create([
      'type' => 'default',
      'variations' => $variations,
      'uid' => $this->user->id(),
    ]);
    $product->save();
    $product1 = $this->reloadEntity($product);

    $variations = [];
    for ($i = 1; $i <= 3; $i++) {
      $variation = ProductVariation::create([
        'type' => 'default',
        'sku' => strtolower($this->randomMachineName()),
        'title' => $this->randomString(),
        'status' => TRUE,
        'uid' => $this->user->id(),
      ]);
      $variation->save();
      $variations[] = $variation;
    }
    $variations = array_reverse($variations);
    $product = Product::create([
      'type' => 'default',
      'variations' => $variations,
      'uid' => $this->user->id(),
    ]);
    $product->save();
    $product2 = $this->reloadEntity($product);

    $bundleItem = ProductBundleItem::create([
      'type' => 'default',
      'uid' => $this->user->id(),
      'title' => 'testBundle1',
      'status' => TRUE,
    ]);
    $bundleItem->setProduct($product1);
    $bundleItem->save();
    $bundleItem = $this->reloadEntity($bundleItem);

    $bundleItem2 = ProductBundleItem::create([
      'type' => 'default',
      'uid' => $this->user->id(),
      'title' => 'testBundle2',
      'status' => TRUE,
    ]);
    $bundleItem2->setProduct($product2);
    $bundleItem2->save();
    $bundleItem2 = $this->reloadEntity($bundleItem2);

    $bundle = ProductBundle::create(
      [
        'type' => 'default',
        'uid' => $this->user->id(),
        'status' => TRUE,
      ]);

    $bundle->save();
    $bundle->setTitle('My testtitle');
    $this->assertEquals('My testtitle', $bundle->getTitle());

    $this->assertTrue($bundle->hasField('body'));
    $created_field = $bundle->getFieldDefinition('body');
    $this->assertInstanceOf(FieldConfig::class, $created_field);
    $this->assertEquals(TRUE, $bundle->isPublished());
    $bundle->setPublished(FALSE);
    $this->assertEquals(FALSE, $bundle->isPublished());

    $bundle->setCreatedTime(635879700);
    $this->assertEquals(635879700, $bundle->getCreatedTime());

    $bundle->setOwner($this->user);
    $this->assertEquals($this->user, $bundle->getOwner());
    $this->assertEquals($this->user->id(), $bundle->getOwnerId());

    $bundle->setOwnerId(0);
    $this->assertInstanceOf(UserInterface::class, $bundle->getOwner());
    $this->assertTrue($bundle->getOwner()->isAnonymous());
    // Whether non existend user returns anonymous.
    $bundle->setOwnerId(99);
    $this->assertInstanceOf(UserInterface::class, $bundle->getOwner());
    $this->assertTrue($bundle->getOwner()->isAnonymous());
    $this->assertEquals(99, $bundle->getOwnerId());

    $bundle->setOwnerId($this->user->id());
    $this->assertEquals($this->user, $bundle->getOwner());
    $this->assertEquals($this->user->id(), $bundle->getOwnerId());
    $this->assertFalse($bundle->hasBundleItems());
    $bundle->setBundleItems([$bundleItem]);
    $bundle->save();
    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $bundle */
    $bundle = $this->reloadEntity($bundle);
    $items = $bundle->getBundleItems();
    $this->assertEquals($items[0]->Id(), $bundleItem->Id());
    $this->assertTrue($bundle->hasBundleItems());
    $this->assertTrue($bundle->hasBundleItem($bundleItem));
    $this->assertFalse($bundle->hasBundleItem($bundleItem2));

    $bundle->addBundleItem($bundleItem2);
    $bundle->save();
    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $bundle */
    $bundle = $this->reloadEntity($bundle);
    $items = $bundle->getBundleItems();
    $ids = $bundle->getBundleItemIds();
    $this->assertEquals($bundleItem->Id(), $items[0]->Id());
    $this->assertEquals($bundleItem->Id(), $ids[0]);
    $this->assertEquals($bundleItem2->Id(), $items[1]->Id());
    $this->assertEquals($bundleItem2->Id(), $ids[1]);
    $this->assertTrue($bundle->hasBundleItem($bundleItem2));

    $test = array_map(function ($item) {
      /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $item */
      $test_item = $item->getCurrentVariation();
      return $test_item;
    }, $items);

    $bundle->removeBundleItem($bundleItem);
    $bundle->save();
    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $bundle */
    $bundle = $this->reloadEntity($bundle);
    $items = $bundle->getBundleItems();
    $this->assertEquals(1, count($items));
    $this->assertEquals($bundleItem2->Id(), $items[0]->Id());
    $this->assertFalse($bundle->hasBundleItem($bundleItem));

    $this->assertNull($bundle->getPrice());
    // 0.00 is a valid Price. Check that we don't inadvertently filter it by some
    // conditionals.
    $bundle->setPrice(new Price('0.00', 'USD'));
    $this->assertEquals($bundle->getPrice(), new Price('0.00', 'USD'));
    $bundle->setPrice(new Price('3.33', 'USD'));
    $this->assertEquals($bundle->getPrice(), new Price('3.33', 'USD'));

    $bundle->delete();
    $this->assertNull(ProductBundle::load($bundle->Id()));
    $this->assertNull(ProductBundleItem::load($bundleItem2->Id()));

  }

  /**
   * Tests bundle item's canonical URL.
   */
  public function testCanonicalBundleItemLink() {
    $bundle_item = ProductBundleItem::create([
      'type' => 'default',
      'uid' => $this->user->id(),
      'title' => 'testBundle1',
      'status' => TRUE,
    ]);
    $bundle_item->save();
    $bundle = ProductBundle::create(
      [
        'type' => 'default',
        'bundle_items' => [$bundle_item],
        'status' => TRUE,
      ]);
    $bundle->save();

    $bundle_url = $bundle->toUrl()->toString();
    $bundle_item_url = $bundle_item->toUrl()->toString();
    $this->assertEquals($bundle_url . '?v=' . $bundle_item->id(), $bundle_item_url);
  }

}

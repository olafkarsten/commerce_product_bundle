<?php

namespace Drupal\Tests\commerce_product_bundle\Kernel\Entity;

use Drupal\commerce_product_bundle\Entity\ProductBundleItem;
use Drupal\Tests\commerce_product_bundle\Kernel\CommerceProductBundleKernelTestBase;

/**
 * Test the Product Bundle Item entity.
 *
 * @coversDefaultClass \Drupal\commerce_product_bundle\Entity\ProductBundleItem
 *
 * @group commerce_product_bundle
 */
class CommerceProductBundleItemTest extends CommerceProductBundleKernelTestBase {

  /**
   * @covers ::getTitle
   * @covers ::setTitle
   * @covers ::isActive
   * @covers ::setActive
   * @covers ::getCreatedTime
   * @covers ::setCreatedTime
   */
  public function testBundleItem() {

    $bundleItem = ProductBundleItem::create([
      'type' => 'default',
    ]);

    $bundleItem->save();

    $bundleItem->setTitle('My testtitle');
    $this->assertEquals('My testtitle', $bundleItem->getTitle());

    $this->assertEquals(TRUE, $bundleItem->isActive());
    $bundleItem->setActive(FALSE);
    $this->assertEquals(FALSE, $bundleItem->isActive());

    $bundleItem->setCreatedTime(635879700);
    $this->assertEquals(635879700, $bundleItem->getCreatedTime());

    $bundleItem->setOwner($this->user);
    $this->assertEquals($this->user, $bundleItem->getOwner());
    $this->assertEquals($this->user->id(), $bundleItem->getOwnerId());
    $bundleItem->setOwnerId(0);
    $this->assertEquals(NULL, $bundleItem->getOwner());
    $bundleItem->setOwnerId($this->user->id());
    $this->assertEquals($this->user, $bundleItem->getOwner());
    $this->assertEquals($this->user->id(), $bundleItem->getOwnerId());

  }

}

<?php

namespace Drupal\Tests\commerce_product_bundle\Kernel\Entity;

use Drupal\commerce_product_bundle\Entity\Productbundle;
use Drupal\commerce_product_bundle\Entity\ProductBundleItem;
use Drupal\field\Entity\FieldConfig;
use Drupal\Tests\commerce_product_bundle\Kernel\CommerceProductBundleKernelTestBase;

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
   */
  public function testBundle() {

    $bundleItem = ProductBundleItem::create([
      'type' => 'default',
    ]);
    $bundleItem->save();

    $bundle = Productbundle::create([
      'type' => 'default',
    ]);
    $bundle->save();

    $bundle->setTitle('My testtitle');
    $this->assertEquals('My testtitle', $bundle->getTitle());

    // Confirm the attached fields are there.
    $this->assertTrue($bundle->hasField('bundle_items'));
    $created_field = $bundle->getFieldDefinition('bundle_items');
    $this->assertInstanceOf(FieldConfig::class, $created_field);
    $this->assertEquals('commerce_product_bundle_i', $created_field->getSetting('target_type'));
    $this->assertEquals('default:commerce_product_bundle_i', $created_field->getSetting('handler'));

    $this->assertTrue($bundle->hasField('stores'));
    $created_field = $bundle->getFieldDefinition('stores');
    $this->assertInstanceOf(FieldConfig::class, $created_field);
    $this->assertEquals('commerce_store', $created_field->getSetting('target_type'));
    $this->assertEquals('default:commerce_store', $created_field->getSetting('handler'));

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
    $this->assertEquals(NULL, $bundle->getOwner());
    $bundle->setOwnerId($this->user->id());
    $this->assertEquals($this->user, $bundle->getOwner());
    $this->assertEquals($this->user->id(), $bundle->getOwnerId());

    $bundle->setBundleItems([$bundleItem]);
    $bundle->save();
    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $bundle */
    $bundle = $this->reloadEntity($bundle);
    $items = $bundle->getBundleItems();
    $this->assertEquals($items[0]->Id(), $bundleItem->Id());

    $bundle->delete();
    $this->assertFalse(ProductBundle::load($bundle->Id()));
    $this->assertFalse(ProductBundleItem::load($bundleItem->Id()));

  }

}

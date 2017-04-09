<?php

namespace Drupal\Tests\commerce_product_bundle\Kernel\Entity;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product_bundle\Entity\ProductBundle;
use Drupal\commerce_product_bundle\Entity\ProductBundleItem;
use Drupal\field\Entity\FieldConfig;
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

    $bundleItem->setMaximumQuantity(55);
    $this->assertEquals(55, $bundleItem->getMaximumQuantity());
    $bundleItem->setMinimumQuantity(11);
    $this->assertEquals(11, $bundleItem->getMinimumQuantity());
    $bundleItem->setQuantity(12);
    $this->assertEquals(12, $bundleItem->getQuantity());

    $this->assertFalse($bundleItem->hasVariations());

    $this->assertEquals(TRUE, $bundleItem->isActive());
    $bundleItem->setActive(FALSE);
    $this->assertEquals(FALSE, $bundleItem->isActive());

    $price = new Price('55.55', 'USD');
    $this->assertFalse($bundleItem->hasUnitPrice());
    $bundleItem->setUnitPrice($price);
    $this->assertTrue($bundleItem->hasUnitPrice());
    $this->assertEquals($price, $bundleItem->getUnitPrice());
    $this->assertEquals('55.55', $price->getNumber());
    $this->assertEquals('USD', $price->getCurrencyCode());

    // Confirm the attached fields are there.
    $this->assertTrue($bundleItem->hasField('variations'));
    $created_field = $bundleItem->getFieldDefinition('variations');
    $this->assertInstanceOf(FieldConfig::class, $created_field);
    $this->assertEquals('commerce_product_variation', $created_field->getSetting('target_type'));
    $this->assertEquals('default:commerce_product_variation', $created_field->getSetting('handler'));

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

    $variations = [];
    for ($i = 1; $i <= 5; $i++) {
      $variation = ProductVariation::create([
        'type'   => 'default',
        'sku'    => strtolower($this->randomMachineName()),
        'title'  => $this->randomString(),
        'status' => $i % 2,
      ]);
      $variation->save();
      $variations[] = $variation;
    }
    $variations = array_reverse($variations);
    $product = Product::create([
      'type'       => 'default',
      'variations' => $variations,
    ]);
    $product->save();

    $bundleItem->setProduct($product);
    $this->assertEquals($product->id(), $bundleItem->getProduct()->id());

    $this->assertFalse($bundleItem->hasVariations());
    $this->assertFalse($bundleItem->hasVariation($variations[0]));
    // Wether the bundleItem returns only the enabled variations.
    $this->assertTrue(count($bundleItem->getVariations()) == 3);

    $bundleItem->setVariations($variations);
    $this->assertTrue($bundleItem->hasVariations());
    $this->assertTrue($bundleItem->hasVariation($variations[0]));
    $this->assertEquals($variations[0]->id(), $bundleItem->getDefaultVariation()->id());
    $this->assertEquals($variations[0]->id(), $bundleItem->getCurrentVariation()->id());
    $bundleItem->setCurrentVariation($variations[4]);
    $this->assertEquals($variations[4]->id(), $bundleItem->getCurrentVariation()->id());

    $this->assertEquals(0, $bundleItem->getVariationIndex($variations[0]));
    $bundleItem->removeVariation($variations[0]);
    $this->assertFalse($bundleItem->hasVariation($variations[0]));

    // Wether the backreference to the bundle gets saved on bundle save.
    $this->assertNull($bundleItem->getBundleId());
    $bundle = ProductBundle::create(['type' => 'default']);
    $bundle->addBundleItem($bundleItem);
    $bundle->save();
    $bundleItem = $this->reloadEntity($bundleItem);
    $this->assertEquals($bundle->id(), $bundleItem->getBundleId());

  }

}

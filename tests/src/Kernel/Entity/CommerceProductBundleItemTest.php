<?php

namespace Drupal\Tests\commerce_product_bundle\Kernel\Entity;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
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
   * @covers ::isRequired
   * @covers ::setRequired
   * @covers ::getCreatedTime
   * @covers ::setCreatedTime
   * @covers ::setMaximumQuantity
   * @covers ::getMaximumQuantity
   * @covers ::setMinimumQuantity
   * @covers ::getMinimumQuantity
   * @covers ::setQuantity
   * @covers ::getQuantity
   * @covers ::hasUnitPrice
   * @covers ::getUnitPrice
   * @covers ::setUnitPrice
   */
  public function testBundleItem() {

    $bundleItem = ProductBundleItem::create([
      'type' => 'default',
    ]);

    $bundleItem->save();

    // Confirm the attached fields are there.
    $this->assertTrue($bundleItem->hasField('variations'));
    $created_field = $bundleItem->getFieldDefinition('variations');
    $this->assertInstanceOf(FieldConfig::class, $created_field);
    $this->assertEquals('commerce_product_variation', $created_field->getSetting('target_type'));
    $this->assertEquals('default:commerce_product_variation', $created_field->getSetting('handler'));

    $bundleItem->setTitle('My testtitle');
    $this->assertEquals('My testtitle', $bundleItem->getTitle());

    $this->assertEquals(TRUE, $bundleItem->isRequired());
    $bundleItem->setRequired(FALSE);
    $this->assertEquals(FALSE, $bundleItem->isRequired());

    // Wether edge case of 0.0 price value works.
    $this->assertNull($bundleItem->getUnitPrice());
    $this->assertFalse($bundleItem->hasUnitPrice());
    $price = new Price('0.00', 'USD');
    $bundleItem->setUnitPrice($price);
    $this->assertTrue($bundleItem->hasUnitPrice());
    $this->assertEquals($price, $bundleItem->getUnitPrice());
    $this->assertEquals('0.0', $price->getNumber());
    $this->assertEquals('USD', $price->getCurrencyCode());

    $price = new Price('55.55', 'USD');
    $bundleItem->setUnitPrice($price);
    $this->assertTrue($bundleItem->hasUnitPrice());
    $this->assertEquals($price, $bundleItem->getUnitPrice());
    $this->assertEquals('55.55', $price->getNumber());
    $this->assertEquals('USD', $price->getCurrencyCode());

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

    $bundleItem->setMaximumQuantity(0);
    $violations = $bundleItem->validate()->getByField("max_quantity");
    $this->assertCount(1, $violations);

    $bundleItem->setMaximumQuantity(55);
    $this->assertEquals(55, $bundleItem->getMaximumQuantity());
    $violations = $bundleItem->validate()->getByField("max_quantity");
    $this->assertCount(0, $violations);

    $bundleItem->setMinimumQuantity(-1);
    $violations = $bundleItem->validate()->getByField("min_quantity");
    $this->assertCount(1, $violations);

    $bundleItem->setMinimumQuantity(11);
    $this->assertEquals(11, $bundleItem->getMinimumQuantity());
    $violations = $bundleItem->validate()->getByField("min_quantity");
    $this->assertCount(0, $violations);

    // Set a product, to prevent counting the required product reference
    // field into the violations when calling ::validate().
    $variation = ProductVariation::create([
      'type'   => 'default',
      'sku'    => strtolower($this->randomMachineName()),
      'title'  => $this->randomString(),
      'status' => 1,
    ]);
    $variation->save();
    $product = Product::create([
      'type'       => 'default',
      'variations' => [$variation],
    ]);
    $product->save();
    $product = $this->reloadEntity($product);
    $bundleItem->setProduct($product);

    $bundleItem->setMinimumQuantity(111);
    $violations = $bundleItem->validate();
    $this->assertCount(1, $violations);

    $bundleItem->setMaximumQuantity(222);
    $bundleItem->setMinimumQuantity(222);
    $violations = $bundleItem->validate();
    $this->assertCount(0, $violations);

    $bundleItem->setQuantity(12);
    $this->assertEquals(12, $bundleItem->getQuantity());
  }

  /**
   * Test the setters, getters and valdiation methods around the
   * reference product and variations.
   *
   * @covers ::setProduct
   * @covers ::getProduct
   * @covers ::getProductId
   * @covers ::hasProduct
   * @covers ::setVariations
   * @covers ::getVariations
   * @covers ::hasVariations
   * @covers ::addVariation
   * @covers ::setCurrentVariation
   * @covers ::getCurrentVariation
   */
  public function testVariationsAndProductMethods() {

    $bundleItem = ProductBundleItem::create([
      'type' => 'default',
    ]);
    $bundleItem->save();

    // Ensure nothing fatals if we call certain methods without setting the
    // variations reference or product reference.
    $this->assertNull($bundleItem->getVariations());
    $this->assertNull($bundleItem->getCurrentVariation());
    $this->assertNull($bundleItem->getVariationIds());
    $this->assertFalse($bundleItem->hasProduct());

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
    $product = $this->reloadEntity($product);
    $bundleItem->setProduct($product);
    $this->assertTrue($bundleItem->hasProduct());

    $this->assertEquals($product->id(), $bundleItem->getProductId());
    $this->assertFalse($bundleItem->hasVariations());
    $bundleItem->setVariations($variations);
    // Uncomment after https://www.drupal.org/project/commerce_product_bundle/issues/2837499
    // $this->assertCount(3, $bundleItem->getVariations());
    $this->assertTrue($bundleItem->hasVariations());
    $this->assertEquals($variations[0]->id(), $bundleItem->getDefaultVariation()->id());
    $this->assertEquals($variations[0]->id(), $bundleItem->getCurrentVariation()->id());
    $bundleItem->setCurrentVariation($variations[4]);
    $this->assertEquals($variations[4]->id(), $bundleItem->getCurrentVariation()->id());

    $bundleItem->removeVariation($variations[0]);
    // Uncomment after https://www.drupal.org/project/commerce_product_bundle/issues/2837499
    // $this->assertCount(2, $bundleItem->getVariations());
    // Wether the backreference to the bundle gets saved on bundle save.
    $this->assertNull($bundleItem->getBundleId());
    $bundle = ProductBundle::create(['type' => 'default']);
    $bundle->addBundleItem($bundleItem);
    $bundle->save();
    $bundleItem = $this->reloadEntity($bundleItem);
    $this->assertEquals($bundle->id(), $bundleItem->getBundleId());

    // Wether setting the variations sets the product reference.
    $bundleItem = ProductBundleItem::create([
      'type' => 'default',
    ]);
    $bundleItem->save();

    $bundleItem->setVariations($variations);
    $this->assertEquals($product->id(), $bundleItem->getProduct()->id());

    // @ToDo Test the bundle <> back reference.

    $freshBundleItem = ProductBundleItem::create([
      'type' => 'default',
    ]);
    $bundleItem->save();

    $values = [
      'id' => strtolower($this->randomMachineName(8)),
      'label' => $this->randomMachineName(),
      'orderItemType' => 'default',
    ];
    $variationType = $this->createEntity('commerce_product_variation_type', $values);
    $otherVariation = ProductVariation::create([
      'type'   => $variationType->getEntityTypeId(),
      'sku'    => strtolower($this->randomMachineName()),
      'title'  => $this->randomString(),
      'status' => $i % 2,
    ]);

    $freshBundleItem->addVariation($otherVariation);
    $this->assertFalse($freshBundleItem->getProduct());
    $this->assertNull($freshBundleItem->getVariations());

    $this::setExpectedException('\InvalidArgumentException');
    $bundleItem->addVariation($otherVariation);

    $this::setExpectedException('\InvalidArgumentException');
    $bundleItem->setVariations([$otherVariation]);

    $this::setExpectedException('\InvalidArgumentException');
    $bundleItem->setCurrentVariation($otherVariation);

  }

}

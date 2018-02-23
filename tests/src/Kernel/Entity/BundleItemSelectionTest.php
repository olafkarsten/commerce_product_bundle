<?php

namespace Drupal\Tests\commerce_product_bundle\Kernel;

use Drupal\commerce_product\Entity\Product;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\commerce_price\Price;
use Drupal\commerce_product_bundle\Entity\Productbundle;
use Drupal\commerce_product_bundle\Entity\ProductBundleItem;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * Tests the adjustment field.
 *
 * @group commerce
 */
class BundleItemSelectionTest extends CommerceProductBundleKernelTestBase {

  /**
   * The test entity.
   *
   * @var \Drupal\entity_test\Entity\EntityTest
   */
  protected $testEntity;

  /**
   * Test product bundle.
   *
   * @var \Drupal\commerce_product_bundle\Entity\ProductBundle
   */
  protected $productBundle;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'entity_reference_revisions',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'test_bundle_item_selection',
      'entity_type' => 'entity_test',
      'type' => 'commerce_product_bundle_item_selection',
      'cardinality' => FieldStorageConfig::CARDINALITY_UNLIMITED,
    ]);
    $field_storage->save();

    $field = FieldConfig::create([
      'field_name' => 'test_bundle_item_selection',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ]);
    $field->save();

    $entity = EntityTest::create([
      'name' => 'Test',
    ]);
    $entity->save();
    $this->testEntity = $entity;

    $variation = ProductVariation::create([
      'type'   => 'default',
      'sku'    => strtolower($this->randomMachineName()),
      'title'  => $this->randomString(),
      'status' => 1,
    ]);
    $variation->setPrice(new Price('33.3333', 'EUR'));
    $variation->save();

    $variation1 = ProductVariation::create([
      'type'   => 'default',
      'sku'    => strtolower($this->randomMachineName()),
      'title'  => $this->randomString(),
      'status' => 1,
    ]);
    $variation1->setPrice(new Price('44.4444', 'EUR'));
    $variation1->save();

    // Product is needed, because bundleItem checks for it, when setting
    // the product variations.
    $product = Product::create([
      'type'   => 'default',
      'title'  => $this->randomString(),
      'status' => 1,
    ]);
    $product->setVariations([$variation, $variation1]);
    $product->save();

    $bundleItem = ProductBundleItem::create([
      'type' => 'default',
      'title' => $this->randomString(),
    ]);
    $bundleItem->setVariations([$variation, $variation1]);
    $bundleItem->save();

    $bundle = Productbundle::create([
      'type' => 'default',
    ]);
    $bundle->setBundleItems([$bundleItem]);
    $bundle->save();
    $this->productBundle = $bundle;
  }

  /**
   * Tests the bundle item selection field..
   */
  public function testBundleItemSelection() {

    $bundleItems = $this->productBundle->getBundleItems();
    $variations = $bundleItems[0]->getVariations();

    /** @var \Drupal\Core\Field\FieldItemListInterface $bundle_item_selection_list */
    $bundle_item_selection_list = $this->testEntity->test_bundle_item_selection;
    $bundle_item_selection_list->appendItem([
      'qty' => 5,
      'bundle_item' => $bundleItems[0]->id(),
      'purchasable_entity' => $variations[0]->id(),
    ]);
    $bundle_item_selection = $bundle_item_selection_list->first();
    $this->assertEquals('5', $bundle_item_selection->get('qty')->getCastedValue());
    $this->assertEquals($bundleItems[0]->getTitle(), $bundle_item_selection->get('title')->getValue());
    $this->assertEquals($bundleItems[0]->id(), $bundle_item_selection->get('bundle_item')->getValue());
    $this->assertEquals($variations[0]->id(), $bundle_item_selection->get('purchasable_entity')->getValue());
    // Remember: We have no per item price, if the product bundle has a global price set.
    $this->assertEquals($variations[0]->getPrice()->getNumber(), $bundle_item_selection->get('unit_price_number')->getValue());
    $this->assertEquals($variations[0]->getPrice()->getCurrencyCode(), $bundle_item_selection->get('unit_price_currency_code')->getValue());

    $bundle = $this->productBundle->setPrice(new Price('1.11', 'USD'));
    $bundle->save();
    $bundleItems[0] = $this->reloadEntity($bundleItems[0]);

    $bundle_item_selection_list->appendItem([
      'qty' => 3,
      'bundle_item' => $bundleItems[0]->id(),
      'purchasable_entity' => $variations[1]->id(),
    ]);
    $bundle_item_selection = $bundle_item_selection_list->get(1);
    $this->assertEquals('3', $bundle_item_selection->get('qty')->getCastedValue());
    $this->assertEquals($bundleItems[0]->getTitle(), $bundle_item_selection->get('title')->getValue());
    $this->assertEquals($bundleItems[0]->id(), $bundle_item_selection->get('bundle_item')->getValue());
    $this->assertEquals($variations[1]->id(), $bundle_item_selection->get('purchasable_entity')->getValue());
    // Remember: We have no per item price, if the product bundle has a global price set.
    $this->assertNull($bundle_item_selection->get('unit_price_number')->getValue());
    $this->assertNull($bundle_item_selection->get('unit_price_currency_code')->getValue());

  }

}

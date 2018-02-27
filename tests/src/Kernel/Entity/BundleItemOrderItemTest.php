<?php

namespace Drupal\Tests\commerce_product_bundle\Kernel\Entity;

use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product_bundle\Entity\BundleItemOrderItem;
use Drupal\commerce_product_bundle\Entity\ProductBundle;
use Drupal\commerce_product_bundle\Entity\ProductBundleItem;
use Drupal\Tests\commerce_product_bundle\Kernel\CommerceProductBundleKernelTestBase;

/**
 * Test the Product Bundle Item entity.
 *
 * @coversDefaultClass \Drupal\commerce_product_bundle\Entity\BundleItemOrderItem
 *
 * @group commerce_product_bundle
 */
class BundleItemOrderItemTest extends CommerceProductBundleKernelTestBase {

  public static $modules = [
    'entity_reference_revisions',
    'profile',
    'commerce_order',
    'state_machine',
  ];

  /**
   * The product bundle entity.
   *
   * @var \Drupal\commerce_product_bundle\Entity\ProductBundle
   */
  protected $productBundle;

  /**
   * The product variation.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  protected $productVariation;

  /**
   * The commerce product bundle item.
   *
   * @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   */
  protected $productBundleItem;

  /**
   * The order item.
   *
   * @var \Drupal\commerce_order\Entity\OrderItemInterface
   */
  protected $orderItem;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('cpb_order_item');
    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_order_item');
    $this->installConfig('commerce_order');

    $variation = ProductVariation::create(['type' => 'default']);
    $variation->save();
    $this->productVariation = $variation;

    $bundleItem = ProductBundleItem::create(
      [
        'type' => 'default',
        'variations' => [$variation],
      ]
    );
    $bundleItem->save();
    $this->productBundleItem = $bundleItem;

    $productBundle = ProductBundle::create([
      'type' => 'default',
      'bundle_items' => [$bundleItem],
    ]);
    $productBundle->save();
    $this->productBundle = $productBundle;

    $this->orderItem = OrderItem::create([
      'type' => 'commerce_product_bundle_default',
    ]);
  }

  /**
   * @covers ::getTitle
   * @covers ::getCreatedTime
   * @covers ::getQuantity
   * @covers ::getUnitPrice
   * @covers ::getBundleItem
   * @covers ::getPurchasedEntity
   */
  public function testBundleItemOrderItem() {

    $created = new \DateTime('now');

    $bundleItemOrderItem = BundleItemOrderItem::create(
      [
        'title' => 'TestTitle',
        'created' => $created->getTimestamp(),
        'bundle_item' => $this->productBundleItem,
        'purchased_entity' => $this->productVariation,
        'unit_price' => new Price('222.33', 'USD'),
        'quantity' => '3',
        'total_price' => new Price('444.66', 'USD'),
      ]
    );

    self::assertEquals(new Price('444.66', 'USD'), $bundleItemOrderItem->getTotalPrice());
    $bundleItemOrderItem->save();
    $bundleItemOrderItem = $this->reloadEntity($bundleItemOrderItem);
    // Wether the total gets recalculation on presave.
    self::assertEquals(new Price('666.99', 'USD'), $bundleItemOrderItem->getTotalPrice());
    self::assertEquals('TestTitle', $bundleItemOrderItem->getTitle());
    $priceToTest = $bundleItemOrderItem->getUnitPrice();
    self::assertEquals(new Price('222.33', 'USD'), $priceToTest);
    self::assertEquals('222.33', $priceToTest->getNumber());
    self::assertEquals('USD', $priceToTest->getCurrencyCode());

    $price = new Price('55.55', 'USD');
    $bundleItemOrderItem->setUnitPrice($price);
    self::assertEquals($price, $bundleItemOrderItem->getUnitPrice());
    self::assertEquals('55.55', $price->getNumber());
    self::assertEquals('USD', $price->getCurrencyCode());
    self::assertEquals(new Price('166.65', 'USD'), $bundleItemOrderItem->getTotalPrice());

    self::assertEquals($created->getTimestamp(), $bundleItemOrderItem->getCreatedTime());
    self::assertEquals('3', $bundleItemOrderItem->getQuantity());

    self::assertEquals($this->productVariation->id(), $bundleItemOrderItem->getPurchasedEntityId());
    self::assertEquals($this->productBundleItem->id(), $bundleItemOrderItem->getBundleItem()->id());
  }

  /**
   * Tests the integration with commerce order item.
   */
  public function testBundleItemOrderItemOnOrderItem() {

    // Whether the config works and the order item type is available and
    // the bundle_item_order items field is attached.
    $orderItem = $this->orderItem;
    self::assertEquals('commerce_product_bundle_default', $this->orderItem->bundle());
    self::assertTrue($orderItem->hasField('bundle_item_order_items'));

    $bundleItemOrderItem0 = BundleItemOrderItem::create(
      [
        'title' => $this->randomString(),
        'bundle_item' => $this->productBundleItem,
        'purchased_entity' => $this->productVariation,
        'unit_price' => new Price('22.33', 'USD'),
        'quantity' => '3',
        'total_price' => new Price('66.99', 'USD'),
      ]
    );

    $bundleItemOrderItem1 = BundleItemOrderItem::create(
      [
        'title' => $this->randomString(),
        'bundle_item' => $this->productBundleItem,
        'purchased_entity' => $this->productVariation,
        'unit_price' => new Price('11.11', 'USD'),
        'quantity' => '1',
        'total_price' => new Price('22.22', 'USD'),
      ]
    );

    $orderItem->set('bundle_item_order_items', [
      $bundleItemOrderItem0,
      $bundleItemOrderItem1,
    ]);
    $orderItem->set('purchased_entity', $this->productBundle);
    $orderItem->save();
    $orderItem = $this->reloadEntity($orderItem);

    $bundleItemOrderItems = $orderItem->get('bundle_item_order_items')
      ->referencedEntities();
    self::assertEquals('3', $bundleItemOrderItems[0]->getQuantity());
    self::assertEquals('1', $bundleItemOrderItems[1]->getQuantity());

    $orderItemId = $orderItem->id();
    // Whether the order item id backreference is populated after order item save.
    self::assertEquals($orderItemId, $bundleItemOrderItems[0]->getOrderItemId());
    self::assertEquals($orderItemId, $bundleItemOrderItems[1]->getOrderItemId());

    // Whether recalculation of total is triggered by order item save.
    self::assertEquals('11.11', $bundleItemOrderItems[1]->getTotalPrice()
      ->getNumber());

    // Tests bundle item order item deletion if the order item has been deleted.
    $orderItem->delete();
    $bundleItemOrderItem0Exists = (bool) BundleItemOrderItem::load($bundleItemOrderItems[0]->id());
    $bundleItemOrderItem1Exists = (bool) BundleItemOrderItem::load($bundleItemOrderItems[1]->id());
    $this->assertEmpty($bundleItemOrderItem0Exists, 'The bundle item order item 0 has been deleted from database.');
    $this->assertEmpty($bundleItemOrderItem1Exists, 'The bundle item order item 1 has been deleted from database.');
  }

}

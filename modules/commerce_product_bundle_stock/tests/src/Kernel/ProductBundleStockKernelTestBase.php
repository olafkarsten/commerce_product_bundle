<?php

namespace Drupal\Tests\commerce_product_bundle_stock\Kernel;

use Drupal\Tests\commerce_product_bundle\Kernel\CommerceProductBundleKernelTestBase;

/**
 * Provides a base class for Commerce Product Bundle Stock tests.
 *
 * @requires module commerce_stock
 */
abstract class ProductBundleStockKernelTestBase extends CommerceProductBundleKernelTestBase {

  /**
   * @var \Drupal\commerce_stock\StockLocationInterface
   */
  protected $locationStub;

  /**
   * Modules to enable.
   *
   * Note that when a child class declares its own $modules list, that list
   * doesn't override this one, it just extends it.
   *
   * @var array
   */
  public static $modules = [
    'commerce_stock',
    'commerce_stock_local',
    'commerce_stock_field',
    'commerce_product_bundle_stock',
  ];

  /**
   * {@inheritdoc}
   * @requires module commerce_stock
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('commerce_stock_location_type');
    $this->installEntitySchema('commerce_stock_location');

    $this->installConfig(['commerce_stock']);
    $this->installConfig(['commerce_stock_local']);
    $this->installConfig(['commerce_product_bundle_stock']);

    $user = $this->createUser();
    $this->user = $this->reloadEntity($user);

    $location = $this->prophesize('Drupal\commerce_stock_local\Entity\StockLocation');
    $location->getId()->willReturn(1);
    $location->getName()->willReturn('TestLocation');
    $location->isActive()->willReturn(TRUE);
    $this->locationStub = $location->reveal();
  }

}

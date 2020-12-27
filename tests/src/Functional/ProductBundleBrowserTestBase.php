<?php

namespace Drupal\Tests\commerce_product_bundle\Functional;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_store\StoreCreationTrait;
use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;

/**
 * Defines base class to use in commerce_product_bundle
 * functional tests.
 *
 * @package Drupal\Tests\commerce_product_bundle\Functional
 */
abstract class ProductBundleBrowserTestBase extends CommerceBrowserTestBase {

  use StoreCreationTrait;
  use EntityReferenceTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_store',
    'commerce_product',
    'commerce_order',
    'commerce_product_bundle',
    'field_ui',
    'options',
    'taxonomy',
  ];

  /**
   * The products to test against.
   *
   * @var \Drupal\commerce_product\Entity\ProductInterface[]
   */
  protected $products;

  /**
   * The variations to test against.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface[]
   */
  protected $variations;

  /**
   * The stores to test against.
   *
   * @var \Drupal\commerce_store\Entity\StoreInterface[]
   */
  protected $stores;

  /**
   * @var \Drupal\commerce_product_bundle\Entity\BundleInterface
   */
  protected $bundle;

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return array_merge([
      'administer commerce_product',
      'administer commerce_product_bundle',
      'administer commerce_product_bundle_type',
      'administer commerce_product_bundle_i',
      'administer commerce_product_bundle_i_type',
      'administer commerce_product_type',
      'administer commerce_product fields',
      'administer commerce_product_variation fields',
      'administer commerce_product_variation display',
      'access commerce_product overview',
    ], parent::getAdministratorPermissions());
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->stores = [];
    for ($i = 0; $i < 3; $i++) {
      $this->stores[] = $this->createStore();
    }

    // Create some products to test against.
    for ($j = 1; $j <= 2; $j++) {

      $variations = [];
      for ($i = 1; $i <= 5; $i++) {
        $variation = ProductVariation::create([
          'type'   => 'default',
          'sku'    => strtolower($this->randomMachineName()),
          'title'  => $this->randomString(),
          'status' => $i % 2,
        ]);
        $variation->save();
        // Not sure yet, whether we need to keep the variations.
        // @todo Remove the comment or refactor.
        $this->variations[] = $variation;
      }
      $variations = array_reverse($variations);
      $product = Product::create([
        'type'       => 'default',
        'variations' => $variations,
      ]);
      $product->save();
      $this->products[] = $product;
    }

  }

}

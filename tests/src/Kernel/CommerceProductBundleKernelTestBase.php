<?php
/**
 * This file is part of the d8commerce package.
 *
 * @author Olaf Karsten <olaf.karsten@beckerundkarsten.de>
 */

namespace Drupal\Tests\commerce_product_bundle\Kernel;

use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;

/**
 * Provides a base class for Commerce Product Bundle tests.
 */
abstract class CommerceProductBundleKernelTestBase extends CommerceKernelTestBase {

  /**
   * Modules to enable.
   *
   * Note that when a child class declares its own $modules list, that list
   * doesn't override this one, it just extends it.
   *
   * @var array
   */
  public static $modules = [
    'address',
    'datetime',
    'entity',
    'options',
    'inline_entity_form',
    'views',
    'path',
    'commerce',
    'commerce_price',
    'commerce_store',
    'commerce_product',
    'commerce_product_bundle',
  ];

  /**
   * A sample user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('commerce_product_variation_type');
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_product_type');
    $this->installConfig(['commerce_product']);
    $this->installEntitySchema('commerce_product_bundle');
    $this->installEntitySchema('commerce_product_bundle_type');
    $this->installEntitySchema('commerce_product_bundle_i');
    $this->installEntitySchema('commerce_product_bundle_i_type');
    $this->installConfig(['commerce_product_bundle']);

    $user = $this->createUser();
    $this->user = $this->reloadEntity($user);
  }


}

<?php

namespace Drupal\Tests\commerce_product_bundle\Kernel;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;

/**
 * Provides a base class for Commerce Product Bundle tests.
 *
 * @requires module commerce_stock
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
    'commerce_order',
    'commerce_number_pattern',
    'commerce_product_bundle',
    'entity_reference_revisions',
    'profile',
    'state_machine',
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
    $this->installEntitySchema('commerce_product_bundle');
    $this->installEntitySchema('commerce_product_bundle_type');
    $this->installEntitySchema('commerce_product_bundle_i');
    $this->installEntitySchema('commerce_product_bundle_i_type');
    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_order_item');
    $this->installConfig([
      'commerce_number_pattern',
      'commerce_order',
      'commerce_product',
      'commerce_product_bundle',
    ]);

    // Create uid: 1 here so that it's skipped in test cases.
    $admin_user = $this->createUser();

    $user = $this->createUser([], [
      'view commerce_product_bundle',
      'view commerce_product',
    ]);
    $this->user = $this->reloadEntity($user);
    $this->drupalSetCurrentUser($this->user);
  }

  /**
   * Creates a new entity.
   *
   * @param string $entity_type
   *   The entity type to be created.
   * @param array $values
   *   An array of settings.
   *   Example: 'id' => 'foo'.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   A new entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createEntity($entity_type, array $values) {
    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    $storage = \Drupal::service('entity_type.manager')
      ->getStorage($entity_type);
    $entity = $storage->create($values);
    $status = $entity->save();
    $this->assertEquals(SAVED_NEW, $status, new FormattableMarkup('Created %label entity %type.', [
      '%label' => $entity->getEntityType()->getLabel(),
      '%type' => $entity->id(),
    ]));
    // The newly saved entity isn't identical to a loaded one, and would fail
    // comparisons.
    $entity = $storage->load($entity->id());

    return $entity;
  }

}

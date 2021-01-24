<?php

namespace Drupal\Tests\commerce_product_bundle\Functional;

use Drupal\commerce_order\Entity\OrderItemType;
use Drupal\commerce_product_bundle\Entity\ProductBundleItemType;
use Drupal\commerce_product_bundle\Entity\ProductBundleType;

/**
 * Tests the product bundle type UI.
 *
 * @group commerce_product_bundle
 */
class ProductBundleTypeTest extends ProductBundleBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  /**
   * Tests whether the default product type was created.
   */
  public function testDefault() {
    $product_bundle_type = ProductBundleType::load('default');
    $this->assertNotEmpty($product_bundle_type);

    $this->drupalGet('admin/commerce/config/product-bundle-types');
    $rows = $this->getSession()->getPage()->findAll('css', 'table tbody tr');
    $this->assertCount(1, $rows);
  }

  /**
   * Tests adding a product type.
   */
  public function testAdd() {
    $user = $this->drupalCreateUser(['administer commerce_product_bundle_type']);
    $this->drupalLogin($user);
    $this->drupalGet('admin/commerce/config/product-bundle-types/add');

    $bundle_item_type_field = $this->getSession()
      ->getPage()
      ->findField('bundleItemType');
    $this->assertFalse($bundle_item_type_field->hasAttribute('disabled'));
    $order_item_type_field = $this->getSession()
      ->getPage()
      ->findField('orderItemType');
    $this->assertTrue($order_item_type_field->hasAttribute('disabled'));

    $edit = [
      'id' => 'foo',
      'label' => 'Foo',
      'description' => 'My even more random product bundle type',
      'bundleItemType' => 'default',
      'orderItemType' => 'commerce_product_bundle_default',
    ];
    $this->submitForm($edit, t('Save'));
    $this->assertSession()
      ->pageTextContains('The product bundle type Foo has been successfully saved.');

    $product_bundle_type = ProductBundleType::load($edit['id']);
    $this->assertNotEmpty($product_bundle_type);
    $this->assertEquals($edit['label'], $product_bundle_type->label());
    $this->assertEquals($edit['description'], $product_bundle_type->getDescription());
    $this->assertEquals($edit['bundleItemType'], $product_bundle_type->getBundleItemTypeId());
    $this->assertEquals($edit['orderItemType'], $product_bundle_type->getOrderItemTypeId());
    $form_display = commerce_get_entity_display('commerce_product', $edit['id'], 'form');
    $this->assertEmpty($form_display->getComponent('variations'));

    // Automatic variation type creation option, single variation mode.
    $this->drupalGet('admin/commerce/config/product-bundle-types/add');
    $edit = [
      'id' => 'foo2',
      'label' => 'Foo2',
      'description' => 'My even more random product type',
      'bundleItemType' => '',
      'orderItemType' => 'commerce_product_bundle_default',
    ];
    $this->submitForm($edit, t('Save'));
    $product_bundle_type = ProductBundleType::load($edit['id']);
    $this->assertNotEmpty($product_bundle_type);
    $this->assertEquals($edit['label'], $product_bundle_type->label());
    $this->assertEquals($edit['description'], $product_bundle_type->getDescription());
    $this->assertEquals($edit['id'], $product_bundle_type->getBundleItemTypeId());
    $bundle_item_type = ProductBundleItemType::load($edit['id']);
    $this->assertNotEmpty($bundle_item_type);
    $this->assertEquals($bundle_item_type->label(), $edit['label']);

    // Confirm that a conflicting product bundle item type ID is detected.
    $product_bundle_type_id = $product_bundle_type->id();
    $product_bundle_type->delete();
    $this->drupalGet('admin/commerce/config/product-bundle-types/add');
    $edit = [
      'id' => $product_bundle_type_id,
      'label' => $this->randomMachineName(),
      'description' => 'My even more random product bundle type',
      'bundleItemType' => '',
    ];
    $this->submitForm($edit, t('Save'));
    $this->assertSession()
      ->pageTextContains(t('A product bundle item type with the machine name @name already exists. Select an existing product bundle item type or change the machine name for this product bundle type.', ['@name' => $product_bundle_type_id]));

    // Confirm that the form can't be submitted with no order item types.
    $default_order_item_type = OrderItemType::load('commerce_product_bundle_default');
    $this->assertNotEmpty($default_order_item_type);
    $default_order_item_type->delete();

    $this->drupalGet('admin/commerce/config/product-bundle-types/add');
    $edit = [
      'id' => 'foo3',
      'label' => 'Foo3',
      'description' => 'Another random product type',
      'bundleItemType' => '',
    ];
    $this->submitForm($edit, t('Save'));
    $this->assertSession()->pageTextContains(t('A new product bundle type cannot be created, because no order item types were found. Select an existing product bundle type or retry after creating a new order item type.'));

    // Confirm that a non-default order item type can be selected.
    $default_order_item_type->delete();
    OrderItemType::create([
      'id' => 'test',
      'label' => 'Test',
      'orderType' => 'default',
      'purchasableEntityType' => 'commerce_product_bundle',
    ])->save();

    $this->drupalGet('admin/commerce/config/product-bundle-types/add');
    $edit = [
      'id' => 'foo4',
      'label' => 'Foo4',
      'description' => 'My even more random product bundle type',
      'bundleItemType' => '',
    ];
    $this->submitForm($edit, t('Save'));
    $product_bundle_type = ProductBundleType::load($edit['id']);
    $this->assertNotEmpty($product_bundle_type);
    $this->assertEquals('test', $product_bundle_type->getOrderItemTypeId());
    $this->assertEquals($edit['label'], $product_bundle_type->label());
    $this->assertEquals($edit['description'], $product_bundle_type->getDescription());
    $this->assertEquals($edit['id'], $product_bundle_type->getBundleItemTypeId());
    $bundle_item_type = ProductBundleItemType::load($edit['id']);
    $this->assertNotEmpty($bundle_item_type);
    $this->assertEquals($edit['label'], $bundle_item_type->label());

  }

  /**
   * Tests editing a product bundle type.
   */
  public function testEdit() {
    $this->drupalGet('admin/commerce/config/product-bundle-types/default/edit');

    $bundle_item_type_field = $this->getSession()
      ->getPage()
      ->findField('bundleItemType');
    $this->assertFalse($bundle_item_type_field->hasAttribute('disabled'));
    $edit = [
      'label' => 'Default!',
      'description' => 'New description.',
    ];
    $this->submitForm($edit, t('Save'));
    $this->assertSession()
      ->pageTextContains('The product bundle type Default! has been successfully saved.');

    $product_bundle_type = ProductBundleType::load('default');
    $this->assertEquals($edit['label'], $product_bundle_type->label());
    $this->assertEquals($edit['description'], $product_bundle_type->getDescription());

    // Cannot change the bundle item type once a product bundle has been created.
    $product_bundle = $this->createEntity('commerce_product_bundle', [
      'type' => 'default',
      'title' => 'Test product bundle',
    ]);
    $this->drupalGet('admin/commerce/config/product-bundle-types/default/edit');
    $bundle_item_type_field = $this->getSession()
      ->getPage()
      ->findField('bundleItemType');
    $this->assertTrue($bundle_item_type_field->hasAttribute('disabled'));
  }

  /**
   * Tests duplicating a product bundle type.
   */
  public function testDuplicate() {
    $this->drupalGet('admin/commerce/config/product-bundle-types/default/duplicate');
    $this->assertSession()->fieldValueEquals('label', 'Default');
    $edit = [
      'label' => 'Default2',
      'id' => 'default2',
    ];
    $this->submitForm($edit, t('Save'));
    $this->assertSession()
      ->pageTextContains('The product bundle type Default2 has been successfully saved.');

    // Confirm that the original product type is unchanged.
    $product_bundle_type = ProductBundleType::load('default');
    $this->assertNotEmpty($product_bundle_type);
    $this->assertEquals('Default', $product_bundle_type->label());

    // Confirm that the new product type has the expected data.
    $product_bundle_type = ProductBundleType::load('default2');
    $this->assertNotEmpty($product_bundle_type);
    $this->assertEquals('Default2', $product_bundle_type->label());
  }

  /**
   * Tests deleting a product bundle type.
   */
  public function testDelete() {
    $bundle_item_type = $this->createEntity('commerce_product_bundle_i_type', [
      'id' => 'foo',
      'label' => 'foo',
    ]);
    /** @var \Drupal\commerce_product_bundle\Entity\ProductBundleTypeInterface $product_bundle_type */
    $product_bundle_type = $this->createEntity('commerce_product_bundle_type', [
      'id' => 'foo',
      'label' => 'foo',
      'bundleItemType' => $bundle_item_type->id(),
    ]);
    $product_bundle = $this->createEntity('commerce_product_bundle', [
      'type' => $product_bundle_type->id(),
      'title' => $this->randomMachineName(),
    ]);

    // Confirm that the type can't be deleted while there's a product bundle.
    $this->drupalGet($product_bundle_type->toUrl('delete-form'));
    $this->assertSession()
      ->pageTextContains(t('@type is used by 1 product bundle on your site. You cannot remove this product bundle type until you have removed all of the @type product bundles.', ['@type' => $product_bundle_type->label()]));
    $this->assertSession()
      ->pageTextNotContains(t('This action cannot be undone.'));

    // Delete the product bundle, confirm that deletion works.
    $product_bundle->delete();
    $product_bundle_type->save();
    $this->drupalGet($product_bundle_type->toUrl('delete-form'));
    $this->assertSession()
      ->pageTextContains(t('Are you sure you want to delete the product bundle type @type?', ['@type' => $product_bundle_type->label()]));
    $this->assertSession()
      ->pageTextContains(t('This action cannot be undone.'));
    $this->submitForm([], 'Delete');
    $exists = (bool) ProductBundleType::load($product_bundle_type->id());
    $this->assertEmpty($exists);
  }

}

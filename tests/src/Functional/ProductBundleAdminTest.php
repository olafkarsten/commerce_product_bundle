<?php

namespace Drupal\Tests\commerce_product_bundle\Functional;

use Drupal\commerce\EntityHelper;
use Drupal\commerce_product_bundle\Entity\ProductBundle;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Create, view, edit, delete, and change product bundles.
 *
 * @group commerce_product_bundle
 */
class ProductBundleAdminTest extends ProductBundleBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  /**
   * Tests creating a product bundle.
   */
  public function testCreateProductBundle() {
    $this->drupalGet('admin/commerce/product-bundles');
    $this->getSession()->getPage()->clickLink('Add product bundle');
    $html = $this->getSession()->getPage()->getHtml();

    $store_ids = EntityHelper::extractIds($this->stores);
    $title = $this->randomMachineName();
    $edit = [
      'title[0][value]' => $title,
    ];
    foreach ($store_ids as $store_id) {
      $edit['stores[target_id][value][' . $store_id . ']'] = $store_id;
    }
    $this->submitForm($edit, 'Save');

    $result = \Drupal::entityQuery('commerce_product_bundle')
      ->condition("title", $edit['title[0][value]'])
      ->range(0, 1)
      ->execute();
    $product_bundle_id = reset($result);
    $product_bundle = ProductBundle::load($product_bundle_id);

    $this->assertNotNull($product_bundle, 'The new product bundle has been created.');
    $this->assertSession()
      ->pageTextContains(t('The product bundle @title has been successfully saved', ['@title' => $title]));
    $this->assertSession()->pageTextContains($title);
    $stores = $product_bundle->getStores();
    $storeIds = $product_bundle->getStoreIds();
    $this->assertFieldValues($product_bundle->getStoreIds(), $store_ids, 'Created product bundle has the correct associated store ids.');
    $this->assertFieldValues($product_bundle->getStores(), $this->stores, 'Created product bundle has the correct associated stores.');

    $this->drupalGet($product_bundle->toUrl('canonical'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($product_bundle->getTitle());
  }

  /**
   * Tests editing a product bundle.
   */
  public function testEditProductBundle() {
    $product_bundle = $this->createEntity('commerce_product_bundle', [
      'type' => 'default',
    ]);

    // Check the integrity of the edit form.
    $this->drupalGet($product_bundle->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists('title[0][value]');

    $title = $this->randomMachineName();
    $store_ids = EntityHelper::extractIds($this->stores);
    $edit = [
      'title[0][value]' => $title,
    ];
    foreach ($store_ids as $store_id) {
      $edit['stores[target_id][value][' . $store_id . ']'] = $store_id;
    }
    $this->submitForm($edit, 'Save');

    $this->container->get('entity_type.manager')
      ->getStorage('commerce_product_bundle')
      ->resetCache([$product_bundle->id()]);
    $product_bundle = ProductBundle::load($product_bundle->id());
    $this->assertEquals($product_bundle->getTitle(), $title, 'The product_bundle title successfully updated.');
    $this->assertFieldValues($product_bundle->getStores(), $this->stores, 'Updated product_bundle has the correct associated stores.');
    $this->assertFieldValues($product_bundle->getStoreIds(), $store_ids, 'Updated product_bundle has the correct associated store ids.');
  }

  /**
   * Tests deleting a product bundle.
   */
  public function testDeleteProductBundle() {
    $product_bundle = $this->createEntity('commerce_product_bundle', [
      'title' => $this->randomMachineName(),
      'type' => 'default',
    ]);
    $this->drupalGet($product_bundle->toUrl('delete-form'));
    $this->assertSession()
      ->pageTextContains(t("Are you sure you want to delete the product bundle @product_bundle?", ['@product_bundle' => $product_bundle->getTitle()]));
    $this->assertSession()
      ->pageTextContains(t('This action cannot be undone.'));
    $this->submitForm([], 'Delete');

    $this->container->get('entity_type.manager')
      ->getStorage('commerce_product_bundle')
      ->resetCache();
    $product_bundle_exists = (bool) ProductBundle::load($product_bundle->id());
    $this->assertEmpty($product_bundle_exists, 'The new product bundle has been deleted from the database.');
  }

  /**
   * Tests viewing the admin/commerce/product-bundles page.
   */
  public function testAdminProductBundles() {

    $this->drupalGet('admin/commerce/product-bundles');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->pageTextNotContains('You are not authorized to access this page.');
    $this->assertNotEmpty($this->getSession()
      ->getPage()
      ->hasLink('Add product bundle'));

    // Create a default type product bundle.
    $product_bundle = $this->createEntity('commerce_product_bundle', [
      'type' => 'default',
      'title' => 'First product bundle',
      'status' => TRUE,
    ]);
    // Create a second product bundle type and product bundles for that type.
    $values = [
      'id' => 'random',
      'label' => 'Random',
      'description' => 'My random product bundle type',
      'bundleItemType' => 'default',
    ];
    $product_bundle_type = $this->createEntity('commerce_product_bundle_type', $values);

    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $second_product_bundle */
    $second_product_bundle = $this->createEntity('commerce_product_bundle', [
      'type' => 'random',
      'title' => 'Second product bundle',
      'status' => FALSE,
    ]);
    /** @var \Drupal\commerce_product\Entity\ProductInterface $third_product */
    $third_product_bundle = $this->createEntity('commerce_product_bundle', [
      'type' => 'random',
      'title' => 'Third product bundle',
      'status' => TRUE,
    ]);

    $this->drupalGet($product_bundle->toUrl('collection'));
    $this->assertSession()
      ->pageTextNotContains('You are not authorized to access this page.');
    $row_count = $this->getSession()
      ->getPage()
      ->findAll('xpath', '//table/tbody/tr');
    $this->assertEquals(3, count($row_count));

    // Confirm that product titles are displayed.
    $page = $this->getSession()->getPage();
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td/a[text()="First product bundle"]');
    $this->assertEquals(1, count($product_bundle_count), 'First product bundle is displayed.');
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td/a[text()="Second product bundle"]');
    $this->assertEquals(1, count($product_bundle_count), 'Second product bundle is displayed.');
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td/a[text()="Third product bundle"]');
    $this->assertEquals(1, count($product_bundle_count), 'Third product bundle is displayed.');

    // Confirm that product types are displayed.
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td[starts-with(text(), "Default")]');
    $this->assertEquals(1, count($product_bundle_count), 'Default product bundle type exists in the table.');
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td[starts-with(text(), "Random")]');
    $this->assertEquals(2, count($product_bundle_count), 'Random product bundle types exist in the table.');

    // Confirm that product statuses are displayed.
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td[starts-with(text(), "Unpublished")]');
    $this->assertEquals(1, count($product_bundle_count), 'Unpublished product bundle exists in the table.');
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td[starts-with(text(), "Published")]');
    $this->assertEquals(2, count($product_bundle_count), 'Published product bundles exist in the table.');

    // Logout and check that anonymous users cannot see the product bundles page
    // and receive a 403 error code.
    $this->drupalLogout();
    $this->drupalGet($product_bundle->toUrl('collection'));
    $this->assertSession()->statusCodeEquals(403);
    $this->assertSession()
      ->pageTextContains('You are not authorized to access this page.');
    $this->assertEmpty($this->getSession()
      ->getPage()
      ->hasLink('Add product bundle'));

    // Login and confirm access for 'access commerce_product-bundle overview'
    // permission. The second product bundle should no longer be visible because
    // it is unpublished.
    $user = $this->drupalCreateUser(['access commerce_product_bundle overview']);
    $this->drupalLogin($user);
    $this->drupalGet($product_bundle->toUrl('collection'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->pageTextNotContains('You are not authorized to access this page.');
    $this->assertEmpty($this->getSession()
      ->getPage()
      ->hasLink('Add product bundle'));

    $row_count = $this->getSession()
      ->getPage()
      ->findAll('xpath', '//table/tbody/tr');
    $this->assertEquals(2, count($row_count));

    // Confirm that product bundle titles are displayed.
    $page = $this->getSession()->getPage();
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td/a[text()="First product bundle"]');
    $this->assertEquals(1, count($product_bundle_count), 'First product bundle is displayed.');
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td/a[text()="Third product bundle"]');
    $this->assertEquals(1, count($product_bundle_count), 'Third product bundle is displayed.');
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td/a[text()="Second product bundle"]');
    $this->assertEquals(0, count($product_bundle_count), 'Second product bundle is not displayed.');

    // Confirm that the right product statuses are displayed.
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td[starts-with(text(), "Unpublished")]');
    $this->assertEquals(0, count($product_bundle_count), 'Unpublished product bundle do not exist in the table.');
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td[starts-with(text(), "Published")]');
    $this->assertEquals(2, count($product_bundle_count), 'Published product bundles exist in the table.');

    // Confirm that product bundle types are displayed.
    $this->assertSession()->optionExists('edit-type', 'default');
    $this->assertSession()->optionExists('edit-type', 'random');
    // Confirm that product types are displayed.
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td[starts-with(text(), "Default")]');
    $this->assertEquals(1, count($product_bundle_count), 'Default product bundle type exists in the table.');
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td[starts-with(text(), "Random")]');
    $this->assertEquals(1, count($product_bundle_count), 'Random product bundle types exist in the table.');

    // Confirm that the product bundle type filter respects view access.
    $authenticated_role = Role::load(RoleInterface::AUTHENTICATED_ID);
    $authenticated_role->revokePermission('view commerce_product_bundle');
    $authenticated_role->save();
    $this->drupalGet($product_bundle->toUrl('collection'));
    $this->assertSession()->pageTextContains('No product bundles available');
    $this->assertSession()->optionNotExists('edit-type', 'default');
    $this->assertSession()->optionNotExists('edit-type', 'random');

    $authenticated_role->grantPermission('view default commerce_product_bundle');
    $authenticated_role->save();
    $this->drupalGet($product_bundle->toUrl('collection'));
    $this->assertSession()->optionExists('edit-type', 'default');
    $this->assertSession()->optionNotExists('edit-type', 'random');

    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td[starts-with(text(), "Default")]');
    $this->assertEquals(1, count($product_bundle_count));
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td[starts-with(text(), "Random")]');
    $this->assertEquals(0, count($product_bundle_count));

    // Login and confirm access for "view own unpublished commerce_product_bundle".
    $user = $this->drupalCreateUser([
      'access commerce_product_bundle overview',
      'view own unpublished commerce_product_bundle',
    ]);
    $second_product_bundle->setOwnerId($user->id());
    $second_product_bundle->save();
    $this->drupalLogin($user);
    $this->drupalGet($product_bundle->toUrl('collection'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()
      ->pageTextNotContains('You are not authorized to access this page.');
    $product_bundle_count = $page->findAll('xpath', '//table/tbody/tr/td/a[text()="Second product bundle"]');
    $this->assertEquals(1, count($product_bundle_count), 'Second product bundle is displayed.');
  }

}

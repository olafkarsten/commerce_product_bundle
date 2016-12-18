<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides #lazy_builder callbacks.
 */
class ProductBundleLazyBuilders {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * Constructs a new ProductBundleLazyBuilders object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder
   *   The entity form builder.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFormBuilderInterface $entity_form_builder) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFormBuilder = $entity_form_builder;
  }

  /**
   * Builds the add to cart form.
   *
   * @param string $product_bundle_id
   *   The product bundle ID.
   * @param string $view_mode
   *   The view mode used to render the product bundle.
   *
   * @return array
   *   A renderable array containing the add to cart form.
   */
  public function addToCartForm($product_bundle_id, $view_mode) {
    /** @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage */
    $order_item_storage = $this->entityTypeManager->getStorage('commerce_order_item');

    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $product_bundle */
    $product_bundle = $this->entityTypeManager->getStorage('commerce_product_bundle')->load($product_bundle_id);
    $order_item = $order_item_storage->createFromPurchasableEntity($product_bundle);
    $form_state_additions = [
      'product_bundle' => $product_bundle,
      'view_mode' => $view_mode,
      // This is where we could pass settings into the form.
      // 'settings' => [
      //   'combine' => $combine,
      // ],.
    ];
    return $this->entityFormBuilder->getForm($order_item, 'add_to_cart', $form_state_additions);
  }

}

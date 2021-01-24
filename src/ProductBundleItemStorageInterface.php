<?php

namespace Drupal\commerce_product_bundle;

use Drupal\commerce_product_bundle\Entity\BundleInterface;
use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for product bundle item entities.
 *
 * This extends the base storage class, adding required special handling for
 * Product bundle item entities.
 *
 * @ingroup commerce_product_bundle
 */
interface ProductBundleItemStorageInterface extends ContentEntityStorageInterface {

  /**
   * Loads the product bundle item from context.
   *
   * Uses the product bundle item specified in the URL (?v=) if it's active and
   * belongs to the current product bundle.
   *
   * Note: The returned product bundle item is not guaranteed to be enabled,
   * the caller needs to check it against the list from loadEnabled().
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleInterface $product_bundle
   *   The current product bundle.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The product bundle item or NULL.
   */
  public function loadFromContext(BundleInterface $product_bundle);

}

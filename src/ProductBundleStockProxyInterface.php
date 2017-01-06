<?php

/**
 * @file
 * Availability or stock proxy interface for product bundles.
 */

namespace \Drupal\commerce_product_bundle;

interface ProductBundleStockProxyInterface {

  /**
   * Gets the availability of a given product bundle.
   *
   * @param \Drupal\commerce_product_bundle\ProductBundleInterface $bundle
   *   The product bundle.
   *
   * @return \Drupal\commerce\AvailabilityResponseInterface $response
   *   The product bundle availbility response.
   */
  public function getAvailability(ProductBundleInterface $bundle);

  /**
   * Proxies a stock transaction to all bundle items.
   *
   * @param \Drupal\commerce_product_bundle\ProductBundleInterface $bundle
   *   The product bundle.
   * @param \Drupal\commerce_stock\StockTransactionInterface $transaction
   *   The stock transaction.
   */
  public function proxyTransaction(ProductBundleInterface $bundle, StockTransaction $transaction);

}


<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\commerce_product_bundle\Entity\BundleInterface;

/**
 * Defines the storage handler class for product bundle entities.
 *
 * This extends the base storage class, adding required special handling for
 * Product bundle entities.
 *
 * @ingroup commerce_product_bundle
 */
interface ProductBundleStorageInterface extends ContentEntityStorageInterface {

}

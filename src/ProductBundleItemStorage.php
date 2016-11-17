<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\commerce_product_bundle\Entity\BundleItemInterface;

/**
 * Defines the storage handler class for product bundle item entities.
 *
 * This extends the base storage class, adding required special handling for
 * product bundle item entities.
 *
 * @ingroup commerce_product_bundle
 */
class ProductBundleItemStorage extends SqlContentEntityStorage implements ProductBundleItemStorageInterface {

}

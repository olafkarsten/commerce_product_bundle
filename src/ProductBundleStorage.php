<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for product bundle entities.
 *
 * This extends the base storage class, adding required special handling for
 * product bundle entities.
 *
 * @ingroup commerce_product_bundle
 */
class ProductBundleStorage extends SqlContentEntityStorage implements ProductBundleStorageInterface {

}

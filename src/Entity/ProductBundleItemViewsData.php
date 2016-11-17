<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for product bundle item entities.
 */
class ProductBundleItemViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['commerce_product_bundle_i']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Product bundle item'),
      'help' => $this->t('The product bundle item ID.'),
    );

    return $data;
  }

}

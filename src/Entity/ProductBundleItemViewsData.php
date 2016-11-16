<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Product bundle item entities.
 */
class ProductBundleItemViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['commerce_product_bundle_item']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Product bundle item'),
      'help' => $this->t('The Product bundle item ID.'),
    );

    return $data;
  }

}

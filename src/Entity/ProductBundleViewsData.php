<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Product bundle entities.
 */
class ProductBundleViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['commerce_product_bundle']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Product bundle'),
      'help' => $this->t('The Product bundle ID.'),
    );

    return $data;
  }

}

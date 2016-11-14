<?php

namespace Drupal\commerce_static_bundle\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Static bundle item entities.
 */
class StaticBundleItemViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['commerce_static_bundle_item']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Static bundle item'),
      'help' => $this->t('The Static bundle item ID.'),
    );

    return $data;
  }

}

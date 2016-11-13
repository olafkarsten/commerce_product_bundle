<?php

namespace Drupal\commerce_static_bundle\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Static bundle entities.
 */
class StaticBundleViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['static_bundle']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Static bundle'),
      'help' => $this->t('The Static bundle ID.'),
    );

    return $data;
  }

}

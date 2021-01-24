<?php

namespace Drupal\commerce_product_bundle\Form;

use Drupal\commerce\Form\CommerceBundleEntityDeleteFormBase;
use Drupal\Core\Url;

/**
 * Builds the form to delete product bundle type entities.
 */
class ProductBundleTypeDeleteForm extends CommerceBundleEntityDeleteFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the product bundle type %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.commerce_product_bundle_type.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

}

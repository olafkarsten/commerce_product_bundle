<?php

namespace Drupal\commerce_product_bundle\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ProductBundleTypeForm.
 *
 * @package Drupal\commerce_product_bundle\Form
 */
class ProductBundleTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $product_bundle_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $product_bundle_type->label(),
      '#description' => $this->t("Label for the Product bundle type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $product_bundle_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_product_bundle\Entity\ProductBundleType::load',
      ],
      '#disabled' => !$product_bundle_type->isNew(),
    ];

    /* @ToDo You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $product_bundle_type = $this->entity;
    $status = $product_bundle_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Product bundle type.', [
          '%label' => $product_bundle_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Product bundle type.', [
          '%label' => $product_bundle_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($product_bundle_type->urlInfo('collection'));
  }

}

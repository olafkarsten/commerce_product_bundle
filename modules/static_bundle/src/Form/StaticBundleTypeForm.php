<?php

namespace Drupal\commerce_static_bundle\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class StaticBundleTypeForm.
 *
 * @package Drupal\commerce_static_bundle\Form
 */
class StaticBundleTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $static_bundle_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $static_bundle_type->label(),
      '#description' => $this->t("Label for the Static bundle type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $static_bundle_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_static_bundle\Entity\StaticBundleType::load',
      ],
      '#disabled' => !$static_bundle_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $static_bundle_type = $this->entity;
    $status = $static_bundle_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Static bundle type.', [
          '%label' => $static_bundle_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Static bundle type.', [
          '%label' => $static_bundle_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($static_bundle_type->urlInfo('collection'));
  }

}

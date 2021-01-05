<?php

namespace Drupal\commerce_product_bundle\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity\Form\EntityDuplicateFormTrait;
use Drupal\language\Entity\ContentLanguageSettings;

/**
 * Provides the product bundle item type form.
 *
 * @package Drupal\commerce_product_bundle\Form
 */
class ProductBundleItemTypeForm extends BundleEntityFormBase {

  use EntityDuplicateFormTrait;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $product_bundle_item_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $product_bundle_item_type->label(),
      '#description' => $this->t("Label for the product bundle item type."),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $product_bundle_item_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_product_bundle\Entity\ProductBundleItemType::load',
      ],
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => !$product_bundle_item_type->isNew(),
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t('This text will be displayed on the <em>Add product bundle item</em> page.'),
      '#default_value' => $product_bundle_item_type->getDescription(),
    ];

    if ($this->moduleHandler->moduleExists('language')) {
      $form['language'] = [
        '#type' => 'details',
        '#title' => $this->t('Language settings'),
        '#group' => 'additional_settings',
      ];
      $form['language']['language_configuration'] = [
        '#type' => 'language_configuration',
        '#entity_information' => [
          'entity_type' => 'commerce_product_bundle_i',
          'bundle' => $product_bundle_item_type->id(),
        ],
        '#default_value' => ContentLanguageSettings::loadByEntityTypeBundle('commerce_product_bundle_i', $product_bundle_item_type->id()),
      ];
      $form['#submit'][] = 'language_configuration_element_submit';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $product_bundle_item_type = $this->entity;
    $status = $product_bundle_item_type->save();
    $this->postSave($product_bundle_item_type, $this->operation);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('Created the %label product bundle item type.', [
          '%label' => $product_bundle_item_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addStatus($this->t('Saved the %label product bundle item type.', [
          '%label' => $product_bundle_item_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($product_bundle_item_type->toUrl('collection'));
  }

}

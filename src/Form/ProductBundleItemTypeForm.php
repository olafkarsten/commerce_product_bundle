<?php

namespace Drupal\commerce_product_bundle\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ProductBundleItemTypeForm.
 *
 * @package Drupal\commerce_product_bundle\Form
 */
class ProductBundleItemTypeForm extends EntityForm {

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
      '#disabled' => !$product_bundle_item_type->isNew(),
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t('This text will be displayed on the <em>Add product bundle item</em> page.'),
      '#default_value' => $product_bundle_item_type->getDescription(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $product_bundle_item_type = $this->entity;
    $status = $product_bundle_item_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label product bundle item type.', [
          '%label' => $product_bundle_item_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label product bundle item type.', [
          '%label' => $product_bundle_item_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($product_bundle_item_type->urlInfo('collection'));
    if ($status == SAVED_NEW) {
      commerce_product_bundle_add_variations_field($product_bundle_item_type);
    }
  }

}

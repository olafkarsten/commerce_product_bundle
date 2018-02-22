<?php

namespace Drupal\commerce_product_bundle\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of 'commerce_product_bundle_item_selection_default'.
 *
 * @FieldWidget(
 *   id = "commerce_product_bundle_item_selection_default",
 *   label = @Translation("Bundle item selection"),
 *   field_types = {
 *     "commerce_product_bundle_item_selection"
 *   }
 * )
 */
class BundleItemSelectionDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $variationStorage = \Drupal::service('entity_type.manager')->getStorage('commerce_product_variation');
    $bundleItemStorage = \Drupal::service('entity_type.manager')->getStorage('commerce_product_bundle_i');

    /** @var int $bundle_item */
    $bundle_item = $items[$delta]->bundle_item;
    /** @var int $selected_qty */
    $selected_qty = $items[$delta]->qty;
    /** @var int $selected_entity */
    $selected_entity = $items[$delta]->purchasable_entity;

    $element['bundle_item'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Bundle item'),
      '#weight' => 0,
      '#target_type' => 'commerce_product_bundle_i',
      '#default_value' => $bundle_item ? $bundleItemStorage->load($bundle_item) : NULL,
    ];
    $element['purchasable_entity'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Selected entity'),
      '#weight' => 2,
      '#target_type' => 'commerce_product_variation',
      '#default_value' => $selected_entity ? $variationStorage->load($selected_entity) : NULL,
    ];
    $element['qty'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Selected quantity'),
      '#weight' => 1,
      '#default_value' => $selected_qty,
    ];

    return $element;
  }

}

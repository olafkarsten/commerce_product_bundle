<?php

namespace Drupal\commerce_product_bundle\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'commerce_product_bundle_add_to_cart' formatter.
 *
 * @FieldFormatter(
 *   id = "commerce_product_bundle_add_to_cart",
 *   label = @Translation("Add to cart form (CPB)"),
 *   field_types = {
 *     "entity_reference",
 *   },
 * )
 */
class AddToCartFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'combine' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['combine'] = [
      '#type' => 'checkbox',
      '#title' => t('Combine order items containing the same product bundle items.'),
      '#description' => t('The order item type, bundle item selections, and data from fields exposed on the Add to Cart form must all match to combine.'),
      '#default_value' => $this->getSetting('combine'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if ($this->getSetting('combine')) {
      $summary[] = $this->t('Combine order items containing the same product bundle items.');
    }
    else {
      $summary[] = $this->t('Do not combine order items containing the same product bundle items.');
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $product_bundle = $items->getEntity();
    if (!empty($product_bundle->in_preview)) {
      $elements[0]['add_to_cart_form'] = [
        '#type' => 'actions',
        ['#type' => 'button', '#value' => $this->t('Add to cart')],
      ];
      return $elements;
    }
    if ($product_bundle->isNew()) {
      return [];
    }

    $view_mode = $this->viewMode;
    // If the field formatter is rendered in Layout Builder, the `viewMode`
    // property will be `_custom` and the original view mode is stored in the
    // third party settings.
    // @see \Drupal\layout_builder\Plugin\Block\FieldBlock::build
    if (isset($this->thirdPartySettings['layout_builder'])) {
      $view_mode = $this->thirdPartySettings['layout_builder']['view_mode'];
    }

    $elements[0]['add_to_cart_form'] = [
      '#lazy_builder' => [
        'commerce_product_bundle.lazy_builders:addToCartForm', [
          $product_bundle->id(),
          $view_mode,
          $this->getSetting('combine'),
          $langcode,
        ],
      ],
      '#create_placeholder' => TRUE,
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $has_cart = \Drupal::moduleHandler()->moduleExists('commerce_cart');
    $entity_type = $field_definition->getTargetEntityTypeId();
    $field_name = $field_definition->getName();
    return $has_cart && $entity_type == 'commerce_product_bundle' && $field_name == 'bundle_items';
  }

}

<?php

namespace Drupal\commerce_product_bundle\Form;

use Drupal\commerce_product\Entity\Product;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\inline_entity_form\Form\EntityInlineForm;

/**
 * Defines the inline form for product bundle items.
 */
class ProductBundleItemInlineForm extends EntityInlineForm {

  /**
   * {@inheritdoc}
   */
  public function getTableFields($bundles) {
    $fields = parent::getTableFields($bundles);
    $fields['product'] = [
      'type' => 'field',
      'label' => t('Product'),
      'weight' => 2,
    ];
    $fields['unit_price'] = [
      'type' => 'field',
      'label' => t('Unit Price'),
      'weight' => 4,
    ];
    $fields['min_quantity'] = [
      'type' => 'field',
      'label' => t('Min Quantity'),
      'weight' => 5,
    ];
    $fields['max_quantity'] = [
      'type' => 'field',
      'label' => t('Max Quantity'),
      'weight' => 6,
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function entityForm(array $entity_form, FormStateInterface $form_state) {
    $entity_form = parent::entityForm($entity_form, $form_state);
    $entity_form['product']['widget'][0]['target_id']['#ajax'] = [
      'callback' => [get_class($this), 'variationsRefresh'],
      'event' => 'autocompleteclose',
      'wrapper' => 'product_variations_refresh',
    ];
    $entity_form['variations']['#attributes'] = ['id' => 'product_variations_refresh'];

    // Init form's variations state.
    $productDefaultId = isset($entity_form['#default_value']) ? $entity_form['#default_value']->get('product')->getValue()[0]['target_id'] : NULL;
    $triggering_element = $form_state->getTriggeringElement();
    $entity_form['variations']['widget']['#disabled'] = FALSE;
    if (isset($triggering_element) && $triggering_element['#ief_form'] == "add") {
      $entity_form['variations']['#states'] = [
        'visible' => [
          ':input[name="bundle_items[form][inline_entity_form][product][0][target_id]"]' => ['filled' => TRUE],
        ],
      ];
      $entity_form['variations']['widget']['#disabled'] = TRUE;
    }
    if (isset($productDefaultId) && $triggering_element) {
      $entity_form['variations']['widget']['#options'] = $this->variationsOptions($productDefaultId);
    }

    return $entity_form;
  }

  /**
   * Get variations select options which belong to a product.
   *
   * @param int $productId
   *   Product entity id.
   *
   * @return array
   *   Selection array.
   */
  public static function variationsOptions($productId) {
    $values['_none'] = '- ' . \Drupal::translation()->translate('All') . ' -';
    $product = Product::load($productId);
    $variations = $product->getVariations();
    /** @var \Drupal\commerce_product\Entity\ProductVariation $variation */
    foreach ($variations as $variation) {
      $values[$variation->id()] = $variation->id() . ': ' . $variation->getTitle();
    }
    return $values;
  }

  /**
   * Product field ajax callback.
   *
   * @param array $form
   *   Inline entity form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Inline entity form_state.
   *
   * @return mixed
   *   Form element
   */
  public static function variationsRefresh(array $form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();

    // Several steps to get the right inline_entity_form.
    $array_parents = array_slice($triggering_element['#array_parents'], 0, -4);
    $inlineEntityFormId = $array_parents[3];
    $element = NestedArray::getValue($form, $array_parents);

    // Get the origin variations form.
    $variationsForm = $element['variations'];
    $bundleItems = $form_state->getValue('bundle_items');
    if ($element['#op'] == 'add') {
      $productId = $bundleItems['form']['inline_entity_form']['product'][0]['target_id'];
    }
    if ($element['#op'] == 'edit') {
      $productId = $bundleItems['form']['inline_entity_form']['entities'][$inlineEntityFormId]['form']['product'][0]['target_id'];
    }
    if ($productId) {
      $variationsForm['widget']['#options'] = ProductBundleItemInlineForm::variationsOptions($productId);
    }
    return $variationsForm;

  }

  /**
   * {@inheritdoc}
   */
  public function entityFormValidate(array &$entity_form, FormStateInterface $form_state) {
    parent::entityFormValidate($entity_form, $form_state);

    // Validate all variations belong to one product.
    $triggering_element = $form_state->getTriggeringElement();
    if (!empty($triggering_element['#ief_submit_trigger'])) {
      $ief_row_delta = $triggering_element['#ief_row_delta'];
      $bundle_items = $form_state->getValue('bundle_items');
      if (isset($ief_row_delta)) {
        $productId = $bundle_items['form']['inline_entity_form']['entities'][$ief_row_delta]['form']['product'][0]['target_id'];
        $formVariationsIds = $bundle_items['form']['inline_entity_form']['entities'][$ief_row_delta]['form']['variations'];
      }
      else {
        $productId = $bundle_items['form']['inline_entity_form']['product'][0]['target_id'];
        $formVariationsIds = $bundle_items['form']['inline_entity_form']['variations'];
      }
      $product = Product::load($productId);
      $variationsIds = $product->getVariationIds();
      foreach ($formVariationsIds as $value) {
        if (!in_array($value['target_id'], $variationsIds)) {
          $message = t('All variations must belong to the same product');
          $form_state->setError($triggering_element, $message);
        }
      }
    }

  }

}

<?php

namespace Drupal\commerce_product_bundle\Form;

use Drupal\commerce_product\Entity\Product;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\Element\EntityAutocomplete;
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

    // This if/else make sure that the form can find the right position of
    // widget element.
    if (isset($entity_form['product']['widget'][0])) {
      $entity_form['product']['widget'][0]['target_id']['#ajax'] = [
        'callback' => [get_class($this), 'variationsRefresh'],
        'event' => 'autocompleteclose',
        'wrapper' => $entity_form['#ief_row_delta'] . 'product_variations_refresh',
      ];
    }
    else {
      $entity_form['product']['widget']['#ajax'] = [
        'callback' => [get_class($this), 'variationsRefresh'],
        'wrapper' => $entity_form['#ief_row_delta'] . 'product_variations_refresh',
      ];
    }
    $entity_form['variations']['#attributes']['id'] = $entity_form['#ief_row_delta'] . 'product_variations_refresh';
    $entity_form['variations']['widget']['#disabled'] = FALSE;

    /** @var \Drupal\commerce_product_bundle\Entity\ProductBundleItem $productBundleItem */
    $productBundleItem = $entity_form['#entity'];
    if ($productBundleItem->hasProduct()) {
      $product = $productBundleItem->getProduct();
      if (!empty($product)) {
        $productId = $productBundleItem->getProductId();
      }
    }

    $userInput = $form_state->getUserInput();

    if ($entity_form['#op'] == 'add') {
      if (isset($userInput['bundle_items']['form']['inline_entity_form']['product'])) {
        $productId = $userInput['bundle_items']['form']['inline_entity_form']['product'];
        if (isset($productId[0]['target_id'])) {
          $productId = EntityAutocomplete::extractEntityIdFromAutocompleteInput($productId[0]['target_id']);
        }
      }
    }
    elseif ($entity_form['#op'] == 'edit') {
      if (isset($userInput['bundle_items']['form']['inline_entity_form']['entities'])) {
        $entities = $userInput['bundle_items']['form']['inline_entity_form']['entities'];
        if (isset($entities[$entity_form['#ief_row_delta']]['form']['product'])) {
          $productId = $entities[$entity_form['#ief_row_delta']]['form']['product'];
          if (isset($productId[0]['target_id'])) {
            $productId = EntityAutocomplete::extractEntityIdFromAutocompleteInput($productId[0]['target_id']);
          }
        }
      }
    }

    $triggering_element = $form_state->getTriggeringElement();
    // isset($triggering_element) is for edit or add page.
    // isset($userInput['_triggering_element_value']) is for changing product.
    // $userInput['op'] == 'Save') is for clicking product_bundle's form
    // save directly.
    if (!empty($productId) && (isset($triggering_element) || isset($userInput['_triggering_element_value']) || $userInput['op'] == 'Save')) {
      $entity_form['variations']['widget']['#options'] = $this->variationsOptions($productId);
    }
    else {
      $entity_form['variations']['widget']['#disabled'] = TRUE;
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
    $element = [];
    $triggering_element = $form_state->getTriggeringElement();

    // Remove the action and the actions container.
    $array_parents = array_slice($triggering_element['#array_parents'], 0, -2);
    while (!(isset($element['#type']) && ($element['#type'] == 'inline_entity_form'))) {
      $element = NestedArray::getValue($form, $array_parents);
      array_pop($array_parents);
    }

    // Get the origin variations form.
    $variationsForm = $element['variations'];
    $bundleItems = $form_state->getValue('bundle_items');
    if ($element['#op'] == 'add') {
      if (isset($bundleItems['form']['inline_entity_form']['product'][0]['target_id'])) {
        $productId = $bundleItems['form']['inline_entity_form']['product'][0]['target_id'];
      }
    }
    elseif ($element['#op'] == 'edit') {
      $entities = $bundleItems['form']['inline_entity_form']['entities'];
      if (isset($entities[$element['#ief_row_delta']]['form']['product'][0]['target_id'])) {
        $productId = $entities[$element['#ief_row_delta']]['form']['product'][0]['target_id'];
      }
    }
    if (!empty($productId)) {
      $variationsForm['widget']['#options'] = ProductBundleItemInlineForm::variationsOptions($productId);
    }
    return $variationsForm;

  }

  /**
   * {@inheritdoc}
   */
  public function entityFormValidate(array &$entity_form, FormStateInterface $form_state) {
    parent::entityFormValidate($entity_form, $form_state);
    $bundleItems = $form_state->getUserInput()['bundle_items'];
    if ($entity_form['#op'] == 'add') {
      if (isset($bundleItems['form']['inline_entity_form']['variations'])) {
        $formVariationsIds = $bundleItems['form']['inline_entity_form']['variations'];
      }
      $productId = $bundleItems['form']['inline_entity_form']['product'];
      if (isset($productId[0]['target_id'])) {
        $productId = EntityAutocomplete::extractEntityIdFromAutocompleteInput($productId[0]['target_id']);
      }
    }
    elseif ($entity_form['#op'] == 'edit') {
      if (isset($bundleItems['form']['inline_entity_form']['entities'][$entity_form['#ief_row_delta']]['form']['variations'])) {
        $formVariationsIds = $bundleItems['form']['inline_entity_form']['entities'][$entity_form['#ief_row_delta']]['form']['variations'];
      }
      $productId = $bundleItems['form']['inline_entity_form']['entities'][$entity_form['#ief_row_delta']]['form']['product'];
      if (isset($productId[0]['target_id'])) {
        $productId = EntityAutocomplete::extractEntityIdFromAutocompleteInput($productId[0]['target_id']);
      }
    }

    if (isset($productId)) {
      $product = Product::load($productId);
      if (isset($product)) {
        $variationsIds = $product->getVariationIds();
        if (!empty($formVariationsIds) && $formVariationsIds[0] != '_none') {
          foreach ($formVariationsIds as $value) {
            if (!in_array($value, $variationsIds)) {
              $message = t("Each bundle items's variations must belong to the same product");
              $form_state->setError($entity_form, $message);
            }
          }
        }
      }
      else {
        $form_state->setError($entity_form, "The product $productId didn't exist");
      }
    }
  }

}

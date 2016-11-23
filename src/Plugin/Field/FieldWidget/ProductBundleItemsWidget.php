<?php

namespace Drupal\commerce_product_bundle\Plugin\Field\FieldWidget;

use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_product\ProductAttributeFieldManagerInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\commerce_product_bundle\Entity\BundleItemInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'commerce_product_variation_attributes' widget.
 *
 * @FieldWidget(
 *   id = "commerce_product_bundle_items",
 *   label = @Translation("Product bundle items"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ProductBundleItemsWidget extends ProductBundleWidgetBase implements ContainerFactoryPluginInterface {

  /**
   * NOTES:
   *
   *  1. This is based off of ProductVariationAttributesWidget which handled
   *     product -> (1) VARIATION selection based on available attributes.
   *  2. This widget will need to handle
   *     bundle -> (products) -> (MANY) VARIATIONS selections based on attributes
   *     So it will need to do everything ProductVariationAttributesWidget does
   *     but also handle multiple selections.
   */

  /**
   * The product attribute storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $attributeStorage;

  /**
   * The attribute field manager.
   *
   * @var \Drupal\commerce_product\ProductAttributeFieldManagerInterface
   */
  protected $attributeFieldManager;

  /**
   * Constructs a new ProductBundleItemsWidget object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_product\ProductAttributeFieldManagerInterface $attribute_field_manager
   *   The attribute field manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, ProductAttributeFieldManagerInterface $attribute_field_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings, $entity_type_manager);

    $this->attributeStorage = $entity_type_manager->getStorage('commerce_product_attribute');
    $this->attributeFieldManager = $attribute_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('commerce_product.attribute_field_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $product_bundle */
    $bundle = $form_state->get('product_bundle');
    $element['bundle'] = [
      '#type' => 'value',
      '#value' => $bundle->id()
    ];
    // @todo Probably use field_selected_variations
    // @todo Probably need qty fields for each variation - :/
    $element['bundle_items'] = [];
    /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundle_item */
    foreach ($bundle->getBundleItems() as $bundle_item) {
      $parents = [$items->getName(), $delta, 'bundle_items', $bundle_item->id()];
      $element['bundle_items'][$bundle_item->id()] = $this->getBundleItemForm($bundle_item, $form, $form_state, $parents);
    }

    $form['#entity_builders'][] = [$this, 'addBundleItemSelections'];

    return $element;
  }

  private function getBundleItemForm(BundleItemInterface $bundle_item, &$form, FormStateInterface $form_state, array $parents) {
    $bundle_item_form = [];

    /** @var \Drupal\commerce_product\Entity\ProductInterface $product */
    $product = $bundle_item->getProduct();

    /** @var \Drupal\commerce_product\Entity\ProductVariationInterface[] $variations */
    $variations = $bundle_item->getVariations();

    if (count($variations) === 0) {
      // Nothing to purchase, tell the parent form to hide itself.
      // @todo Only hide the entire bundle_items form when there are no variations to select in any item.
      // $form_state->set('hide_form', TRUE);
      $bundle_item_form['variation'] = [
        '#type' => 'value',
        '#value' => 0,
      ];
      return $bundle_item_form;
    }
    elseif (count($variations) === 1) {
      /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $selected_variation */
      $selected_variation = reset($variations);
      // If there is 1 variation but there are attribute fields, then the
      // customer should still see the attribute widgets, to know what they're
      // buying (e.g a product only available in the Small size).
      if (empty($this->attributeFieldManager->getFieldDefinitions($selected_variation->bundle()))) {
        $bundle_item_form['variation'] = [
          '#type' => 'value',
          '#value' => $selected_variation->id(),
        ];
        return $bundle_item_form;
      }
    }

    // Build the bundle item's full attribute form.
    $wrapper_id = Html::getUniqueId('commerce-product-bundle-item-add-to-cart-form');
    $bundle_item_form += [
      '#wrapper_id' => $wrapper_id,
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
    ];

    $user_input = (array) NestedArray::getValue($form_state->getUserInput(), $parents);
    if (!empty($user_input)) {
      $selected_variation = $this->selectVariationFromUserInput($variations, $user_input);
    }
    else {
      $selected_variation = $this->variationStorage->loadFromContext($product);
      // The returned variation must also be enabled.
      if (!in_array($selected_variation, $variations)) {
        $selected_variation = reset($variations);
      }
    }

    $bundle_item_form['variation'] = [
      '#type' => 'value',
      '#value' => $selected_variation->id(),
    ];
    // Set the selected variation in the form state for our AJAX callback.
    $form_state->set('purchased_entity][widget][0][bundle_items][' . $bundle_item->id() . '][selected_variation', $selected_variation->id());

    $bundle_item_form['attributes'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['attribute-widgets'],
      ],
    ];
    foreach ($this->getItemAttributeInfo($selected_variation, $variations) as $field_name => $attribute) {
      $bundle_item_form['attributes'][$field_name] = [
        '#type' => $attribute['element_type'],
        '#title' => $attribute['title'],
        '#options' => $attribute['values'],
        '#required' => $attribute['required'],
        '#default_value' => $selected_variation->getAttributeValueId($field_name),
        '#ajax' => [
          'callback' => [get_class($this), 'ajaxRefresh'],
          'wrapper' => $bundle_item_form['#wrapper_id'],
        ],
      ];
      // Convert the _none option into #empty_value.
      if (isset($bundle_item_form['attributes'][$field_name]['#options']['_none'])) {
        if (!$bundle_item_form['attributes'][$field_name]['#required']) {
          $bundle_item_form['attributes'][$field_name]['#empty_value'] = '';
        }
        unset($bundle_item_form['attributes'][$field_name]['#options']['_none']);
      }
      // 1 required value -> Disable the bundle_item_variations to skip unneeded ajax calls.
      if ($attribute['required'] && count($attribute['values']) === 1) {
        $bundle_item_form['attributes'][$field_name]['#disabled'] = TRUE;
      }
      // Optimize the UX of optional attributes:
      // - Hide attributes that have no values.
      // - Require attributes that have a value on each variation.
      if (empty($bundle_item_form['attributes'][$field_name]['#options'])) {
        $bundle_item_form['attributes'][$field_name]['#access'] = FALSE;
      }
      if (!isset($bundle_item_form['attributes'][$field_name]['#empty_value'])) {
        $bundle_item_form['attributes'][$field_name]['#required'] = TRUE;
      }
    }

    return $bundle_item_form;
  }

  /**
   * Selects a product variation from user input.
   *
   * If there's no user input (form viewed for the first time), the default
   * variations are returned.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface[] $variations
   *   An array of product variations.
   * @param array $user_input
   *   The user input.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface
   *   The selected variation.
   */
  protected function selectVariationFromUserInput(array $variations, array $user_input) {
    $current_variation = reset($variations);
    if (!empty($user_input)) {
      $attributes = $user_input['attributes'];
      foreach ($variations as $variation) {
        $match = TRUE;
        foreach ($attributes as $field_name => $value) {
          if ($variation->getAttributeValueId($field_name) != $value) {
            $match = FALSE;
          }
        }
        if ($match) {
          $current_variation = $variation;
          break;
        }
      }
    }

    return $current_variation;
  }

  /**
   * Gets the attribute information for the selected product variation.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $selected_variation
   *   The selected product variation.
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface[] $variations
   *   The available product variations.
   *
   * @return array[]
   *   The attribute information, keyed by field name.
   */
  protected function getItemAttributeInfo(ProductVariationInterface $selected_variation, array $variations) {
    $attributes = [];
    $field_definitions = $this->attributeFieldManager->getFieldDefinitions($selected_variation->bundle());
    $field_map = $this->attributeFieldManager->getFieldMap($selected_variation->bundle());
    $field_names = array_column($field_map, 'field_name');
    $index = 0;
    foreach ($field_names as $field_name) {
      /** @var \Drupal\commerce_product\Entity\ProductAttributeInterface $attribute_type */
      $attribute_type = $this->attributeStorage->load(substr($field_name, 10));
      $field = $field_definitions[$field_name];
      $attributes[$field_name] = [
        'field_name' => $field_name,
        'title' => $field->getLabel(),
        'required' => $field->isRequired(),
        'element_type' => $attribute_type->getElementType(),
      ];
      // The first attribute gets all values. Every next attribute gets only
      // the values from variations matching the previous attribute value.
      // For 'Color' and 'Size' attributes that means getting the colors of all
      // variations, but only the sizes of variations with the selected color.
      $callback = NULL;
      if ($index > 0) {
        $previous_field_name = $field_names[$index - 1];
        $previous_field_value = $selected_variation->getAttributeValueId($previous_field_name);
        $callback = function ($variation) use ($previous_field_name, $previous_field_value) {
          /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $variation */
          return $variation->getAttributeValueId($previous_field_name) == $previous_field_value;
        };
      }

      $attributes[$field_name]['values'] = $this->getAttributeValues($variations, $field_name, $callback);
      $index++;
    }
    // Filter out attributes with no values.
    $attributes = array_filter($attributes, function ($attribute) {
      return !empty($attribute['values']);
    });

    return $attributes;
  }

  /**
   * Gets the attribute values of a given set of variations.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface[] $variations
   *   The variations.
   * @param string $field_name
   *   The field name of the attribute.
   * @param callable|null $callback
   *   An optional callback to use for filtering the list.
   *
   * @return array[]
   *   The attribute values, keyed by attribute ID.
   */
  protected function getAttributeValues(array $variations, $field_name, callable $callback = NULL) {
    $values = [];
    foreach ($variations as $variation) {
      if (is_null($callback) || call_user_func($callback, $variation)) {
        $attribute_value = $variation->getAttributeValue($field_name);
        if ($attribute_value) {
          $values[$attribute_value->id()] = $attribute_value->label();
        }
        else {
          $values['_none'] = '';
        }
      }
    }

    return $values;
  }

  /**
   * Entity builder: updates the order item with the bundle item selections.
   *
   * @param string $entity_type
   *   The entity type.
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $order_item
   *   The entity updated with the submitted values.
   * @param array $form
   *   The complete form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function addBundleItemSelections($entity_type, OrderItemInterface $order_item, array $form, FormStateInterface $form_state) {
    $bundle_item_selections = [];
    foreach ($form_state->getValue('purchased_entity')[0]['bundle_items'] as $item => $selection) {
      $bundle_item_selections[] = [
        'bundle_item' => $item,
        'selected_entity' => $selection['variation'],
        'selected_qty' => '1', // @todo implement quantity field
      ];
    }
    $order_item->set('field_bundle_item_selections', $bundle_item_selections);
  }

}

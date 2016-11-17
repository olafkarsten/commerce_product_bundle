<?php

namespace Drupal\commerce_product_bundle\Plugin\Field\FieldWidget;

use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Event\ProductVariationAjaxChangeEvent;
use Drupal\commerce_product\Event\ProductEvents;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the base structure for product bundle widgets.
 *
 * Product bundle widget forms depends on the 'bundle' being present in
 * $form_state.
 *
 * @see \Drupal\commerce_product_bundle\Plugin\Field\FieldFormatter\AddToCartFormatter::viewElements().
 */
abstract class ProductBundleWidgetBase extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * NOTES:
   *
   *  1. This is based off of ProductVariationWidgetBase which handled
   *     product -> (1) VARIATION selection
   *  2. This base widget will always need to handle
   *     bundle -> (products) -> (MANY) VARIATIONS selections
   */

  /**
   * The product bundle item storage.
   *
   * @var \Drupal\commerce_product\ProductBundleItemStorageInterface
   */
  protected $bundleItemStorage;

  /**
   * The product storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface;
   */
  protected $productStorage;

  /**
   * The product variation storage.
   *
   * @var \Drupal\commerce_product\ProductVariationStorageInterface
   */
  protected $variationStorage;

  /**
   * Constructs a new ProductBundleWidgetBase object.
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
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->bundleItemStorage = $entity_type_manager->getStorage('commerce_product_bundle_i');
    $this->productStorage = $entity_type_manager->getStorage('commerce_product');
    $this->variationStorage = $entity_type_manager->getStorage('commerce_product_variation');
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $entity_type = $field_definition->getTargetEntityTypeId();
    $field_name = $field_definition->getName();

    // @todo: Check that this field is only a commerce_product_bundle type field.
    return $entity_type == 'commerce_order_item' && $field_name == 'purchased_entity';
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // Assumes that the bundle item ID comes from an $element['bundle_item'] built
    // in formElement().
    // @todo Ensure the structure and values of this array are correct.
    foreach ($values as $key => $value) {
      $values[$key] = [
        'target_id' => $value['bundle_item'],
      ];
    }

    return $values;
  }

  /**
   * #ajax callback: Replaces the rendered fields on variation change.
   *
   * Assumes the existence of a 'selected_variation' in $form_state.
   *
   * @todo We will need to support the existence of MULTIPLE selected variations.
   */
  public static function ajaxRefresh(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Render\MainContent\MainContentRendererInterface $ajax_renderer */
    $ajax_renderer = \Drupal::service('main_content_renderer.ajax');
    $request = \Drupal::request();
    $route_match = \Drupal::service('current_route_match');
    /** @var \Drupal\Core\Ajax\AjaxResponse $response */
    $response = $ajax_renderer->renderResponse($form, $request, $route_match);

    $variation = ProductVariation::load($form_state->get('selected_variation'));
    /** @var \Drupal\commerce_product\ProductVariationFieldRendererInterface $variation_field_renderer */
    $variation_field_renderer = \Drupal::service('commerce_product.variation_field_renderer');
    $view_mode = $form_state->get('form_display')->getMode();
    $variation_field_renderer->replaceRenderedFields($response, $variation, $view_mode);
    // Allow modules to add arbitrary ajax commands to the response.
    $event = new ProductVariationAjaxChangeEvent($variation, $response, $view_mode);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch(ProductEvents::PRODUCT_VARIATION_AJAX_CHANGE, $event);

    return $response;
  }

}

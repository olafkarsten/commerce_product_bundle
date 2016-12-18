<?php

namespace Drupal\commerce_product_bundle\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'commerce_product_bundle_item_selection_default' formatter.
 *
 * @FieldFormatter(
 *   id = "commerce_product_bundle_item_selection_default",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "commerce_product_bundle_item_selection"
 *   }
 * )
 */
class BundleItemSelectionDefaultFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The product variation storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $productVariationStorage;

  /**
   * The product bundle item storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $bundleItemStorage;

  /**
   * Constructs a new PriceDefaultFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->productVariationStorage = $entity_type_manager->getStorage('commerce_product_variation');
    $this->bundleItemStorage = $entity_type_manager->getStorage('commerce_product_bundle_i');
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
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $qty = $item->selected_qty;
      $bundle_item = $this->bundleItemStorage->load($item->bundle_item)->label();
      $variation = $this->productVariationStorage->load($item->selected_entity)->label();
      $elements[$delta] = [
        '#markup' => "{$bundle_item}: ({$qty}) {$variation}",
        '#cache' => [
          'contexts' => [],
        ],
      ];
    }

    return $elements;
  }

}

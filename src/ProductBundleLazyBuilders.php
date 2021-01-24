<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Provides #lazy_builder callbacks.
 */
class ProductBundleLazyBuilders implements TrustedCallbackInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs a new ProductBundleLazyBuilders object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    FormBuilderInterface $form_builder,
    EntityRepositoryInterface $entity_repository
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
    $this->entityRepository = $entity_repository;
  }

  /**
   * Builds the add to cart form.
   *
   * @param string $product_bundle_id
   *   The product bundle ID.
   * @param string $view_mode
   *   The view mode used to render the product bundle.
   * @param bool $combine
   *   TRUE to combine order items containing the same product bundle items.
   * @param string $langcode
   *   The langcode.
   *
   * @return array
   *   A renderable array containing the add to cart form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Core\Form\EnforcedResponseException
   * @throws \Drupal\Core\Form\FormAjaxException
   */
  public function addToCartForm(
    $product_bundle_id,
    $view_mode,
    $combine,
    $langcode
  ) {
    /** @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage */
    $order_item_storage = $this->entityTypeManager->getStorage('commerce_order_item');

    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $product_bundle */
    $product_bundle = $this->entityTypeManager->getStorage('commerce_product_bundle')
      ->load($product_bundle_id);

    // Load Product for current language.
    $product_bundle = $this->entityRepository->getTranslationFromContext($product_bundle, $langcode);

    $order_item = $order_item_storage->createFromPurchasableEntity($product_bundle);

    /** @var \Drupal\commerce_cart\Form\AddToCartFormInterface $form_object */
    $form_object = $this->entityTypeManager->getFormObject('commerce_order_item', 'add_to_cart');
    $form_object->setEntity($order_item);
    // The default form id is based on the variation ID, but in this case the
    // product bundle id is more reliable (the variation/selection might change
    // between requests due to an availability change, for example).
    $form_object->setFormId($form_object->getBaseFormId() . '_commerce_product_bundle_' . $product_bundle_id);
    $form_state = (new FormState())->setFormState([
      'product_bundle' => $product_bundle,
      'view_mode' => $view_mode,
      'settings' => [
        'combine' => $combine,
      ],
    ]);

    return $this->formBuilder->buildForm($form_object, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['addToCartForm'];
  }

}

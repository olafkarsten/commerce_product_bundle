<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of product bundle item entities.
 *
 * @ingroup commerce_product_bundle
 */
class ProductBundleItemListBuilder extends EntityListBuilder implements FormInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The parent product bundle.
   *
   * @var \Drupal\commerce_product_bundle\Entity\BundleInterface
   */
  protected $productBundle;

  /**
   * The delta values of the bundle items field items.
   *
   * @var integer[]
   */
  protected $bundleItemDeltas = [];

  /**
   * Whether tabledrag is enabled.
   *
   * @var bool
   */
  protected $hasTableDrag = TRUE;

  /**
   * Constructs a new ProductBundleItemListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityStorageInterface $storage,
    EntityRepositoryInterface $entity_repository,
    RouteMatchInterface $route_match,
    FormBuilderInterface $form_builder
  ) {
    parent::__construct($entity_type, $storage);

    $this->formBuilder = $form_builder;
    $this->productBundle = $route_match->getParameter('commerce_product_bundle');
    // The product bundle might not be available when the list builder is
    // instantiated by Views to build the list of operations.
    if (!empty($this->productBundle)) {
      $this->productBundle = $entity_repository->getTranslationFromContext($this->productBundle);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(
    ContainerInterface $container,
    EntityTypeInterface $entity_type
  ) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('entity.repository'),
      $container->get('current_route_match'),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_product_bundle_items';
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $bundleItems = $this->productBundle->getBundleItems();
    foreach ($bundleItems as $delta => $bundleItem) {
      $this->bundleItemDeltas[$bundleItem->id()] = $delta;
    }
    return $bundleItems;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['minqty'] = $this->t('Minimum quantity');
    $header['maxqty'] = $this->t('Maximum quantity');
    $header['price'] = $this->t('Unit price');
    $header['status'] = $this->t('Status');
    if ($this->hasTableDrag) {
      $header['weight'] = $this->t('Weight');
    }
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $entity */
    $row['id'] = $entity->id();
    $row['title'] = $entity->label();
    $row['minqty'] = $entity->getMinimumQuantity();
    $row['maxqty'] = $entity->getMaximumQuantity();
    $row['price'] = $entity->getUnitPrice();
    $row['status'] = $entity->isPublished() ? $this->t('Published') : $this->t('Unpublished');
    if ($this->hasTableDrag) {
      $row['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $entity->label()]),
        '#title_display' => 'invisible',
        '#default_value' => $this->productBundleDeltas[$entity->id()],
        '#attributes' => ['class' => ['weight']],
      ];
    }
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = $this->formBuilder->getForm($this);
    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $build['pager'] = [
        '#type' => 'pager',
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $bundleItems = $this->load();
    if (count($bundleItems) <= 1) {
      $this->hasTableDrag = FALSE;
    }
    $delta = 10;
    // Dynamically expand the allowed delta based on the number of entities.
    $count = count($bundleItems);
    if ($count > 20) {
      $delta = ceil($count / 2);
    }

    // Override the page title to contain the product bundle label.
    $form['#title'] = $this->t('%product bundle items', ['%productbundle' => $this->productBundle->label()]);

    $form['bundle_items'] = [
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#empty' => $this->t('There are no @label yet.', ['@label' => $this->entityType->getPluralLabel()]),
    ];
    foreach ($bundleItems as $entity) {
      $row = $this->buildRow($entity);
      $row['id'] = ['#markup' => $row['id']];
      $row['title'] = ['#markup' => $row['title']];
      $row['minqty'] = ['#markup' => $row['minqty']];
      $row['maxqty'] = ['#markup' => $row['maxqty']];
      $row['price'] = [
        '#type' => 'inline_template',
        '#template' => '{{ price|commerce_price_format }}',
        '#context' => [
          'price' => $row['price'],
        ],
      ];
      $row['status'] = ['#markup' => $row['status']];
      if (isset($row['weight'])) {
        $row['weight']['#delta'] = $delta;
      }
      $form['bundle_items'][$entity->id()] = $row;
    }

    if ($this->hasTableDrag) {
      $form['bundle_items']['#tabledrag'][] = [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'weight',
      ];
      $form['actions']['#type'] = 'actions';
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => t('Save'),
        '#button_type' => 'primary',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // No validation.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $bundleItems = $this->productBundle->getBundleItems();
    $newBundleItems = [];
    foreach ($form_state->getValue('bundle_items') as $id => $value) {
      $newBundleItems[$value['weight']] = $bundleItems[$this->bundleItemDeltas[$id]];
    }
    $this->productBundle->setBundleItems($newBundleItems);
    $this->productBundle->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    if ($entity->access('create') && $entity->hasLinkTemplate('duplicate-form')) {
      $operations['duplicate'] = [
        'title' => $this->t('Duplicate'),
        'weight' => 20,
        'url' => $this->ensureDestination($entity->toUrl('duplicate-form')),
      ];
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  protected function ensureDestination(Url $url) {
    return $url->mergeOptions(['query' => ['destination' => Url::fromRoute('<current>')->toString()]]);
  }

}

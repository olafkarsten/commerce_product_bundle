<?php

namespace Drupal\commerce_product_bundle\Form;

use Drupal\commerce_product\Form\ProductForm;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for product bundle edit forms.
 *
 * @ingroup commerce_product_bundle
 */
class ProductBundleForm extends ContentEntityForm {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new ProductBundleForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   */
  public function __construct(
    EntityRepositoryInterface $entity_repository,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    TimeInterface $time,
    DateFormatterInterface $date_formatter
  ) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);

    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_product_bundle\Entity\ProductBundle $entity */
    // Skip building the form if there are no products.
    $product_query = $this->entityTypeManager->getStorage('commerce_product')
      ->getQuery();
    if ($product_query->count()->execute() == 0) {
      $link = Link::createFromRoute('Add a product.', 'entity.commerce_product.add_page');
      $form['warning'] = [
        '#markup' => t("Product bundles can't be created until at least one product has been added. @link", ['@link' => $link->toString()]),
      ];
      return $form;
    }
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $commerce_product_bundle */
    $commerce_product_bundle = $this->entity;
    $form = parent::form($form, $form_state);

    $form['#tree'] = TRUE;
    $form['#theme'] = ['commerce_product_form'];
    $form['#attached']['library'][] = 'commerce_product/form';
    // Changed must be sent to the client, for later overwrite error checking.
    $form['changed'] = [
      '#type' => 'hidden',
      '#default_value' => $commerce_product_bundle->getChangedTime(),
    ];
    $form['status']['#group'] = 'footer';

    $last_saved = t('Not saved yet');
    if (!$commerce_product_bundle->isNew()) {
      $last_saved = $this->dateFormatter->format($commerce_product_bundle->getChangedTime(), 'short');
    }
    $form['meta'] = [
      '#attributes' => ['class' => ['entity-meta__header']],
      '#type' => 'container',
      '#group' => 'advanced',
      '#weight' => -100,
      'published' => [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $commerce_product_bundle->isPublished() ? $this->t('Published') : $this->t('Not published'),
        '#access' => !$commerce_product_bundle->isNew(),
        '#attributes' => [
          'class' => ['entity-meta__title'],
        ],
      ],
      'changed' => [
        '#type' => 'item',
        '#wrapper_attributes' => [
          'class' => ['entity-meta__last-saved', 'container-inline'],
        ],
        '#markup' => '<h4 class="label inline">' . $this->t('Last saved') . '</h4> ' . $last_saved,
      ],
      'author' => [
        '#type' => 'item',
        '#wrapper_attributes' => [
          'class' => ['author', 'container-inline'],
        ],
        '#markup' => '<h4 class="label inline">' . $this->t('Author') . '</h4> ' . $commerce_product_bundle->getOwner()
          ->getDisplayName(),
      ],
    ];
    $form['advanced'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['entity-meta']],
      '#weight' => 99,
    ];
    $form['visibility_settings'] = [
      '#type' => 'details',
      '#title' => t('Visibility settings'),
      '#open' => TRUE,
      '#group' => 'advanced',
      '#access' => !empty($form['stores']['#access']),
      '#attributes' => [
        'class' => ['product-visibility-settings'],
      ],
      '#weight' => 30,
    ];
    $form['path_settings'] = [
      '#type' => 'details',
      '#title' => t('URL path settings'),
      '#open' => !empty($form['path']['widget'][0]['alias']['#default_value']),
      '#group' => 'advanced',
      '#access' => !empty($form['path']['#access']) && $commerce_product_bundle->get('path')
        ->access('edit'),
      '#attributes' => [
        'class' => ['path-form'],
      ],
      '#attached' => [
        'library' => ['path/drupal.path'],
      ],
      '#weight' => 60,
    ];
    $form['author'] = [
      '#type' => 'details',
      '#title' => t('Authoring information'),
      '#group' => 'advanced',
      '#attributes' => [
        'class' => ['product-form-author'],
      ],
      '#weight' => 90,
      '#optional' => TRUE,
    ];
    if (isset($form['uid'])) {
      $form['uid']['#group'] = 'author';
    }
    if (isset($form['created'])) {
      $form['created']['#group'] = 'author';
    }
    if (isset($form['path'])) {
      $form['path']['#group'] = 'path_settings';
    }
    if (isset($form['stores'])) {
      $form['stores']['#group'] = 'visibility_settings';
      $form['#after_build'][] = [
        ProductForm::class,
        'hideEmptyVisibilitySettings',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    if ($this->entity->isNew()) {
      $actions['submit_continue'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save and add bundle items'),
        '#button_type' => 'secondary',
        '#continue' => TRUE,
        '#submit' => ['::submitForm', '::save'],
      ];
    }

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $entity->save();
    $this->messenger()
      ->addStatus($this->t('The product bundle %label has been successfully saved.', [
        '%label' => $entity->label(),
      ]));

    if (!empty($form_state->getTriggeringElement()['#continue'])) {
      $form_state->setRedirect('entity.commerce_product_bundle_i.collection', ['commerce_product_bundle' => $entity->id()]);
    }
    else {
      $form_state->setRedirect('entity.commerce_product_bundle.canonical', ['commerce_product_bundle' => $entity->id()]);
    }
  }

}

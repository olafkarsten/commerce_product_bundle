<?php

namespace Drupal\commerce_product_bundle\Form;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\commerce_product_bundle\Entity\BundleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting a product bundle revision.
 *
 * @ingroup commerce_product_bundle
 */
class ProductBundleRevisionRevertForm extends ConfirmFormBase {


  /**
   * The product bundle revision.
   *
   * @var \Drupal\commerce_product_bundle\Entity\BundleInterface
   */
  protected $revision;

  /**
   * The product bundle storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $ProductBundleStorage;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new ProductBundleRevisionRevertForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The product bundle storage.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityStorageInterface $entity_storage, DateFormatterInterface $date_formatter) {
    $this->ProductBundleStorage = $entity_storage;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('commerce_product_bundle'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_product_bundle_revision_revert_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to revert to the revision from %revision-date?', ['%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime())]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.commerce_product_bundle.version_history', array('commerce_product_bundle' => $this->revision->id()));
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Revert');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $product_bundle_revision = NULL) {
    $this->revision = $this->ProductBundleStorage->loadRevision($product_bundle_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // The revision timestamp will be updated when the revision is saved. Keep
    // the original one for the confirmation message.
    $original_revision_timestamp = $this->revision->getRevisionCreationTime();

    $this->revision = $this->prepareRevertedRevision($this->revision, $form_state);
    $this->revision->revision_log = t('Copy of the revision from %date.', ['%date' => $this->dateFormatter->format($original_revision_timestamp)]);
    $this->revision->save();

    $this->logger('content')->notice('Product bundle: reverted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    drupal_set_message(t('product bundle %title has been reverted to the revision from %revision-date.', ['%title' => $this->revision->label(), '%revision-date' => $this->dateFormatter->format($original_revision_timestamp)]));
    $form_state->setRedirect(
      'entity.commerce_product_bundle.version_history',
      array('commerce_product_bundle' => $this->revision->id())
    );
  }

  /**
   * Prepares a revision to be reverted.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleInterface $revision
   *   The revision to be reverted.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleInterface
   *   The prepared revision ready to be stored.
   */
  protected function prepareRevertedRevision(BundleInterface $revision, FormStateInterface $form_state) {
    $revision->setNewRevision();
    $revision->isDefaultRevision(TRUE);
    $revision->setRevisionCreationTime(REQUEST_TIME);

    return $revision;
  }

}
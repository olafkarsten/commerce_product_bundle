<?php

namespace Drupal\commerce_product_bundle\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\entity\Form\EntityDuplicateFormTrait;

/**
 * Form controller for product bundle item edit forms.
 *
 * @ingroup commerce_product_bundle
 */
class ProductBundleItemForm extends ContentEntityForm {

  use EntityDuplicateFormTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityFromRouteMatch(
    RouteMatchInterface $route_match,
    $entity_type_id
  ) {
    if ($route_match->getRawParameter('commerce_product_bundle_i') !== NULL) {
      $entity = $route_match->getParameter('commerce_product_bundle_i');
    }
    else {
      /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $product_bundle */
      $product_bundle = $route_match->getParameter('commerce_product_bundle');
      /** @var \Drupal\commerce_product_bundle\Entity\BundleTypeInterface $product_bundle_type */
      $product_bundle_type = $this->entityTypeManager->getStorage('commerce_product_bundle_type')
        ->load($product_bundle->bundle());
      $values = [
        'type' => $product_bundle_type->getBundleItemTypeId(),
        'bundle_id' => $product_bundle->id(),
      ];
      $entity = $this->entityTypeManager->getStorage('commerce_product_bundle_i')
        ->create($values);
    }

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $is_new = $this->entity->isNew();
    $this->entity->save();
    $this->postSave($this->entity, $this->operation);
    $this->messenger()
      ->addStatus($this->t('Saved the %label product bundle item.', [
        '%label' => $this->entity->label(),
      ]));
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $is_new ? SAVED_NEW : SAVED_UPDATED;
  }

}

<?php

namespace Drupal\commerce_product_bundle;

use Drupal\content_translation\ContentTranslationHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the translation handler for products bundles.
 *
 * Based on NodeTranslationHandler.
 */
class ProductBundleTranslationHandler extends ContentTranslationHandler {

  /**
   * {@inheritdoc}
   */
  public function entityFormAlter(array &$form, FormStateInterface $form_state, EntityInterface $entity) {
    parent::entityFormAlter($form, $form_state, $entity);

    // Move the translation fieldset to a vertical tab.
    if (isset($form['content_translation'])) {
      $form['content_translation'] += [
        '#group' => 'advanced',
        '#attributes' => [
          'class' => ['product-bundle-translation-options'],
        ],
      ];
      $form['content_translation']['#weight'] = 100;
      // The basic product bundle values will be used, no need for specific elements.
      $form['content_translation']['status']['#access'] = FALSE;
      $form['content_translation']['name']['#access'] = FALSE;
      $form['content_translation']['created']['#access'] = FALSE;
    }

    /** @var \Drupal\Core\Entity\ContentEntityFormInterface $form_object */
    $form_object = $form_state->getFormObject();
    $form_langcode = $form_object->getFormLangcode($form_state);
    $translations = $entity->getTranslationLanguages();
    // Change the submit button labels to inform the user that
    // publishing/unpublishing won't apply to all translations.
    if (!$entity->isNew() && (!isset($translations[$form_langcode]) || count($translations) > 1)) {
      foreach (['publish', 'unpublish', 'submit'] as $button) {
        if (isset($form['actions'][$button])) {
          $form['actions'][$button]['#value'] .= ' ' . t('(this translation)');
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function entityFormEntityBuild($entity_type, EntityInterface $entity, array $form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $entity */
    if ($form_state->hasValue('content_translation')) {
      $translation = &$form_state->getValue('content_translation');
      $translation['status'] = $entity->isPublished();
      $account = $entity->uid->entity;
      $translation['uid'] = $account ? $account->id() : 0;
      $translation['created'] = \Drupal::service('date.formatter')->format($entity->created->value, 'custom', 'Y-m-d H:i:s O');
    }
    parent::entityFormEntityBuild($entity_type, $entity, $form, $form_state);
  }

}

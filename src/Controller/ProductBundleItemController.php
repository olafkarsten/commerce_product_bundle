<?php

namespace Drupal\commerce_product_bundle\Controller;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides title callbacks for product variation routes.
 */
class ProductBundleItemController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs a new ProductBundleItemController.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, TranslationInterface $string_translation) {
    $this->entityRepository = $entity_repository;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('string_translation')
    );
  }

  /**
   * Provides the add title callback for product bundle items.
   *
   * @return string
   *   The title for the product bundle item add page.
   */
  public function addTitle() {
    return $this->t('Add bundle item');
  }

  /**
   * Provides the edit title callback for product bundle items.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   *
   * @return string
   *   The title for the product bundle item edit page.
   */
  public function editTitle(RouteMatchInterface $route_match) {
    $product_bundle_item = $route_match->getParameter('commerce_product_bundle_i');
    $product_bundle_item = $this->entityRepository->getTranslationFromContext($product_bundle_item);

    return $this->t('Edit %label', ['%label' => $product_bundle_item->label()]);
  }

  /**
   * Provides the delete title callback for product bundle items.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   *
   * @return string
   *   The title for the product bundle item delete page.
   */
  public function deleteTitle(RouteMatchInterface $route_match) {
    $product_bundle_item = $route_match->getParameter('commerce_product_bundle_i');
    $product_bundle_item = $this->entityRepository->getTranslationFromContext($product_bundle_item);

    return $this->t('Delete %label', ['%label' => $product_bundle_item->label()]);
  }

  /**
   * Provides the collection title callback for product bundle items.
   *
   * @return string
   *   The title for the product bundle item collection.
   */
  public function collectionTitle() {
    return $this->t('Bundle items');
  }

}

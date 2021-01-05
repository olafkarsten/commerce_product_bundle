<?php

namespace Drupal\commerce_product_bundle\ContextProvider;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_product_bundle\Entity\ProductBundleType;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\layout_builder\DefaultsSectionStorageInterface;
use Drupal\layout_builder\OverridesSectionStorageInterface;

/**
 * Sets the current product bundle item as context on commerce_product_bundle_i routes.
 *
 * @todo Remove once core gets a generic EntityRouteContext.
 */
class ProductBundleItemContext implements ContextProviderInterface {

  use StringTranslationTrait;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The product bundle item storage.
   *
   * @var \Drupal\commerce_product_bundle\ProductBundleItemStorageInterface
   */
  protected $productBundleItemStorage;

  /**
   * Constructs a new ProductRouteContext object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager) {
    $this->routeMatch = $route_match;
    $this->productBundleItemStorage = $entity_type_manager->getStorage('commerce_product_bundle_i');
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids) {
    $context_definition = new EntityContextDefinition('entity:commerce_product_bundle_i', new TranslatableMarkup('Product bundle item'));
    $value = $this->routeMatch->getParameter('commerce_product_bundle_i');
    if ($value === NULL) {
      if ($product_bundle = $this->routeMatch->getParameter('commerce_product_bundle')) {
        $value = $this->productBundleItemStorage->loadFromContext($product_bundle);
      }
      /** @var \Drupal\commerce_product_bundle\Entity\BundleTypeInterface $product_bundle_type */
      elseif ($product_bundle_type = $this->routeMatch->getParameter('commerce_product_bundle_type')) {
        if (is_string($product_bundle_type)) {
          $product_bundle_type = ProductBundleType::load($product_bundle_type);
        }
        $value = $this->productBundleItemStorage->createWithSampleValues($product_bundle_type->getBundleItemTypeId());
      }
      // @todo Simplify this logic once EntityTargetInterface is available
      // @see https://www.drupal.org/project/drupal/issues/3054490
      elseif (strpos($this->routeMatch->getRouteName(), 'layout_builder') !== FALSE) {
        /** @var \Drupal\layout_builder\SectionStorageInterface $section_storage */
        $section_storage = $this->routeMatch->getParameter('section_storage');
        if ($section_storage instanceof DefaultsSectionStorageInterface) {
          $context = $section_storage->getContextValue('display');
          assert($context instanceof EntityDisplayInterface);
          if ($context->getTargetEntityTypeId() === 'commerce_product_bundle') {
            $product_bundle_type = ProductBundleType::load($context->getTargetBundle());
            $value = $this->productBundleItemStorage->createWithSampleValues($product_bundle_type->getBundleItemTypeId());
          }
        }
        elseif ($section_storage instanceof OverridesSectionStorageInterface) {
          $context = $section_storage->getContextValue('entity');
          if ($context instanceof ProductInterface) {
            $value = $context->getDefaultVariation();
            if ($value === NULL) {
              $product_bundle_type = ProductBundleType::load($context->bundle());
              $value = $this->productBundleItemStorage->createWithSampleValues($product_bundle_type->getBundleItemTypeId());
            }
          }
        }
      }
    }

    $cacheability = new CacheableMetadata();
    $cacheability->setCacheContexts(['route']);
    $context = new Context($context_definition, $value);
    $context->addCacheableDependency($cacheability);

    return ['commerce_product_bundle_i' => $context];
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {
    return $this->getRuntimeContexts([]);
  }

}

<?php

namespace Drupal\commerce_product_bundle\Controller;

use Drupal\commerce_product_bundle\Entity\BundleItemTypeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for product bundle item routes.
 */
class ProductBundleItemController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a NodeController object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(DateFormatterInterface $date_formatter, RendererInterface $renderer) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

//  /**
//   * Displays add content links for available content types.
//   *
//   * Redirects to node/add/[type] if only one content type is available.
//   *
//   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
//   *   A render array for a list of the node types that can be added; however,
//   *   if there is only one node type defined for the site, the function
//   *   will return a RedirectResponse to the node add page for that one node
//   *   type.
//   */
//  public function addPage() {
//    $build = [
//      '#theme' => 'node_add_list',
//      '#cache' => [
//        'tags' => $this->entityManager()->getDefinition('node_type')->getListCacheTags(),
//      ],
//    ];
//
//    $content = array();
//
//    // Only use node types the user has access to.
//    foreach ($this->entityManager()->getStorage('node_type')->loadMultiple() as $type) {
//      $access = $this->entityManager()->getAccessControlHandler('node')->createAccess($type->id(), NULL, [], TRUE);
//      if ($access->isAllowed()) {
//        $content[$type->id()] = $type;
//      }
//      $this->renderer->addCacheableDependency($build, $access);
//    }
//
//    // Bypass the node/add listing if only one content type is available.
//    if (count($content) == 1) {
//      $type = array_shift($content);
//      return $this->redirect('node.add', array('node_type' => $type->id()));
//    }
//
//    $build['#content'] = $content;
//
//    return $build;
//  }

//  /**
//   * Provides the node submission form.
//   *
//   * @param \Drupal\node\NodeTypeInterface $node_type
//   *   The node type entity for the node.
//   *
//   * @return array
//   *   A node submission form.
//   */
//  public function add(NodeTypeInterface $node_type) {
//    $node = $this->entityManager()->getStorage('node')->create(array(
//      'type' => $node_type->id(),
//    ));
//
//    $form = $this->entityFormBuilder()->getForm($node);
//
//    return $form;
//  }

  /**
   * The _title_callback for the node.add route.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleItemTypeInterface $item_type
   *   The current bundle item type.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(BundleItemTypeInterface $item_type) {
    return $this->t('Create @name', array('@name' => $item_type->label()));
  }

}

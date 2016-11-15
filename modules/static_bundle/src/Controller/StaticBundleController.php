<?php

namespace Drupal\commerce_static_bundle\Controller;

use Drupal\commerce_product_bundle\Entity\BundleInterface;
use Drupal\commerce_product_bundle\Entity\BundleTypeInterface;
use Drupal\commerce_static_bundle\StaticBundleStorageInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for static bundle item routes.
 */
class StaticBundleController extends ControllerBase implements ContainerInjectionInterface {

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
   * Displays a node revision.
   *
   * @param int $bundle_revision
   *   The node revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($bundle_revision) {
    $item = $this->entityManager()->getStorage('commerce_static_bundle')->loadRevision($bundle_revision);
    $item = $this->entityManager()->getTranslationFromContext($item);
    $item_view_controller = new StaticBundleViewController($this->entityManager, $this->renderer, $this->currentUser());
    $page = $item_view_controller->view($item);
    // unset($page['nodes'][$item->id()]['#cache']);
    return $page;
  }

  /**
   * Page title callback for a node revision.
   *
   * @param int $bundle_revision
   *   The node revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($bundle_revision) {
    $bundle = $this->entityManager()->getStorage('commerce_static_bundle_item')->loadRevision($bundle_revision);
    return $this->t('Revision of %title from %date', array('%title' => $bundle->label(), '%date' => format_date($bundle->getRevisionCreationTime())));
  }

  /**
   * Generates an overview table of older revisions of a node.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleInterface $commerce_static_bundle
   *   A bundle item object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(BundleInterface $commerce_static_bundle) {
    $account = $this->currentUser();
    $langcode = $commerce_static_bundle->language()->getId();
    $langname = $commerce_static_bundle->language()->getName();
    $languages = $commerce_static_bundle->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $bundle_storage = $this->entityManager()->getStorage('commerce_static_bundle');
    $type = $commerce_static_bundle->getType();

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $commerce_static_bundle->label()]) : $this->t('Revisions for %title', ['%title' => $commerce_static_bundle->label()]);
    $header = array($this->t('Revision'), $this->t('Operations'));

    $revert_permission = (($account->hasPermission("revert $type revisions") || $account->hasPermission('revert all revisions') || $account->hasPermission('administer nodes')) && $commerce_static_bundle->access('update'));
    $delete_permission = (($account->hasPermission("delete $type revisions") || $account->hasPermission('delete all revisions') || $account->hasPermission('administer nodes')) && $commerce_static_bundle->access('delete'));

    $rows = array();
    $default_revision = $commerce_static_bundle->getRevisionId();

    foreach ($this->getRevisionIds($commerce_static_bundle, $bundle_storage) as $vid) {
      /** @var \Drupal\commerce_static_bundle\Entity\StaticBundleItem $revision */
      $revision = $bundle_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->revision_timestamp->value, 'short');
        if ($vid != $commerce_static_bundle->getRevisionId()) {
          $link = $this->l($date, new Url('entity.commerce_static_bundle_item.revision', ['commerce_static_bundle_item' => $commerce_static_bundle->id(), 'revision' => $vid]));
        }
        else {
          $link = $commerce_static_bundle->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => ['#markup' => $revision->revision_log_message->value, '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        // @todo Simplify once https://www.drupal.org/node/2334319 lands.
        $this->renderer->addCacheableDependency($column['data'], $username);
        $row[] = $column;

        if ($vid == $default_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];

          $rows[] = [
            'data' => $row,
            'class' => ['revision-current'],
          ];
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $vid < $commerce_static_bundle->getRevisionId() ? $this->t('Revert') : $this->t('Set as current revision'),
              'url' => $has_translations ?
                Url::fromRoute('node.revision_revert_translation_confirm', ['node' => $commerce_static_bundle->id(), 'node_revision' => $vid, 'langcode' => $langcode]) :
                Url::fromRoute('node.revision_revert_confirm', ['node' => $commerce_static_bundle->id(), 'node_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('node.revision_delete_confirm', ['node' => $commerce_static_bundle->id(), 'node_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];

          $rows[] = $row;
        }
      }
    }

    $build['node_revisions_table'] = array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#attached' => array(
        'library' => array('node/drupal.node.admin'),
      ),
      '#attributes' => ['class' => 'node-revision-table'],
    );

    $build['pager'] = array('#type' => 'pager');

    return $build;
  }

  /**
   * The _title_callback for the node.add route.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleTypeInterface $bundle_type
   *   The current bundle item type.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(BundleTypeInterface $bundle_type) {
    return $this->t('Create @name', array('@name' => $bundle_type->label()));
  }

  /**
   * Gets a list of node revision IDs for a specific node.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleInterface $bundle
   *   The bundle item entity.
   * @param \Drupal\commerce_static_bundle\StaticBundleStorageInterface $bundle_storage
   *   The node storage handler.
   *
   * @return int[]
   *   Node revision IDs (in descending order).
   */
  protected function getRevisionIds(BundleInterface $bundle, StaticBundleStorageInterface $bundle_storage) {
    $result = $bundle_storage->getQuery()
      ->allRevisions()
      ->condition($bundle->getEntityType()->getKey('id'), $bundle->id())
      ->sort($bundle->getEntityType()->getKey('revision'), 'DESC')
      ->pager(50)
      ->execute();
    return array_keys($result);
  }

}

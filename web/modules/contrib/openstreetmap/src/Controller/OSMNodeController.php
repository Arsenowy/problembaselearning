<?php

namespace Drupal\openstreetmap\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\openstreetmap\Entity\OSMNodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class OSMNodeController.
 *
 *  Returns responses for OSM Node routes.
 */
class OSMNodeController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a OSM Node revision.
   *
   * @param int $osm_node_revision
   *   The OSM Node revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($osm_node_revision) {
    $osm_node = $this->entityTypeManager()->getStorage('osm_node')
      ->loadRevision($osm_node_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('osm_node');

    return $view_builder->view($osm_node);
  }

  /**
   * Page title callback for a OSM Node revision.
   *
   * @param int $osm_node_revision
   *   The OSM Node revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($osm_node_revision) {
    $osm_node = $this->entityTypeManager()->getStorage('osm_node')
      ->loadRevision($osm_node_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $osm_node->label(),
      '%date' => $this->dateFormatter->format($osm_node->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a OSM Node.
   *
   * @param \Drupal\openstreetmap\Entity\OSMNodeInterface $osm_node
   *   A OSM Node object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(OSMNodeInterface $osm_node) {
    $account = $this->currentUser();
    $osm_node_storage = $this->entityTypeManager()->getStorage('osm_node');

    $langcode = $osm_node->language()->getId();
    $langname = $osm_node->language()->getName();
    $languages = $osm_node->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations
      ? $this->t('@langname revisions for %title', [
        '@langname' => $langname,
        '%title' => $osm_node->label(),
      ])
      : $this->t('Revisions for %title', ['%title' => $osm_node->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all osm node revisions") || $account->hasPermission('administer osm node entities')));
    $delete_permission = (($account->hasPermission("delete all osm node revisions") || $account->hasPermission('administer osm node entities')));

    $rows = [];

    $vids = $osm_node_storage->revisionIds($osm_node);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\openstreetmap\OSMNodeInterface $revision */
      $revision = $osm_node_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $osm_node->getRevisionId()) {
          $link = $this->l($date, new Url('entity.osm_node.revision', [
            'osm_node' => $osm_node->id(),
            'osm_node_revision' => $vid,
          ]));
        }
        else {
          $link = $osm_node->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.osm_node.translation_revert', [
                'osm_node' => $osm_node->id(),
                'osm_node_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.osm_node.revision_revert', [
                'osm_node' => $osm_node->id(),
                'osm_node_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.osm_node.revision_delete', [
                'osm_node' => $osm_node->id(),
                'osm_node_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['osm_node_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

  /**
   * Retrieves all data from OSM and displays a link to edit the node there.
   */
  public function osmData(OSMNodeInterface $osm_node) {
    $osm_id = $osm_node->get('osm_id')->value;
    $type = $osm_node->isWay() ? 'way' : 'node';
    if ($osm_node->isWay()) {
      $json = \Drupal::service('overpass')->way($osm_id);
    }
    else {
      $json = \Drupal::service('overpass')->node($osm_id);
    }
    return [
      'data' => [
        '#markup' => '<pre>' . print_r($json, TRUE) . '</pre>',
      ],
      'link' => [
        '#type' => 'link',
        '#title' => t('Edit on OSM'),
        '#url' => Url::fromUri("https://www.openstreetmap.org/edit?{$type}={$osm_id}"),
        '#options' => [
          'attributes' => [
            'target' => '_blank',
          ],
        ],
      ],
    ];
  }

}

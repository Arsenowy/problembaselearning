<?php

namespace Drupal\openstreetmap\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a OSM Node revision.
 *
 * @ingroup openstreetmap
 */
class OSMNodeRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The OSM Node revision.
   *
   * @var \Drupal\openstreetmap\Entity\OSMNodeInterface
   */
  protected $revision;

  /**
   * The OSM Node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $oSMNodeStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->oSMNodeStorage = $container->get('entity_type.manager')->getStorage('osm_node');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'osm_node_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.osm_node.version_history', ['osm_node' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $osm_node_revision = NULL) {
    $this->revision = $this->oSMNodeStorage->loadRevision($osm_node_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->oSMNodeStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('OSM Node: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()->addMessage(t('Revision from %revision-date of OSM Node %title has been deleted.', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
      '%title' => $this->revision->label(),
    ]));
    $form_state->setRedirect(
      'entity.osm_node.canonical',
       ['osm_node' => $this->revision->id()]
    );
    if ($this->connection->query(
      'SELECT COUNT(DISTINCT vid)
      FROM {osm_node_field_revision}
      WHERE id = :id',
      [':id' => $this->revision->id()]
    )->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.osm_node.version_history',
         ['osm_node' => $this->revision->id()]
      );
    }
  }

}

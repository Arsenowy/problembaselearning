<?php

namespace Drupal\openstreetmap\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the OSM Node entity.
 *
 * @ingroup openstreetmap
 *
 * @ContentEntityType(
 *   id = "osm_node",
 *   label = @Translation("OSM Node"),
 *   bundle_label = @Translation("OSM Node type"),
 *   handlers = {
 *     "storage" = "Drupal\openstreetmap\OSMNodeStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\openstreetmap\OSMNodeListBuilder",
 *     "views_data" = "Drupal\openstreetmap\Entity\OSMNodeViewsData",
 *     "translation" = "Drupal\openstreetmap\OSMNodeTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\openstreetmap\Form\OSMNodeForm",
 *       "add" = "Drupal\openstreetmap\Form\OSMNodeForm",
 *       "edit" = "Drupal\openstreetmap\Form\OSMNodeForm",
 *       "delete" = "Drupal\openstreetmap\Form\OSMNodeDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\openstreetmap\OSMNodeHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\openstreetmap\OSMNodeAccessControlHandler",
 *   },
 *   base_table = "osm_node",
 *   data_table = "osm_node_field_data",
 *   revision_table = "osm_node_revision",
 *   revision_data_table = "osm_node_field_revision",
 *   translatable = TRUE,
 *   permission_granularity = "bundle",
 *   admin_permission = "administer osm node entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "canonical" = "/osm_node/{osm_node}",
 *     "add-page" = "/admin/content/osm_node/add",
 *     "add-form" = "/admin/content/osm_node/add/{osm_node_type}",
 *     "edit-form" = "/admin/content/osm_node/{osm_node}/edit",
 *     "delete-form" = "/admin/content/osm_node/{osm_node}/delete",
 *     "version-history" = "/admin/content/osm_node/{osm_node}/revisions",
 *     "revision" = "/admin/content/osm_node/{osm_node}/revisions/{osm_node_revision}/view",
 *     "revision_revert" = "/admin/content/osm_node/{osm_node}/revisions/{osm_node_revision}/revert",
 *     "revision_delete" = "/admin/content/osm_node/{osm_node}/revisions/{osm_node_revision}/delete",
 *     "translation_revert" = "/admin/content/osm_node/{osm_node}/revisions/{osm_node_revision}/revert/{langcode}",
 *     "collection" = "/admin/content/osm_node",
 *   },
 *   bundle_entity_type = "osm_node_type",
 *   field_ui_base_route = "entity.osm_node_type.edit_form"
 * )
 */
class OSMNode extends EditorialContentEntityBase implements OSMNodeInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * Checks if this node is a way.
   *
   * @return bool
   *   Whether or not
   */
  public function isWay() {
    return !!$this->get('way')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {


    parent::preSave($storage);

  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * Saves, optionally pulling new version from OSM.
   *
   * @param bool $pull_from_osm
   *   Whether or not to pull from OSM.
   *
   * @return int
   *   The node ID
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function save($pull_from_osm = TRUE) {
    if ($pull_from_osm) {
      // Get the node from OSM.
      if ($this->isWay()) {
        $data = \Drupal::service('overpass')->way((int) $this->get('osm_id')->value);
        $geodata = \Drupal::service('geofield.wkt_generator')->wktBuildPolygon(array_map(function ($point) {
          return [$point->lon, $point->lat];
        }, $data->geometry));
      }
      else {
        $data = \Drupal::service('overpass')->node((int) $this->get('osm_id')->value);
        $geodata = \Drupal::service('geofield.wkt_generator')->wktBuildPoint([
          $data->lon,
          $data->lat
        ]);
      }

      $this->set('geodata', [['value' => $geodata]]);

      // Go through all the tags and if the entity has that field, set it
      $this->setFieldsFromTags($data->tags);
    }

    return parent::save();
  }

  /**
   * Loads and saves an OSM Node given an ID.
   *
   * Used for syncing.
   */
  public static function saveInPlace($id) {
    $node = static::load($id);
    $node->save();
  }

  /**
   * Gets a new OSM Node given a way id and optional bundle.
   *
   * Used in batch operations.
   */
  public static function fromWayId($way_id, $bundle = 'default') {
    $element = \Drupal::service('overpass')->way($way_id);
    return static::fromElement($element, $bundle);
  }

  /**
   * Saves a new OSM Node given a way id and optional bundle.
   *
   * Used in batch operations.
   */
  public static function saveFromWayId($way_id, $bundle = 'default') {
    return static::saveFromElement(static::fromWayId($way_id, $bundle), $bundle);
  }

  /**
   * Gets a new OSM Node given a node id and optional bundle.
   *
   * Used in batch operations.
   */
  public static function fromNodeId($node_id, $bundle) {
    $element = \Drupal::service('overpass')->node($node_id);
    return static::fromElement($element, $bundle);
  }

  /**
   * Saves a new OSM Node given a node id and optional bundle.
   *
   * Used in batch operations.
   */
  public static function saveFromNodeId($node_id, $bundle = 'default') {
    return static::saveFromElement(static::fromNodeId($node_id, $bundle), $bundle);
  }

  public function setFieldsFromTags($tags) {
    $manager = \Drupal::service('entity_field.manager');
    // We don't want to save data into any core fields except "name"
    $base_fields = array_diff(
      array_keys($manager->getBaseFieldDefinitions('osm_node')),
      ['name']
    );
    foreach ($tags as $key => $value) {
      if ($this->hasField($key) && !in_array($key, $base_fields)) {
        $this->set($key, $value);
      } else if ($this->hasField("field_{$key}")) {
        $this->set("field_{$key}", $value);
      }
    }
  }

  /**
   * Given a payload from OSM, creates or updates an OSM Node.
   *
   * Does not save.
   */
  public static function fromElement($element, $bundle = 'default') {
    $id = \Drupal::database()->query(
      "SELECT `id`
      FROM {osm_node_field_data}
      WHERE `osm_id` = :osm_id",
      [':osm_id' => $element->id]
    )->fetch(\PDO::FETCH_COLUMN);
    if ($id) {
      $osm_node = OSMNode::load($id);
    } else {
      $osm_node = static::create([
        'type' => $bundle,
        'osm_id' => ['value' => $element->id],
        'way' => ['value' => ($element->type === 'way')]
      ]);
    }
    if ($osm_node->isWay()) {
      $geodata = \Drupal::service('geofield.wkt_generator')->wktBuildPolygon(array_map(function ($point) {
        return [$point->lon, $point->lat];
      }, $element->geometry));
    }
    else {
      $geodata = \Drupal::service('geofield.wkt_generator')->wktBuildPoint([
        $element->lon,
        $element->lat
      ]);
    }

    $osm_node->set('geodata', [['value' => $geodata]]);
    $osm_node->setFieldsFromTags($element->tags);
    return $osm_node;
  }

  /**
   * Given an API response from OSM and optional bundle, upserts an OSM Node.
   */
  public static function saveFromElement($element, $bundle = 'default', &$context = []) {
    $osm_node = static::fromElement($element, $bundle);
    $new = $osm_node->isNew();
    try {
      $osm_node->save(false);
    } catch (\Throwable $e) {
      \Drupal::logger('openstreetmap')->error($e->getMessage());
    }
    $context['message'] = ($new ? 'Added ' : 'Updated ') . $osm_node->label();
    return $osm_node;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['osm_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('OSM ID'))
      ->setDescription(t('The OSM ID of the node.'))
      ->addConstraint('UniqueField')
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [ 'type' => 'string_textfield' ])
      ->setDisplayOptions('view', [ 'type' => 'string' ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['way'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Is Way'))
      ->setDescription(t('Check if the ID refers to a way (set of nodes) instead of a single node'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [ 'type' => 'boolean_checkbox' ]);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the OSM Node entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['geodata'] = BaseFieldDefinition::create('geofield')
      ->setLabel(t('Geodata'))
      ->setDescription(t('Geodata of the node. Set from OpenStreetMap.'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the OSM Node is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the OSM Node entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}

<?php

namespace Drupal\openstreetmap_queries\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\openstreetmap_queries\OSMQueryInterface;

/**
 * Defines the OSM query entity class.
 *
 * @ContentEntityType(
 *   id = "osm_query",
 *   label = @Translation("Overpass Query"),
 *   label_collection = @Translation("Overpass Queries"),
 *   handlers = {
 *     "view_builder" = "Drupal\openstreetmap_queries\OSMQueryViewBuilder",
 *     "list_builder" = "Drupal\openstreetmap_queries\OSMQueryListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\openstreetmap_queries\OSMQueryAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\openstreetmap_queries\Form\OSMQueryForm",
 *       "edit" = "Drupal\openstreetmap_queries\Form\OSMQueryForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "osm_query",
 *   admin_permission = "administer openstreetmap queries",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/osm_query/add",
 *     "canonical" = "/admin/structure/osm_query/{osm_query}",
 *     "edit-form" = "/admin/structure/osm_query/{osm_query}/edit",
 *     "delete-form" = "/admin/structure/osm_query/{osm_query}/delete",
 *     "collection" = "/admin/structure/osm_query"
 *   },
 *   field_ui_base_route = "entity.osm_query.settings"
 * )
 */
class OSMQuery extends ContentEntityBase implements OSMQueryInterface {

  use EntityChangedTrait;

  /**
   * Runs the contents of this query through the overpass service.
   */
  public function execute() {
    $query = $this->get('code')->value;
    $bundle = $this->get('bundle')->value;
    try {
      \Drupal::service('overpass')->nodesFromQuery($query, $bundle);
    } catch (\Throwable $e) {
      \Drupal::messenger()->addError($e->getMessage());
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return (bool) $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus($status) {
    $this->set('status', $status);
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

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the overpass query.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('A boolean indicating whether the overpass query is enabled.'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['code'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Overpass Query'))
      ->setDescription(t('Valid Overpass API Query'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => -3,
        'settings' => ['allowed_formats' => ['no_filter']],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['bundle'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Bundle'))
      ->setDescription(t('Bundle into which to save the results of this query'))
      ->setCardinality(1)
      ->setDisplayOptions('form', [
        'type' => 'options_select',
      ])
      ->setSettings([
        'allowed_values_function' => 'openstreetmap_query_bundle_option_callback',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);


    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the overpass query was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the overpass query was last edited.'));

    return $fields;
  }

}

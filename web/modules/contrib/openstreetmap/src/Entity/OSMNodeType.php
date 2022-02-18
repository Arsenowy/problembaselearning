<?php

namespace Drupal\openstreetmap\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the OSM Node type entity.
 *
 * @ConfigEntityType(
 *   id = "osm_node_type",
 *   label = @Translation("OSM Node type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\openstreetmap\OSMNodeTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\openstreetmap\Form\OSMNodeTypeForm",
 *       "edit" = "Drupal\openstreetmap\Form\OSMNodeTypeForm",
 *       "delete" = "Drupal\openstreetmap\Form\OSMNodeTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\openstreetmap\OSMNodeTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "osm_node_type",
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   admin_permission = "administer site configuration",
 *   bundle_of = "osm_node",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/osm_node_type/{osm_node_type}",
 *     "add-form" = "/admin/structure/osm_node_type/add",
 *     "edit-form" = "/admin/structure/osm_node_type/{osm_node_type}/edit",
 *     "delete-form" = "/admin/structure/osm_node_type/{osm_node_type}/delete",
 *     "collection" = "/admin/structure/osm_node_type"
 *   }
 * )
 */
class OSMNodeType extends ConfigEntityBundleBase implements OSMNodeTypeInterface {

  /**
   * The OSM Node type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The OSM Node type label.
   *
   * @var string
   */
  protected $label;

}

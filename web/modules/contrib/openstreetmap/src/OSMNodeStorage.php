<?php

namespace Drupal\openstreetmap;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\openstreetmap\Entity\OSMNodeInterface;

/**
 * Defines the storage handler class for OSM Node entities.
 *
 * This extends the base storage class, adding required special handling for
 * OSM Node entities.
 *
 * @ingroup openstreetmap
 */
class OSMNodeStorage extends SqlContentEntityStorage implements OSMNodeStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(OSMNodeInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {osm_node_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {osm_node_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(OSMNodeInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {osm_node_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('osm_node_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}

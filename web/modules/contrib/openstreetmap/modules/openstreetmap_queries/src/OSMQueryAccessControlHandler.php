<?php

namespace Drupal\openstreetmap_queries;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the openstreetmap queries entity type.
 */
class OSMQueryAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view openstreetmap queries');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, [
          'edit openstreetmap queries',
          'administer openstreetmap queries',
        ], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, [
          'delete openstreetmap queries',
          'administer openstreetmap queries',
        ], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, [
      'create openstreetmap queries',
      'administer openstreetmap queries',
    ], 'OR');
  }

}

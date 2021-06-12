<?php

namespace Drupal\field_encrypt_searchable;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Blind Index entity.
 *
 * @see \Drupal\field_encrypt_searchable\Entity\BlindIndexEntity.
 */
class BlindIndexEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\field_encrypt_searchable\Entity\BlindIndexEntityInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished blind index entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published blind index entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit blind index entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete blind index entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add blind index entities');
  }


}

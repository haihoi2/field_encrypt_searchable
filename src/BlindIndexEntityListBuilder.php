<?php

namespace Drupal\field_encrypt_searchable;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Blind Index entities.
 *
 * @ingroup field_encrypt_searchable
 */
class BlindIndexEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Blind Index ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\field_encrypt_searchable\Entity\BlindIndexEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.blind_index_entity.edit_form',
      ['blind_index_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}

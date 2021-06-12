<?php

namespace Drupal\field_encrypt_searchable\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Blind Index entity.
 *
 * @ingroup field_encrypt_searchable
 *
 * @ContentEntityType(
 *   id = "blind_index_entity",
 *   label = @Translation("Blind Index"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\field_encrypt_searchable\BlindIndexEntityListBuilder",
 *     "views_data" = "Drupal\field_encrypt_searchable\Entity\BlindIndexEntityViewsData",
 *
 *     "access" = "Drupal\field_encrypt_searchable\BlindIndexEntityAccessControlHandler",
 *   },
 *   base_table = "blind_index_entity",
 *   translatable = FALSE,
 *   admin_permission = "administer blind index entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 * )
 */
class BlindIndexEntity extends ContentEntityBase implements BlindIndexEntityInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

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

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Blind Index entity.'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['vid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Vid'))
      ->setDescription(t('The revision ID of entity of Blind index.'))
      ->setRequired(TRUE);

    $fields['entity_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('The ID of entity of Blind index.'))
      ->setRequired(TRUE);

    $fields['entity_type_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity type id'))
      ->setDescription(t('The entity type of entity of Blind index.'))
      ->setSettings(array(
        'max_length' => 255,
        'text_processing' => 0,
      ))
      ->setRequired(TRUE);

    $fields['entity_bundle'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity bundle'))
      ->setDescription(t('The entity bundle of entity of Blind index.'))
      ->setSettings(array(
        'max_length' => 255,
        'text_processing' => 0,
      ))
      ->setRequired(TRUE);

    $fields['entity_bundle'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity bundle'))
      ->setDescription(t('The entity bundle of entity of Blind index.'))
      ->setSettings(array(
        'max_length' => 255,
        'text_processing' => 0,
      ))
      ->setRequired(TRUE);

    $fields['entity_field'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity field'))
      ->setDescription(t('The entity field of entity of Blind index.'))
      ->setSettings(array(
        'max_length' => 255,
        'text_processing' => 0,
      ))
      ->setRequired(TRUE);

    $fields['entity_field_delta'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Entity field delta'))
      ->setDescription(t('The entity field delta of entity of Blind index.'))
      ->setRequired(TRUE);

    $fields['index_value'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Blind index value'))
      ->setDescription(t('The blind index value.'))
      ->setRequired(TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Blind Index is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}

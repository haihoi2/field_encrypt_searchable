<?php

namespace Drupal\field_encrypt_searchable\Plugin\views\filter;

/**
 * Encrypted field views filter.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("encrypted_field_filter")
 */

use Drupal;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field_encrypt_searchable\Transformation\SearchLikeTransformation;
use Drupal\views\Plugin\views\filter\StringFilter;
use Drupal\views\Views;
use ParagonIE\CipherSweet\Backend\FIPSCrypto;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\CipherSweet\EncryptedField;
use ParagonIE\CipherSweet\KeyProvider\StringProvider;

class EncryptedFieldViewsFilter extends StringFilter {

  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['fields'] = ['default' => ''];

    return $options;
  }

  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $this->view->initStyle();

    // Allow to choose all fields as possible
    if ($this->view->style_plugin->usesFields()) {
      $options = [];
      foreach ($this->view->display_handler->getHandlers('field') as $name => $field) {
        $definition = $field->definition;
        /** @var EntityFieldManagerInterface $entityFieldManager */
        $entityFieldManager = \Drupal::service('entity_field.manager');
        $storage = $entityFieldManager->getFieldStorageDefinitions($definition['entity_type'])[$name];
        if (empty($storage) || !is_callable([$storage, 'getThirdPartySetting'])) {
          continue;
        }
        // Check if the field is encrypted.
        $hasBlindIndex = $storage->getThirdPartySetting('field_encrypt', 'encrypt', FALSE)
          && $storage->getThirdPartySetting('field_encrypt', 'blind_index', FALSE);
        if ($hasBlindIndex) {
          $options[$name] = $field->adminLabel(TRUE);
        }
      }
//      $form['operator']['#options'] = array_intersect_key($form['operator']['#options'], array_flip(['=', 'contains']));
      unset($form['operator']);
      if ($options) {
        $form['fields'] = [
          '#type' => 'select',
          '#title' => $this->t('Choose encrypted field for filtering'),
          '#description' => $this->t("This filter doesn't work for very special field handlers."),
          '#options' => $options,
          '#default_value' => $this->options['fields'],
        ];
      }
      else {
        $form_state->setErrorByName('', $this->t('You have to add some fields to be able to use this filter.'));
      }
    }
  }

  public function query()
  {
    $selectedFieldId = $this->options['fields'];
    $field = $this->view->field[$selectedFieldId];

    $entityFieldManager = Drupal::service('entity_field.manager');
    $storage = $entityFieldManager->getFieldStorageDefinitions('user')[$selectedFieldId];
    $encryption_profile_id = $storage->getThirdPartySetting('field_encrypt', 'encryption_profile', []);
    $encryption_profile = Drupal::service('encrypt.encryption_profile.manager')
      ->getEncryptionProfile($encryption_profile_id);

    $provider = new StringProvider($encryption_profile->getEncryptionKey()->getKeyValue());
    $engine = new CipherSweet($provider, new FIPSCrypto());
    $prepareBlindIndex = (new EncryptedField($engine));
    $prepareBlindIndex->addBlindIndex(
      new BlindIndex("encrypt_searchable", [new SearchLikeTransformation(0, mb_strlen($this->value))], 128)
    );
    $index = $prepareBlindIndex->getBlindIndex($this->value, 'encrypt_searchable');

    $field->ensureMyTable();
    $configuration = [
      'table' => 'blind_index_entity',
      'field' => 'entity_id',
      'left_table' => $field->tableAlias,
      'left_field' => 'entity_id',
      'operator' => '=',
      'extra' => [
        [
          'field' => 'langcode',
          'value' => Drupal::languageManager()->getCurrentLanguage()->getId(),
        ],
        [
          'field' => 'status',
          'value' => 1,
        ],
        [
          'field' => 'entity_type_id',
          'value' => $field->definition['entity_type'],
        ],
        [
          'field' => 'entity_field',
          'value' => $field->field,
        ],
      ]
    ];
    $join = Views::pluginManager('join')->createInstance('standard', $configuration);
    $this->query->addRelationship("{$field->realField}__blind_index_entity", $join, 'node_field_data');
    $this->query->addWhere('AND', "{$field->realField}__blind_index_entity.index_value", $index);
  }

}

<?php

namespace Drupal\openstreetmap\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\openstreetmap\Entity\OSMNodeType;

/**
 * Allows arbitrary import from Overpass query.
 */
class OSMImportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openstreetmap_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['query'] = [
      '#type' => 'textarea',
      '#title' => 'Overpass Query',
    ];

    $bundles = [
      '' => '- none -',
    ];

    foreach (OSMNodeType::loadMultiple() as $slug => $bundle) {
      $bundles[$slug] = $bundle->label();
    }

    $form['bundle'] = [
      '#type' => 'select',
      '#options' => $bundles,
      '#title' => 'OSM Node Type to Import',
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => 998,
      'search' => [
        '#type' => 'submit',
        '#value' => 'Import',
        '#name' => 'import',
      ],
      'replace' => [
        '#type' => 'submit',
        '#value' => 'Run Query',
        '#name' => 'run',
      ],
    ];

    return $form;
  }

  /**
   * Ensures a bundle has been set to import into.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $button = $form_state->getTriggeringElement();
    $bundle = $form_state->getValue('bundle');
    if ($button['#name'] === 'import' && !$bundle) {
      $form_state->setErrorByName('bundle', $this->t('You must select a bundle into which to import these nodes'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $query = $form_state->getValue('query');
    if (stripos($query, '[out:json];') !== 0) {
      $query = '[out:json];' . $query;
    }
    if (stripos($query, 'out geom;') === FALSE) {
      $query = $query . 'out geom;';
    }
    \Drupal::messenger()->addMessage(Markup::create('<pre style="padding: 1rem;border: 1px solid currentColor">' . $query . '</pre>'));

    $button = $form_state->getTriggeringElement();
    if ($button['#name'] === 'import') {
      $bundle = $form_state->getValue('bundle');
      \Drupal::service('overpass')->nodesFromQuery($query, $bundle);
    }
    elseif ($button['#name'] === 'run') {
      $response = \Drupal::service('overpass')->query($query);
      \Drupal::messenger()->addMessage(Markup::create('<pre>' . print_r($response, TRUE) . '</pre>'));
    }

  }

}

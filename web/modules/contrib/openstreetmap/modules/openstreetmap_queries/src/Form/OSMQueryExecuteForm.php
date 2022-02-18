<?php

namespace Drupal\openstreetmap_queries\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\openstreetmap\Entity\OSMNode;
use Drupal\openstreetmap\Entity\OSMNodeType;
use Drupal\openstreetmap_queries\Entity\OSMQuery;
use Drupal\openstreetmap_queries\OSMQueryInterface;

/**
 * Form for executing OSM queries and previewing results.
 */
class OSMQueryExecuteForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openstreetmap_query_execute_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, OSMQueryInterface $osm_query = NULL) {

    if ($osm_query) {
      $form['osm_query_id'] = [
        '#type' => 'hidden',
        '#value' => $osm_query->id(),
      ];

      $code = $osm_query->get('code')->value;

      $form['query'] = [
        '#markup' => '<pre>' . $code . '</pre>'
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
      'execute' => [
        '#type' => 'submit',
        '#value' => 'Execute',
        '#name' => 'execute'
      ],
      'test' => [
        '#type' => 'submit',
        '#value' => 'Test',
        '#name' => 'test'
      ]
    ];


    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#name'] === 'execute') {
      if ($form_state->getValue('osm_query_id')) {
        $osm_query = OSMQuery::load($form_state->getValue('osm_query_id'));
        $osm_query->execute();
      }
    } else if ($form_state->getTriggeringElement()['#name'] === 'test') {
      $osm_query = OSMQuery::load($form_state->getValue('osm_query_id'));
      $code = $osm_query->get('code')->value;
      $json = \Drupal::service('overpass')->query($code);
      \Drupal::messenger()->addMessage('Results in ' . count(json_decode($json)->elements) . ' nodes');
    }

  }

}

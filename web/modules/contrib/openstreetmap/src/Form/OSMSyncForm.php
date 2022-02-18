<?php

namespace Drupal\openstreetmap\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\openstreetmap\Entity\OSMNode;

/**
 * Allows syncing all current OSM Nodes with source.
 */
class OSMSyncForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openstreetmap_sync_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $count = count(OSMNode::loadMultiple());

    $form['description'] = [
      '#plain_text' => "There are currently {$count} OSM nodes in Drupal tracking OpenStreetMap data.",
      '#suffix' => '<br />',
      '#weight' => 0,
    ];

    $form['instructions'] = [
      '#plain_text' => 'Press Sync to re-fetch all information from OpenStreetMap.',
      '#suffix' => '<br />',
      '#weight' => 99,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => 100,

      'sync' => [
        '#type' => 'submit',
        '#value' => 'Sync',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch = [
      'title' => 'Syncing OSM Nodes',
      'operations' => [],
    ];

    $nodes = OSMNode::loadMultiple();

    foreach ($nodes as $node) {
      $batch['operations'][] = [
        ['Drupal\openstreetmap\Entity\OSMNode', 'saveInPlace'],
        [$node->id()],
      ];
    }

    \Drupal::moduleHandler()->alter('openstreetmap_sync_batch', $batch);

    batch_set($batch);

    \Drupal::moduleHandler()->invokeAll('openstreetmap_sync');
  }

}

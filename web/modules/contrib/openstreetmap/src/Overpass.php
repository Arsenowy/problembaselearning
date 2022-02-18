<?php

namespace Drupal\openstreetmap;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Database\Connection;
use GuzzleHttp\Client;

/**
 * The overpass service, which handles imports from the overpass endpoint.
 */
class Overpass {
  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */

  protected $database;

  /**
   * Our HTTP Connector.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * User-specified configuration.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs a new Overpass object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The Drupal database connection.
   * @param \GuzzleHttp\Client $client
   *   Client for making HTTP Calls.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Drupal configuration.
   */
  public function __construct(Connection $connection, Client $client, ConfigFactory $configFactory) {
    $this->database = $connection;
    $this->httpClient = $client;
    $this->config = $configFactory->get('openstreetmap.settings');
  }

  /**
   * Given an overpass query, passes it to the configured parser.
   */
  public function query(string $query) {
    $endpoint = $this->config->get('endpoint');
    if (!$endpoint) {
      throw new \Exception('OpenStreetMap Overpass API endpoint not configured');
    }
    $json = $this->httpClient->request('GET', $endpoint, [
      'query' => [
        'data' => $query,
      ],
      'timeout' => 25
    ]);
    $contents = $json->getBody()->getContents();
    return $contents;
  }

  /**
   * Given a query, coerces it to JSON response.
   */
  public function json(string $query) {
    return json_decode($this->query("[out:json];{$query}out;"));
  }

  /**
   * Given a node ID, constructs JSON query for single node.
   */
  public function node(int $node_id) {
    return current($this->json("node({$node_id});")->elements);
  }

  /**
   * Given a way ID, constructs JSON query for way and geometry.
   */
  public function way(int $way_id) {
    return current(json_decode($this->query("[out:json];way({$way_id});out geom;"))->elements);
  }

  /**
   * Given a query, constructs OSM Nodes.
   */
  public function nodesFromQuery($query, $bundle = 'default') {
    $results = json_decode($this->query($query));
    if (!$results->elements) {
      return;
    }
    $batch = [
      'title' => 'Adding nodes from query',
      'operations' => array_values(array_map(function ($element) use ($bundle) {
        return [
          ['Drupal\openstreetmap\Entity\OSMNode', 'saveFromElement'],
          [$element, $bundle]
        ];
      }, $results->elements))
    ];
    batch_set($batch);
  }
}

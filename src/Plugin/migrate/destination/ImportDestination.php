<?php

declare(strict_types = 1);

namespace Drupal\lcm_acs_xrm\Plugin\migrate\destination;

use Drupal\lcm_acs_xrm\Service\MauticApi;
use Drupal\lcm_acs_xrm\Service\SftpClientFactory;
use Drupal\migrate\Event\ImportAwareInterface;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\migrate\Plugin\migrate\destination\DestinationBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StreamWrapper\PublicStream;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The 'acs_importidestination' destination plugin.
 *
 * @MigrateDestination(id = "acs_importidestination")
 */
class ImportDestination extends DestinationBase implements ContainerFactoryPluginInterface, ImportAwareInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The sftp client factory.
   *
   * @var \Drupal\lcm_acs_xrm\Service\SftpClientFactory
   */
  protected $sftpClient;

  /**
   * The Mautic service.
   *
   * @var \Drupal\lcm_acs_xrm\Service\MauticApi
   */
  protected $mauticApi;

  /**
   * The destination IDs.
   *
   * @var array
   */
  protected $ids;

  /**
   * The destination fields.
   *
   * @var array
   */
  protected $fields;

  /**
   * The soutce file .
   *
   * @var array
   */
  protected $sourceFile;

  /**
   * The size of the batch.
   *
   * @var int
   */
  protected $batchSize;

  /**
   * Constructs a new ImportDestination instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *   The migration object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\lcm_acs_xrm\Service\SftpClientFactory $sftp_client_factoty
   *   The sftp client factory.
   * @param \Drupal\lcm_acs_xrm\Service\MauticApi $mautic_api
   *   The Mautic API service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, ConfigFactoryInterface $config_factory, SftpClientFactory $sftp_client_factory, MauticApi $mautic_api) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->initializeMigrationConfiguration();
    $this->mauticApi = $mautic_api;
    $this->batchSize = $this->configuration['batch'] ?? 1;
    $this->config = $config_factory->get('lcm_acs_xrm.settings');
    $this->sftpClient = $sftp_client_factory->create($this->config->get('lcm_acs_xrm_sftp_hostname'), $this->config->get('lcm_acs_xrm_sftp_port'));
    if (isset($this->configuration['custom_object'])) {
      // Retrieve custom object information from Mautic based on configuration and validate.
      $this->customObject = $this->mauticApi->getCustomObject($configuration['custom_object']);
      if (!isset($this->customObject['customObject']) || empty($this->customObject['customObject'])) {
        throw new \InvalidArgumentException('The custom object ' . $configuration['custom_object'] . ' was not found.');
      }
    }

  }

  /**
   * Constructs the ImportDestinationCustomObjects plugin instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *   The migration object.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('config.factory'),
      $container->get('lcm_acs_xrm.sftp_client_factory'),
      $container->get('lcm_acs_xrm.mautic_api'));
  }

  /**
   * Initializes the migration configuration.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the migration plugin is not found.
   */
  protected function initializeMigrationConfiguration() {
    $migration_plugin_manager = \Drupal::service('plugin.manager.migration');
    $migrationConfiguration = $migration_plugin_manager->getDefinition($this->migration->id());
    // print_r($migrationConfiguration);
    // Get the IDs.
    foreach ($migrationConfiguration['source']['ids'] as $id) {
      $this->ids[$id] = ['type' => 'string'];
    }
    // Get the fields.
    foreach ($migrationConfiguration['process'] as $fieldkey => $fieldname) {
      $this->fields[$fieldkey] = $fieldname;
    }
    // The source path.
    $this->sourceFile = $migrationConfiguration['source']['path'];
  }

  /**
   * Prepares the data for import.
   *
   * @param \Drupal\migrate\Row $row
   *   The row being processed.
   *
   * @return array
   *   The prepared data.
   */
  protected function prepareData($row) {

    $data = [];
    foreach ($this->fields as $key => $name) {
      $data[$key] = $row->getDestinationProperty($key);
    }
    return $data;
  }

  /**
   * Executes the task with Mautic API.
   *
   * @param array $payload
   *   The payload containing data for executing the API call.
   */
  public function executeTask(array $payload): void {
    // Do nothing.
  }

  /**
   * Delete the remote and local file.
   */
  public function deleteSourceFile() : void {
    unlink(PublicStream::basePath() . '/' . basename($this->sourceFile));
    $this->sftpClient->openConnection($this->config->get('lcm_acs_xrm_sftp_username'), \Drupal::service('key.repository')->getKey($this->config->get('lcm_acs_xrm_api_secretkey'))->getKeyValue());
    $this->sftpClient->deleteFile($this->sourceFile);
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    $payload = $this->prepareData($row);
    $id = array_key_first($this->ids);
    $this->executeTask($payload);
    return [$id => substr(json_encode($payload), 0, 255)];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return $this->ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return $this->fields;
  }

  /**
   * {@inheritdoc}
   */
  public function preImport(MigrateImportEvent $event) {
    // Do nothing.
  }

  /**
   * {@inheritDoc}
   */
  public function postImport(MigrateImportEvent $event): void {
    // marci---
    // $this->deleteSourceFile();
  }

}

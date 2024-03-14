<?php

declare(strict_types = 1);

namespace Drupal\lcm_acs_xrm\Plugin\migrate\source;

use Drupal\lcm_acs_xrm\Service\SftpClientFactory;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StreamWrapper\PublicStream;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The 'acs_importsource' source plugin.
 *
 * @MigrateSource(
 *   id = "acs_importsource",
 *   source_module = "lcm_acs_xrm",
 * )
 */
final class ImportSource extends CSV implements ContainerFactoryPluginInterface {

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
   * Constructs the plugin instance.
   *
   * @param array $configuration
   *   The plugin configuration, including the 'id' key.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *   The migration object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\lcm_acs_xrm\Service\SftpClientFactory $sftp_client_factoty
   *   The sftp client factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, ConfigFactoryInterface $config_factory, SftpClientFactory $sftp_client_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->config = $config_factory->get('lcm_acs_xrm.settings');
    // $this->sftpClient = $sftp_client_factory->create($this->config->get('lcm_acs_xrm_sftp_hostname'), $this->config->get('lcm_acs_xrm_sftp_port'));
    // $this->sftpClient->openConnection($this->config->get('lcm_acs_xrm_sftp_username'), \Drupal::service('key.repository')->getKey($this->config->get('lcm_acs_xrm_api_secretkey'))->getKeyValue());
    // $localFile = $this->getFilePath();
    // if (!$this->sftpClient->downloadFile($this->configuration['path'], $localFile)) {
    //   throw new \InvalidArgumentException('You must declare the "path" to the source CSV file in your source settings.');
    // }
    // $this->configuration['path'] = $localFile;
  }

  /**
   * Creates an instance of the ImportSource.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\migrate\Plugin\MigrationInterface|null $migration
   *   The migration object, if available.
   *
   * @return \Drupal\ace_migrate\Plugin\migrate\process\ImportSource
   *   A new instance of the ImportSource.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('config.factory'),
      $container->get('lcm_acs_xrm.sftp_client_factory'),
    );
  }

  /**
   * Sets the 'rows_total' source property with the total number of rows.
   *
   * @param \Drupal\migrate\Row $row
   *   The row being prepared.
   *
   * @return bool
   *   TRUE if the row is prepared successfully, FALSE otherwise.
   *
   * @see \Drupal\migrate\Plugin\migrate\source\SourcePluginBase::prepareRow()
   */
  public function prepareRow(Row $row) {
    $row->setSourceProperty('rows_total', parent::count());
    return parent::prepareRow($row);
  }

  /**
   * Generates a file path for the file to be downloaded.
   *
   * @param string $subdirectory
   *   The subdirectory to be appended to the file path.
   *
   * @return string
   *   The generated file path.
   *
   * @throws \Drupal\migrate\MigrateException
   *   Thrown if the 'path' key is not present in the configuration.
   *
   * @see \Drupal\migrate\Plugin\migrate\source\SourcePluginBase::getFilePath()
   */
  public function getFilePath() {
    if (!isset($this->configuration['path'])) {
      throw new MigrateException('The configuration key "path" is required.');
    }
    return PublicStream::basePath() . '/' . basename($this->configuration['path']);
  }

}

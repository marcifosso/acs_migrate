<?php

declare(strict_types = 1);

namespace Drupal\lcm_acs_xrm\Plugin\migrate\destination;

use Drupal\migrate\Row;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The 'acs_importdestination_members' destination plugin.
 *
 * @MigrateDestination(id = "acs_importdestination_members")
 */
final class ImportDestinationMembers extends ImportDestination implements ContainerFactoryPluginInterface {

  /**
   * The current batch of payload.
   *
   * @var array
   */
  protected $payloadBatch = [];

  /**
   * Constructs the ImportDestinationMembersDelete plugin instance.
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
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    $payload = $this->prepareData($row);
    $this->payloadBatch[] = $payload;
    // Check if it's time to call executeTask.
    if (count($this->payloadBatch) >= $this->batchSize || $row->getSourceProperty('index') == $row->getSourceProperty('rows_total')) {
      $this->executeTask($this->payloadBatch);
      $this->payloadBatch = [];
    }
    $id = array_key_first($this->ids);
    return [$id => $row->getSourceProperty('MEMBER_NR_13')];
  }

  /**
   * Executes the task of creating members in Mautic.
   *
   * @param array $payload
   *   The payload containing data for creating the member.
   */
  public function executeTask(array $payload): void {
    \Drupal::logger('lcm_acs_xrm')->notice('API call- lo ' . count($payload));
    $this->mauticApi->makeApiRequest('/api/contacts/bulk/new', 'POST', $payload);
  }

}

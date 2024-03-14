<?php

declare(strict_types = 1);

namespace Drupal\lcm_acs_xrm\Plugin\migrate\destination;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * The 'acs_importdestination_customobjects' destination plugin.
 *
 * @MigrateDestination(id = "acs_importdestination_customobjects")
 */
final class ImportDestinationCustomObjects extends ImportDestination implements ContainerFactoryPluginInterface {

  /**
   * The MauticApi service.
   *
   * @var \Drupal\lcm_acs_xrm\Service\MauticApi
   */
  protected $mauticApi;

  /**
   * Custom object information retrieved from Mautic.
   *
   * @var array
   */
  protected $customObject = [];

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
   * @param \Drupal\lcm_acs_xrm\Service\MauticApi $mautic_api
   *   The Mautic API service.
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
   * Executes the task of creating custom objects in Mautic.
   *
   * @param array $payload
   *   The payload containing data for creating the custom object.
   */
  public function executeTask(array $payload): void {
    $key = strtolower(str_replace('_', '', array_key_first($this->ids)));
    $this->customObject['name'] = $payload[$key];
    foreach ($this->customObject['fieldValues'] as $ii => &$field) {
      if (array_key_exists($field['value'], $payload)) {
        $field['value'] = $payload[$field['value']];
      }
      else {
        unset($this->customObject['fieldValues'][$ii]);
      }
    }
    $this->mauticApi->makeApiRequest('/api/v2/custom_items', 'POST', $this->customObject);
  }

}

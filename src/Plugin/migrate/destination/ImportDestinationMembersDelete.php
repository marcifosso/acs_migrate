<?php

declare(strict_types = 1);

namespace Drupal\lcm_acs_xrm\Plugin\migrate\destination;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a destination plugin for deleting contacts using Mautic API.
 *
 * @MigrateDestination(
 *   id = "acs_importdestination_membersdelete",
 *   label = @Translation("Mautic Members Delete")
 * )
 */
final class ImportDestinationMembersDelete extends ImportDestination implements ContainerFactoryPluginInterface {

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
   * Executes the task of deleting members in Mautic.
   *
   * @param array $payload
   *   The payload containing data for deleting the member.
   */
  public function executeTask(array $payload): void {
    $response = $this->mauticApi->makeApiRequest('/api/contacts?search=' . $payload['email']);
    if ($response) {
      $data = json_decode($response);
      if (is_object($data) && $data->total == 1) {
        $contacts = (array) $data->contacts;
        foreach ($contacts as $contact) {
          if ($contact->fields->all->member_nr_13 == $payload['member_nr_13']) {
            $this->mauticApi->makeApiRequest('/api/contacts/' . $contact->id . '/delete', 'DELETE');
          }
        }
      }
    }
  }

}

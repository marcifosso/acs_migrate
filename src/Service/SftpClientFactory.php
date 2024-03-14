<?php

declare(strict_types = 1);

namespace Drupal\lcm_acs_xrm\Service;

/**
 * Factory class for creating instances of the SftpClient.
 */
class SftpClientFactory {

  /**
   * Creates a new instance of the SftpClient.
   *
   * @param string $hostname
   *   The hostname for the SFTP connection.
   * @param string $port
   *   The port for the SFTP connection.
   *
   * @return \Drupal\lcm_acs_xrm\Service\SftpClient
   *   A new instance of the SftpClient.
   */
  public static function create(string $hostname, string $port) {
    return new SftpClient($hostname, $port);
  }

}

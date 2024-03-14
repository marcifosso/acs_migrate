<?php

declare(strict_types = 1);

namespace Drupal\lcm_acs_xrm\Service;

use phpseclib3\Net\SFTP;

/**
 * Provides a service for SFTP operations.
 */
class SftpClient {

  /**
   * The SFTP object.
   *
   * @var \phpseclib3\Net\SFTP
   */
  private $sftp;

  /**
   * Constructs a new SftpClient object.
   *
   * @param string $hostname
   *   The hostname for the SFTP connection.
   * @param string $port
   *   The port for the SFTP connection.
   */
  public function __construct(string $hostname, string $port) {
    $this->sftp = new SFTP($hostname, $port);
  }

  /**
   * Opens a connection to the SFTP server.
   *
   * @param string $username
   *   The username for authentication.
   * @param string|null $password
   *   The optional password for authentication.
   *
   * @throws \Exception
   *   Thrown if authentication fails.
   */
  public function openConnection($username, $password = NULL) {
    if (isset($password) && !empty($password)) {
      return $this->loginWithPassword($username, $password);
    }
    return $this->loginWithAgent($username);
  }

  /**
   * Protected method to login using a username and password.
   *
   * @param string $username
   *   The username for authentication.
   * @param string $password
   *   The password for authentication.
   *
   * @throws \Exception
   *   Thrown if authentication fails.
   */
  protected function loginWithPassword($username, $password) {
    if (!$this->sftp->login($username, $password)) {
      throw new \Exception("Failed to authenticate with username ${username} and password ${password}.");
    }
  }

  /**
   * Protected method to login using an agent (public key).
   *
   * @param string $username
   *   The username for authentication.
   *
   * @throws \Exception
   *   Thrown if authentication fails.
   */
  protected function loginWithAgent($username) {
    if (!$this->sftp->login($username)) {
      throw new \Exception("Failed to authenticate with username $username and public key.");
    }
  }

  /**
   * Downloads a file from the SFTP server.
   *
   * @param string $remotePath
   *   The remote path of the file on the server.
   * @param string $localPath
   *   The local path to save the downloaded file.
   *
   * @return false|int
   *   The number of bytes written to the local file, or false on failure.
   *
   * @throws \Exception
   *   Thrown if the remote file cannot be opened.
   */
  public function downloadFile($remotePath, $localPath) {
    $contents = $this->sftp->get($remotePath);
    if ($contents === FALSE) {
      throw new \Exception("Could not open remote file: $remotePath.");
    }
    return file_put_contents($localPath, $contents);
  }

  /**
   * Deletes a file on the SFTP server.
   *
   * @param string $remotePath
   *   The remote path of the file on the server.
   *
   * @return bool
   *   TRUE if the file is successfully deleted, FALSE otherwise.
   */
  public function deleteFile(string $remotePath): bool {
    return $this->sftp->delete($remotePath);
  }

  /**
   * Checks if the SFTP client is connected to the server.
   *
   * @return bool
   *   TRUE if connected, FALSE otherwise.
   */
  public function isConnected() {
    return $this->sftp->isConnected();
  }

  /**
   * Closes the connection to the SFTP server.
   */
  public function closeConnection() {
    $this->sftp->disconnect();
  }

}

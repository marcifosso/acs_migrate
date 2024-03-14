<?php

declare(strict_types = 1);

namespace Drupal\lcm_acs_xrm\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Provides an acs_process_lowercase plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: acs_process_lowercase
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(id = "acs_process_lowercase")
 */
final class LowercaseProcess extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property): mixed {
    return strtolower($value);
  }

}

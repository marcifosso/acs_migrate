<?php

declare(strict_types = 1);

namespace Drupal\acs_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Provides an acs_process_capitalizedcase plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: acs_process_capitalizedcase
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(id = "acs_process_capitalizedcase")
 */
final class CapitalizedcaseProcess extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property): mixed {
    return ucwords($value);
  }

}

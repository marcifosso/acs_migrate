<?php

declare(strict_types = 1);

namespace Drupal\acs_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Provides an acs_process_dateformat plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: acs_process_dateformat
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(id = "acs_process_dateformat")
 */
final class DateFormatProcess extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property): mixed {
    $dateTime = new \DateTime(strtotime($value));
    return $dateTime->format('Y-m-d');
  }

}

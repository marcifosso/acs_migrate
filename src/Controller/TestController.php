<?php

declare(strict_types = 1);

namespace Drupal\lcm_acs_xrm\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Tests
 */
final class TestController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function __invoke(): array {

    $stringToHash = "https://migration.ddev.site";
    $hashedString = hash('md5', $stringToHash);
    $hashedString2 = password_hash($stringToHash, PASSWORD_BCRYPT);
    Echo "Original String: $stringToHash<br>";
    echo "Original String: $hashedString<br>";
    echo "Hashed String: $hashedString2";

    dpm([$hashedString]);

    // $mauticApi = \Drupal::service('lcm_acs_xrm.mautic_api');
    // $response2 = $mauticApi->makeApiRequest('/api/contacts');
    // $decoded = json_decode($response2, TRUE);
    // print_r(count($decoded));
  
    // dpm($decoded['contacts'], 'test');
    // $emails = [];
    // foreach($decoded['contacts'] as $id => $contact){
    //   $emails[$contact['fields']['all']['email']] = $contact['fields']['all']['member_nr_13'];
    // }
    // ksort($emails);
    // dpm($emails);

    // foreach($decoded['contacts'] as $id => $contact){
    //   $mauticApi->makeApiRequest('/api/contacts/' . $id . '/delete', 'DELETE');
    // }.
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('helooo!'),
    ];

    return $build;
  }

}

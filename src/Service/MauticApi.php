<?php

namespace Drupal\lcm_acs_xrm\Service;

use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class MauticApi.
 *
 * Service class for interacting with the Mautic API.
 */
class MauticApi {

  /**
   * The configuration factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $config;

  /**
   * The Drupal HTTP client factory.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  private $httpClient;

  /**
   * A token to avoid mixing up the acces stoken
   * 
   * @var string
   */
  private $token;

  /**
   * MauticApi constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory for LCM ACS-XRM settings.
   * @param \Drupal\Core\Http\ClientFactory $httpClientFactory
   *   The Drupal HTTP client factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ClientFactory $httpClientFactory) {
    $this->config = $configFactory;
    if (!$this->config->get('lcm_acs_xrm.settings')->get('lcm_acs_xrm_api_baseurl')) {
      throw new \InvalidArgumentException("You must ensure the Mautic API is configured properly.");
    }
    $this->httpClient = $httpClientFactory->fromOptions(['base_uri' => $this->config->get('lcm_acs_xrm.settings')->get('lcm_acs_xrm_api_baseurl')]);
    $this->token = md5($this->config->get('lcm_acs_xrm.settings')->get('lcm_acs_xrm_api_baseurl'));
  }

  /**
   * Makes an API request to Mautic.
   *
   * @param string $endpoint
   *   The API endpoint.
   * @param string $method
   *   The HTTP method (default: 'GET').
   * @param array $data
   *   The data to send with the request.
   *
   * @return string
   *   The API response.
   */
  public function makeApiRequest($endpoint, $method = 'GET', $data = []): string {
    $accessToken = $this->getAccessToken();
    $options = [
      'headers' => [
        'Authorization' => 'Bearer ' . $accessToken,
      ],
      'json' => $data,
    ];

    try {
      $response = $this->httpClient->request($method, $endpoint, $options);
      return $response->getBody()->getContents();
    }
    catch (\Exception $e) {
      \Drupal::logger('lcm_acs_xrm')->error('Error making API request: ' . $e->getMessage());
      // Handle error appropriately.
    }
    return FALSE;
  }

  /**
   * Get information about a custom object by its alias.
   *
   * @param string $objectAlias
   *   The alias of the custom object.
   *
   * @return array Associative array containing information about the custom object.
   */
  public function getCustomObject($objectAlias) {
    $customObject = [];
    $response = $this->makeApiRequest('/api/v2/custom_objects');
    $objects = json_decode($response, TRUE);

    foreach ($objects['hydra:member'] as $object) {
      if ($object['alias'] == $objectAlias) {
        $customObject['name'] = "";
        $customObject['customObject'] = $object['@id'];
        foreach ($object['customFields'] as $field) {
          $customObject['fieldValues'][] = [
            "id" => $field['@id'],
            "value" => $field['alias'],
          ];
        }
        break;
      }
    }

    return $customObject;
  }

  /**
   * Gets the access token for Mautic API.
   *
   * @return string
   *   The access token.
   */
  protected function getAccessToken() {
    $accessToken = $this->config->get('lcm_acs_xrm.settings')->get('acs_access_token_'.$this->token);
    $expirationTime = $this->config->get('lcm_acs_xrm.settings')->get('acs_access_token_expiration_'.$this->token);
    if ($accessToken && $expirationTime > REQUEST_TIME) {
      return $accessToken;
    }
    // If the access token is not available or expired, request a new one.
    $tokenEndpoint = '/oauth/v2/token';
    $options = [
      'headers' => [
        'Content-Type' => 'application/x-www-form-urlencoded',
      ],
      'form_params' => [
        'grant_type' => 'client_credentials',
        'client_id' => $this->config->get('lcm_acs_xrm.settings')->get('lcm_acs_xrm_api_clientid'),
        'client_secret' => \Drupal::service('key.repository')->getKey($this->config->get('lcm_acs_xrm.settings')->get('lcm_acs_xrm_api_secretkey'))->getKeyValue(),
      ],
    ];

    try {
      $response = $this->httpClient->post($tokenEndpoint, $options);
      $data = json_decode($response->getBody()->getContents(), TRUE);
      $config = $this->config->getEditable('lcm_acs_xrm.settings');
      $config->set('acs_access_token_'.$this->token, $data['access_token']);
      $config->set('acs_access_token_expiration_'.$this->token, REQUEST_TIME + $data['expires_in']);
      $config->save();

      return $data['access_token'];
    }
    catch (\Exception $e) {
      \Drupal::logger('lcm_acs_xrm')->error('Error obtaining access token: ' . $e->getMessage());
    }
  }

}

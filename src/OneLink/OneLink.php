<?php
/**
 * @file
 */

namespace StevenWichers\OneLink;

/**
 * A OneLink helper class to abstract some of the translation API away.
 *
 * Major documentation around the API can be found in the docs folder.
 *
 * @author Steven Wichers
 * @license http://www.apache.org/licenses/LICENSE-2.0.html Apache License v2
 */
class OneLink {

  /**
   * Which version of the API we are using.
   */
  const VERSION = 2.0;

  protected $default_service_type = 'tx';
  protected $default_language = 'en';
  protected $host = null;
  protected $pass = null;
  protected $user = null;
  protected $request = null;

  /**
   * Allowable service types.
   *
   * See the docs folder for more information.
   *
   * @var array
   */
  private $service_types = array(
    'tx',
    'smt',
    'wmt',
    'tx+smt',
    'tx+wmt',
    'parse',
  );

  /**
   * Allowable mime types.
   *
   * @var array
   */
  private $mime_types = array(
    'text/html',
    'text/xml',
    'text/javascript',
    'text/json',
    'text/plain',
    'text/segment',
    'application/json',
  );

  /**
   * Initialize the OneLink helper.
   *
   * @param string $username
   *  The API username.
   *
   * @param string $password
   *   The API password.
   *
   * @param string $host
   *   The API host.
   *
   * @param array $options
   *   Any additional options (supported: otx_service, language).
   */
  public function __construct($username, $password, $host, array $options = array()) {

    $this->request = new \Requests_Session(null, array('Content-type' => 'application/x-www-form-urlencoded'));

    $this->user = $username;
    $this->pass = $password;
    $this->host = $host;

    if (!empty($options['otx_service'])) {

      $this->setServiceType($options['otx_service']);
    }

    if (!empty($options['language'])) {

      $this->default_language = $options['language'];
    }
  }

  /**
   * Get the API service URL.
   *
   * @param string $lang
   *   The language we are translating for.
   *
   * @return string
   *   The API service URL.
   */
  protected function getServiceURL($lang) {

    return sprintf('https://%s-%s.onelink-translations.com/OneLinkOTX/', $lang, $this->host);
  }

  /**
   * Determine if the given service type is valid.
   *
   * @param string $type
   *   The service type to check.
   *
   * @return boolean
   *   TRUE if the type is valid, FALSE otherwise.
   */
  protected function isValidServiceType($type) {

    return in_array($type, $this->service_types);
  }

  /**
   * Determine if the given mime type is valid.
   *
   * @param string $type
   *   The mime type to check.
   *
   * @return boolean
   *   TRUE if the type is valid, FALSE otherwise.
   */
  protected function isValidMimeType($type) {

    return in_array($type, $this->mime_types);
  }

  /**
   * Parent translate function for use by other methods.
   *
   * @param string $text
   *   The text to translate.
   *
   * @param string $lang
   *   The language to translate the text in to. Defaults to the current
   *   language.
   *
   * @param array $args
   *   Any API argument overrides.
   *
   * @return OneLinkResult
   *   A OneLinkResult object.
   */
  protected function translate($text, $lang = null, array $args = array()) {

    if (empty($lang)) {

      $lang = $this->default_language;
    }

    if (empty($lang)) {

      throw new \Exception('No language specified.');
    }

    $default_args = array(
      'otx_account' => sprintf('%s,%s', $this->user, $this->pass),
      'otx_mimetype' => 'text/html',
      'otx_service' => $this->default_service_type,
      'otx_content' => $text,
    );

    $args = array_merge($default_args, $args);

    if (!$this->isValidMimeType($args['otx_mimetype'])) {

      throw new Exception('Invalid mimetype specified.');
    }
    elseif (!$this->isValidServiceType($args['otx_service'])) {

      throw new Exception('Invalid service type specified.');
    }

    return $this->makeRequest($this->getServiceURL($lang), $args);
  }

  /**
   * Issue a OneLink API request.
   *
   * @param string $url
   *   The API URL to use.
   *
   * @param array $args
   *   An array of arguments to send with the API request.
   *
   * @return OneLinkResult
   *   A OneLinkResult object.
   */
  public function makeRequest($url, array $args) {

    $resp = $this->request->post($url, array(), $args);

    return new OneLinkResult($resp);
  }

  /**
   * Translate text.
   *
   * @param string $text
   *   The text to translate.
   *
   * @param string $lang
   *   The language to translate the text in to. Defaults to the current
   *   language.
   *
   * @return OneLinkResult
   *   A OneLinkResult object.
   */
  public function translateText($text, $lang = null) {

    // OTX requires text to be surrounded by markup, or else it will just
    // ignore what you give it.
    return $this->translateHtml('<p>' . $text . '</p>', $lang);
  }

  /**
   * Translate HTML with text.
   *
   * @param string $html
   *   The HTML to translate.
   *
   * @param string $lang
   *   The language to translate the text in to. Defaults to the current
   *   language.
   *
   * @return OneLinkResult
   *   A OneLinkResult object.
   */
  public function translateHtml($html, $lang = null) {

    $args = array(
      'otx_mimetype' => 'text/html',
    );

    return $this->translate($html, $lang, $args);
  }

  /**
   * Translate segments of text.
   *
   * @param array $segments
   *   An array of text segments to translate.
   *
   * @param string $lang
   *   The language to translate the text in to. Defaults to the current
   *   language.
   *
   * @return OneLinkResult
   *   A OneLinkResult object.
   */
  public function translateSegments(array $segments, $lang = null) {

    // According to TDC, this is the expected way to translate multiple
    // segments of text at this time.
    foreach ($segments as &$segment) {

      $segment = '<div>' . $segment . '</div>';
    }
    unset($segment);

    $text = implode('', $segments);

    return $this->translateText($text, $lang);
  }

  /**
   * Sets the current service type.
   *
   * @param string $new_type
   *   The service type to set.
   *
   * @return string
   *   The old service type.
   */
  public function setServiceType($new_type) {

    if (!$this->isValidServiceType($new_type)) {

      throw new \Exception('Invalid service type.');
    }

    $old = $this->default_service_type;
    $this->default_service_type = $new_type;

    return $old;
  }
}

<?php
/**
 * @file
 */

namespace StevenWichers\OneLink;

/**
 * A result wrapper for OneLink API request responses.
 *
 * @author Steven Wichers
 * @license http://www.apache.org/licenses/LICENSE-2.0.html Apache License v2
 */
class OneLinkResult {

  protected $result = null;

  /**
   * Return the result as text.
   *
   * @return string
   *   The result body.
   */
  public function __toString() {

    return $this->result->body;
  }

  /**
   * Initialize the result.
   *
   * @param \Requests_Response $result
   *   A Request_Response object from a OneLink API response.
   */
  public function __construct(\Requests_Response $result) {

    $this->result = $result;
  }

  /**
   * Determine if this result is translated.
   *
   * @param integer $amount
   *   The amount for text to be considered fully translated. If the translation
   *   percentage is greater than this amount than it is considered translated.
   *   Defaults to 97%.
   *
   * @return boolean
   *   TRUE if the text was translated, FALSE otherwise.
   */
  public function isTranslated($amount = 97) {

    return $this->getTranslationPercent() >= $amount;
  }

  /**
   * Get the percentage complete of translation.
   *
   * This is driven by translated segments, and not an actual word-for-word
   * percentage calculation.
   *
   * @return integer
   *   The percentage of text that was translated.
   */
  public function getTranslationPercent() {

    return !empty($this->result->headers['x-onelinktxpercent']) ?
      $this->result->headers['x-onelinktxpercent'] :
      0;
  }

  /**
   * Determine if this request was a successful request.
   *
   * @return boolean
   *   TRUE of the request was successful, FALSE otherwise.
   */
  public function isSuccessful() {

    return $this->result->success && 200 == $this->result->status_code;
  }

  /**
   * Determine if the request was a failure.
   *
   * @return boolean
   *   TRUE if the request was not successful, FALSE otherwise.
   */
  public function isFailure() {

    return !$this->isSuccessful();
  }

  /**
   * Get the request status code.
   *
   * @return integer
   *   The HTTP status code of the request.
   */
  public function getStatusCode() {

    return $this->result->status_code;
  }

  /**
   * Get the request's data.
   *
   * @return string|bool
   *   The request data if available, FALSE otherwise.
   */
  public function getData() {

    return empty($this->result->body) ?
      false :
      $this->result->body;
  }

  /**
   * Get the raw API result.
   *
   * @return array
   *   The OneLink API result as passed in originally.
   */
  public function getResult() {

    return $this->result;
  }
}

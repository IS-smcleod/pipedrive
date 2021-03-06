<?php
namespace Pipedrive;

/**
 * Client for interacting with Pipedrive API v1
 */
class Client {

  protected $token, $url;

  public function __construct($token, $url = "https://api.pipedrive.com/v1/") {
    $this->token = $token;
    $this->url   = $url;
  }

  /**
   * Marge the specific fields from the source array into the target array.
   * @param  mixed        $target base array new entries will be added to
   * @param  mixed        $source source array to get new entries from
   * @param  string|array $fields keys to merge
   * @return mixed
   */
  protected function mergeOptions($target, $source, $fields) {
    if (!is_array($fields)) { $fields = explode(",", $fields); }
    foreach ($fields as $field) {
      if (isset($source[$field])) { $target[$field] = $source[$field]; }
    }
    return $target;
  }

  /**
   * Error handler for API responses.
   * @param  \Unirest\Response $response response to check
   */
  protected function errorHandler(\Unirest\Response $response) {
    if ($response->code == 429) {
      throw new \Exception("API rate limit exceeded, retry after " . $response->headers["Retry-After"] . " seconds");
    }
  }

  /**
   * Request wrapper
   * @param  string   $method  HTTP method to make the request with
   * @param  string   $url     URL for the request
   * @param  mixed    $body    content for the request
   * @param  mixed    $headers headers for the request
   * @return stdClass
   */
  protected function request($method, $url, $body = array(), $headers = array()) {
    if ($method === "POST") {
      // Ensure the token is part of the URL
      $url .= strpos($url, '?') !== false ? '&' : '?';
      $url .= "api_token=" . $this->token;
      // Encode the body
      $body = json_encode($body);
    } else {
      // Ensure the token is part of the body
      $body = array_merge($body, array("api_token" => $this->token));
    }

    // Set the default data type
    $headers = array_merge(array(
      "Content-Type" => "application/json",
      "Accept" => "application/json"
    ), $headers);

    $response = \Unirest\Request::send($method, $url, $body, $headers);

    // Save information for debugging
    $this->lastRequest = array(
      "method"  => $method,
      "url"     => $url,
      "headers" => $headers,
      "body"    => $body,
      "info"    => \Unirest\Request::getInfo()
    );

    // Check for common errors
    $this->errorHandler($response);

    // Return the response
    return $response->body;
  }

  /**
   * Get all of the deals.
   * Possible options:
   * integer filter_id    ID of the filter to use
   * integer start        number of items to skip
   * integer limit        maximum number of items in response
   * string  sort         comma separated list of fields to and how they should be sorted
   * boolean owned_by_you only include deals owned by the user
   * @param  mixed $options options for the request
   * @return mixed          the resulting deals
   */
  public function getDeals($options = array()) {
    $body = self::mergeOptions(array(), $options, "filter_id,start,limit,sort,owned_by_you");
    return $this->request('GET', $this->url . "deals", $body);
  }

  /**
   * Get a deal.
   * @param  interger $id id of the target deal
   * @return mixed        the deal or
   */
  public function getDeal($id) {
    return $this->request('GET', $this->url . "deals/" . $id);
  }

  /**
   * Create a new Deal.
   * Possible fields:
   * string  title       title of the deal. Required
   * string  value       value of the deal. default: 0
   * string  currency    ISO 3166 alpha 3, three letter country code. default: user's currency
   * integer user_id     id of the user who will be marked as the owner of this deal. default: user's id
   * integer person_id   id of the user this deal will be associated with.
   * integer org_id      id of the organization this deal will be associated with.
   * integer stage_id    id of the stage this deal will be placed into. default: first stage of the default pipeline
   * string  status      open, won, lost, deleted. default: open
   * string  lost_reason message about why the deal was lost.
   * string  add_time    Creation date & time in UTC. Admin only. format: YYYY-MM-DD HH:MM:SS
   * integer visible_to  1 = private, 3 = shared. default: user's default for type
   *
   * @param  mixed   $fields      optional fields to specify
   * @return mixed
   */
  public function createDeal($fields = array()) {
    $body = $fields;
    if ($body["title"] === NULL || $body["title"] === "") {
      throw new \Exception("A TITLE is required");
    } else if (isset($body["status"]) && !preg_match("/^(?:open|won|lost|deleted)$/", $body["status"])) {
      throw new \Exception("'" . $body["status"] . "' is not a valid status value. Valid values are: open, won, lost, deleted");
    } else if (isset($body["visible_to"]) && $body["visible_to"] !== 1 && $body["visible_to"] !== 3) {
      throw new \Exception("'" . $body["visible_to"] . "' is not a valid visible_to value. Valid values are: 1, 3");
    }
    return $this->request('POST', $this->url . "deals", $body);
  }

  /**
   * Get all of the deal fields.
   * @return mixed
   */
  public function getDealFields() {
    return $this->request('GET', $this->url . "dealFields");
  }

  /**
   * Get a deal field.
   * @param  interger $id id of the target deal field
   * @return mixed        the deal or
   */
  public function getDealField($id) {
    return $this->request('GET', $this->url . "dealFields/" . $id);
  }

}

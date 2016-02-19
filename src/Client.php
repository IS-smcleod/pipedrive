<?php
namespace Pipedrive;

/**
 * Client for interacting with Pipedrive API v1
 */
class Client {

  protected $token, $url;

  public function __construct($token, $url = "https://api.pipedrive.com/v1/") {
    $this->token = $token;
    $this->url = $url;
  }

  /**
   * Get a copy of the common headers.
   * @return array
   */
  protected function getHeaders() {
    return array(
      "Content-Type" => "application/json",
      "Accept" => "application/json"
    );
  }

  /**
   * Get a copy of the common body.
   * @return array
   */
  protected function getBody() {
    return array("api_token" => $this->token);
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
  public function getDeals($options) {
    $body = self::mergeOptions($this->getBody(), $options, "filter_id,start,limit,sort,owned_by_you");
    $response = \Unirest\Request::get($this->url . "deals", $this->getHeaders(), json_encode($body));
    self::errorHandler($response);
    return $response->body;
  }

  /**
   * Get a deal.
   * @param  interger $id id of the target deal
   * @return mixed        the deal or
   */
  public function getDeal($id) {
    $response = \Unirest\Request::get($this->url . "deal/" . $id, $this->getHeaders(), json_encode($this->getBody()));
    self::errorHandler($response);
    return $response->body;
  }

  /**
   * Create a new Deal.
   * Possible fields:
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
   * @param  string  $title       title of the deal.
   * @param  mixed   $fields      optional fields to specify
   * @return mixed
   */
  public function createDeal($title, $fields) {
    if (!isset($title)) {
      throw new \Exception("A TITLE is required");
    } else if (isset($status) && !preg_match("/^(?:open|won|lost|deleted)$/", $status)) {
      throw new \Exception("'" . $status . "' is not a valid status value. Valid values are: open, won, lost, deleted");
    } else if (isset($visible_to) && $visible_to !== 1 && $visible_to !== 3) {
      throw new \Exception("'" . $visible_to . "' is not a valid visible_to value. Valid values are: 1, 3");
    }
    $body = array_merge($this->getBody(), $fields);
    $body["title"] = $title;
    $response = \Unirest\Request::post($this->url . "deal/" . $id, $this->getHeaders(), json_encode($body));
    self::errorHandler($response);
    return $response->body;
  }

  /**
   * Get all of the deal fields.
   * @return mixed
   */
  public function getDealFields() {
    $response = \Unirest\Request::get($this->url . "dealFields", $this->getHeaders(), json_encode($this->getBody()));
    self::errorHandler($response);
    return $response->body;
  }

  /**
   * Get a deal field.
   * @param  interger $id id of the target deal field
   * @return mixed        the deal or
   */
  public function getDealField($id) {
    $response = \Unirest\Request::get($this->url . "dealFields/" . $id, $this->getHeaders(), json_encode($this->getBody()));
    self::errorHandler($response);
    return $response->body;
  }

}

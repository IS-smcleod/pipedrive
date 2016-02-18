<?php
namespace Pipedrive;

class Pipedrive {

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
   * @param  integer          $filter_id    ID of the filter to use
   * @param  integer          $start        number of items to skip
   * @param  integer          $limit        maximum number of items in response
   * @param  string           $sort         comma separated list of fields to and how they should be sorted
   * @param  boolean          $owned_by_you only include deals owned by the user
   * @return mixed                          the resulting deals
   */
  public function getDeals($filter_id, $start, $limit, $sort, $owned_by_you) {
    $body = array();
    if (isset($filter_id   )) { $body["filter_id"]    = $filter_id;    }
    if (isset($start       )) { $body["start"]        = $start;        }
    if (isset($limit       )) { $body["limit"]        = $limit;        }
    if (isset($sort        )) { $body["sort"]         = $sort;         }
    if (isset($owned_by_you)) { $body["owned_by_you"] = $owned_by_you; }
    $response = \Unirest\Request::get($this->url . "deals", getHeaders(), json_encode($body));
    errorHandler($response);
    return $response->body;
  }

  /**
   * Get a deal.
   * @param  interger $id id of the target deal
   * @return mixed        the deal or
   */
  public function getDeal($id) {
    if (!isset($id)) { throw new \Exception("An ID is required"); }
    $response = \Unirest\Request::get($this->url . "deal/" . $id, getHeaders());
    errorHandler($response);
    return $response->body;
  }

  /**
   * Create a
   * @param  string  $title       title of the deal.
   * @param  string  $value       value of the deal. default: 0
   * @param  string  $currency    ISO 3166 alpha 3, three letter country code. default: user's currency
   * @param  integer $user_id     id of the user who will be marked as the owner of this deal. default: user's id
   * @param  integer $person_id   id of the user this deal will be associated with.
   * @param  integer $org_id      id of the organization this deal will be associated with.
   * @param  integer $stage_id    id of the stage this deal will be placed into. default: first stage of the default pipeline
   * @param  string  $status      open, won, lost, deleted. default: open
   * @param  string  $lost_reason message about why the deal was lost.
   * @param  string  $add_time    Creation date & time in UTC. Admin only. format: YYYY-MM-DD HH:MM:SS
   * @param  integer $visible_to  1 = private, 3 = shared. default: user's default for type
   * @return mixed
   */
  public function createDeal($title, $value, $currency, $user_id, $person_id, $org_id, $stage_id, $status, $lost_reason, $add_time, $visible_to, $fields) {
    if (!isset($title)) {
      throw new \Exception("A TITLE is required");
    } else if (isset($status) && !preg_match("/^(?:open|won|lost|deleted)$/", $status)) {
      throw new \Exception("'" . $status . "' is not a valid status value. Valid values are: open, won, lost, deleted");
    } else if (isset($visible_to) && $visible_to !== 1 && $visible_to !== 3) {
      throw new \Exception("'" . $visible_to . "' is not a valid visible_to value. Valid values are: 1, 3");
    }
    $body = array();
    if (isset($fields)) { $body = array_merge($body, $fields); }
    $body["title"] = $title;
    if (isset($value      )) { $body["value"]       = $value;       }
    if (isset($currency   )) { $body["currency"]    = $currency;    }
    if (isset($user_id    )) { $body["user_id"]     = $user_id;     }
    if (isset($person_id  )) { $body["person_id"]   = $person_id;   }
    if (isset($org_id     )) { $body["org_id"]      = $org_id;      }
    if (isset($stage_id   )) { $body["stage_id"]    = $stage_id;    }
    if (isset($status     )) { $body["status"]      = $status;      }
    if (isset($lost_reason)) { $body["lost_reason"] = $lost_reason; }
    if (isset($add_time   )) { $body["add_time"]    = $add_time;    }
    if (isset($visible_to )) { $body["visible_to"]  = $visible_to;  }
    $response = \Unirest\Request::post($this->url . "deal/" . $id, getHeaders(), json_encode($body));
    errorHandler($response);
    return $response->body;
  }

  /**
   * Get all of the deal fields.
   * @return mixed
   */
  public function getDealFields() {
    $response = \Unirest\Request::get($this->url . "dealFields", getHeaders());
    errorHandler($response);
    return $response->body;
  }

  /**
   * Get a deal field.
   * @param  interger $id id of the target deal field
   * @return mixed        the deal or
   */
  public function getDealField($id) {
    if (!isset($id)) { throw new \Exception("An ID is required"); }
    $response = \Unirest\Request::get($this->url . "dealFields/" . $id, getHeaders());
    errorHandler($response);
    return $response->body;
  }

}

<?php

namespace App\Libraries\Api\Strapi;

class BaseConfig
{
  /**
   * Base Api Url
   *
   * @var string
   */
  public $apiUrl;

  /**
   * Api key Strapi
   *
   * @var string
   */
  public $apiKey;

  public function __construct(string $apiUrl,string $apiKey)
  {
    $this->apiUrl = $apiUrl;
    $this->apiKey = $apiKey;
  }

  public function __get($key)
  {
    if (property_exists($this, $key)) {
      return $this->{$key};
    } else {
      throw new \Exception("$key doesn't exist");
    }
  }

  public function __set($key, $value)
  {
    throw new \Exception("Cannot set $key");
  }
}
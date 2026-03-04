<?php

namespace App\Http\Exceptions\Api;

class ErrorException extends \Exception
{
  /**
   * @var string
   */
  protected $message;

  /**
   * @param string $message
   * 
   * @return void
   */
  public function __construct(string $message)
  {
    $this->message = $message;
  }

}
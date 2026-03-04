<?php

namespace App\Http\Exceptions\Api;

use App\Entities\Headers;

abstract class Exception extends \RuntimeException
{
  /**
   * @var string
   */
  protected $logMessage;

  /**
   * @var int
   */
  protected $statusCode;

  /**
   * @var string
   */
  protected $message;

  /**
   * @var array
   */
  protected $headers;

  /**
   * @param int $statusCode
   * @param string $message
   * @param array $headers
   * @param \Throwable $previous
   * @param int $code
   * 
   * @return void
   */
  public function __construct(
    int $statusCode,
    string $message,
    array $headers = [],
    \Throwable $previous = null,
    ?int $code = 0
  ) {
    $request = request();

    $this->logMessage = $request->method() . ' ' . $request->url() . ' ' . $request->header(Headers::X_REQUEST_ID);
    $this->statusCode = $statusCode;
    $this->message = $message;
    $this->headers = $headers;

    parent::__construct($message, $code, $previous);
  }

  /**
   * Get the default context variables for logging.
   * 
   * @return array
   */
  protected function context()
  {
    $context = [
      'message' => $this->message
    ];

    return array_filter($context);
  }

  /**
   * @return int
   */
  public function getStatusCode()
  {
    return $this->statusCode;
  }

  /**
   * @return array
   */
  public function getHeaders()
  {
    return $this->headers;
  }

  /**
   * Render an exception into an HTTP response.
   * 
   * @param \Illuminate\Http\Request $request
   * 
   * @return \Illuminate\Http\Response
   */
  abstract public function render($request);

  /**
   * @return \Illuminate\Http\JsonResponse
   */
  protected function convertToJsonResponse()
  {
    $data = [
      'code' => $this->getStatusCode(),
      'message' => $this->getMessage()
    ];

    return response()->json(
      $data,
      $this->getStatusCode(),
      $this->getHeaders(),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    );
  }
}
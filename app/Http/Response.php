<?php

namespace App\Http;

use App\Controller\Error\ErrorController;
use App\Utils\View;

class Response
{
  private $httpCode = 200;
  private $headers = [];
  private $contentType = 'text/html';
  private $content;

  public function __construct($httpCode, $content, $contentType = 'text/html', $contentDisposition = [])
  {
    $this->httpCode = $httpCode;
    $this->content = $content;
    $this->setContentType($contentType);
    $this->addHeader('Access-Control-Allow-Origin', '*');
    $this->addHeader('Access-Control-Allow-Headers', '*');
    $this->addHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS');

    // Content-Disposition: attachment
    if(!empty($contentDisposition)){
      foreach($contentDisposition as $key => $value){
        $this->addHeader($key, $value);
      }
    }
  }

  public function setContentType($contentType)
  {
    $this->contentType = $contentType;
    $this->addHeader('Content-Type', $contentType);
  }

  public function addHeader($key, $value)
  {
    $this->headers[$key] = $value;
  }

  private function sendHeaders()
  {
    http_response_code($this->httpCode);
    foreach ($this->headers as $key => $value) {
      if($key !== "filename")
        header($key . ': ' . $value);
      else{
        header($key . '="' . $value.'"');
      }
    }
  }

  private function sendContent()
  {
    switch (true) {
      case $this->httpCode >= 400 && $this->httpCode < 600:
        echo ErrorController::getError($this->httpCode, $this->content);
        break;
      default:
        echo $this->content;
        break;
    }
  }

  public function sendResponse()
  {
    $this->sendHeaders();
    switch ($this->contentType) {
      case 'text/html';
        $this->sendContent($this->content);
        exit;
      case 'application/json';
        echo json_encode($this->content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        exit;
      case 'application/pdf':
        echo $this->content;
        exit;
      case 'application/zip':
        readfile($this->content);
        unlink($this->content);
        exit;
    }
  }
}

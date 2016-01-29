<?php
class ErrorController extends Yaf_Controller_Abstract {
  /** 
   * you can also call to Yaf_Request_Abstract::getException to get the 
   * un-caught exception.
  */
  public function errorAction($exception) {
    /* error occurs */
    switch ($exception->getCode()) {
      case YAF_ERR_NOTFOUND_MODULE:
      case YAF_ERR_NOTFOUND_CONTROLLER:
      case YAF_ERR_NOTFOUND_ACTION:
      case YAF_ERR_NOTFOUND_VIEW:
          echo 404, ":", $exception->getMessage();
          break;
      default:
        $message = $exception->getMessage();
        echo 0, ":", $exception->getMessage();
        break;
    }
  } 
}
?>
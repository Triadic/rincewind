<?php

/**
 * The DefaultDispatcher
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dispatcher
 */
/**
 * Include the interface
 */
require_interface('Dispatcher');

/**
 * The DefaultDispatcher works with the controllerFactory.
 * 
 * It tries to instantiate the appropriate controller, and to call the right action on it.
 * If it works, the dispatcher is very happy about it.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dispatcher
 */
class DefaultDispatcher implements Dispatcher {

  /**
   * ControllerFactory
   * 
   * @var type 
   */
  private $controllerFactory;
  /**
   * @var string
   */
  private $defaultControllerName;
  /**
   * @var Sanitizer
   */
  private $actionSanitizer;
  /**
   * @var UtilsFactory
   */
  private $utils;

  /**
   * @param ControllerFactory $controllerFactory 
   * @param string $defaultControllerName
   */
  public function __construct($controllerFactory, Sanitizer $actionSanitizer, UtilsFactory $utils, $defaultControllerName = 'Home') {
    $this->controllerFactory = $controllerFactory;
    $this->defaultControllerName = $defaultControllerName;
    $this->actionSanitizer = $actionSanitizer;
    $this->utils = $utils;
  }

  /**
   * Parses the url, and dispatches to the appropriate controller.
   * @param bool $skipControllerInitialization
   */
  public function dispatch($skipControllerInitialization = false) {
    try {

      $controllerName = isset($_GET['controller']) ? trim($_GET['controller']) : $this->defaultControllerName;

      $controllerName = ucfirst(preg_replace('/\-([a-z])/e', 'strtoupper("$1")', $controllerName));

      try {
        $controller = $this->controllerFactory->get($controllerName);
      }
      catch (ControllerFactoryException $e) {
        $controller = $this->controllerFactory->get($this->defaultControllerName);
      }

      $errorDuringRender = null;
      $errorCode = null;
      $controller->initData();

      try {
        try {
          // Try to dispatch to the actual action.
          $actionParameters = explode('/', isset($_GET['action']) ? $_GET['action'] : 'index');

          $action = $actionParameters[0];
          array_shift($actionParameters);

          if ($action{0} === '_') {
            throw new ErrorCode(ErrorCode::NOT_FOUND, 'Tried to access action with underscore.');
          }

          $action = $this->actionSanitizer->sanitize($action);

          try {
            // Check if the action is valid
            $reflectionClass = new ReflectionClass($controller);

            $actionMethod = $reflectionClass->getMethod($action);

            if ($action !== 'index' && (method_exists('Controller', $action) || ! $actionMethod->isPublic() || ($actionMethod->class !== get_class($controller)))) throw new DispatcherException();
          }
          catch (DispatcherException $e) {
            throw new ErrorCode(ErrorCode::NOT_FOUND, 'Tried to access invalid action.');
          }

          $controller->setAction($action);

          $parameters = array();
          $stringParameters = array();

          $i = 0;
          foreach ($actionMethod->getParameters() as $parameter) {
            $actionParameter = isset($actionParameters[$i]) ? $actionParameters[$i] : null;

            if ($actionParameter === null) {
              if ( ! $parameter->isDefaultValueAvailable()) {
                throw new ErrorCode(ErrorCode::BAD_REQUEST, 'Not all parameters supplied.');
              }
              // Well: there is no more additional query, and apparently the rest of the parameters are optional, so continue.
              continue;
            }
            if ($parameterTypeClass = $parameter->getClass()) {
              if ( ! $parameterTypeClass->isSubclassOf('RW_Type')) {
                throw new ErrorCode(ErrorCode::BAD_REQUEST, 'Invalid parameter type.');
              }
              $parameterTypeClassName = $parameterTypeClass->getName();
              $parameters[] = new $parameterTypeClassName($actionParameter);
            }
            else {
              $parameters[] = $actionParameter;
            }
            $stringParameters[] = $actionParameter;
            $i ++;
          }
          $controller->setActionParameters($stringParameters);

          if ( ! $skipControllerInitialization) $controller->initialize();

          // This actually calls the apropriate action.
          call_user_func_array(array($controller, $action), $parameters);

          $controller->extendData();

          try {
            $controller->render(true);
          }
          catch (Exception $e) {
            throw new ErrorCode(ErrorCode::INTERNAL_SERVER_ERROR, 'Error during render.');
          }
        }
        catch (ErrorMessageException $e) {
          $errorDuringRender = true;
          $this->utils->message()->addErrorMessage($e->getMessage());
        }
        catch (Exception $e) {
          $additionalInfo = array();
          $additionalInfo['controllerName'] = $controllerName;
          $additionalInfo['action'] = $action;
          $additionalInfo['exceptionThrown'] = get_class($e);
          $additionalInfo['error'] = $e->getMessage();
          Log::warning($e->getMessage(), 'Dispatcher', $additionalInfo);
          throw new ErrorCode(ErrorCode::INTERNAL_SERVER_ERROR);
        }
      }
      catch(ErrorCode $e) {
        // All other exceptions have already been caught.
        $errorDuringRender = true;
        $errorCode = $e->getCode();
        $e->writeHttpHeader();
      }

      if ($errorDuringRender) {
        $controller->renderError($errorCode);
      }
    }
    catch (Exception $e) {
      die('<h1 class="error">' . $e->getMessage() . '</h1>');
    }
  }

}

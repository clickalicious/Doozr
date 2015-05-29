<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Doozr - Route - Dispatcher
 *
 * Dispatcher.php - Route dispatcher for dispatching route from request state to MVP.
 *
 * PHP versions 5.4
 *
 * LICENSE:
 * Doozr - The lightweight PHP-Framework for high-performance websites
 *
 * Copyright (c) 2005 - 2015, Benjamin Carl - All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * - All advertising materials mentioning features or use of this software
 *   must display the following acknowledgement: This product includes software
 *   developed by Benjamin Carl and other contributors.
 * - Neither the name Benjamin Carl nor the names of other contributors
 *   may be used to endorse or promote products derived from this
 *   software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Please feel free to contact us via e-mail: opensource@clickalicious.de
 *
 * @category   Doozr
 * @package    Doozr_Route
 * @subpackage Doozr_Route_Dispatcher
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2015 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version    Git: $Id$
 * @link       http://clickalicious.github.com/Doozr/
 */

/**
 * Doozr - Route - Dispatcher
 *
 * Route dispatcher for dispatching route from request state to MVP.
 *
 * @category   Doozr
 * @package    Doozr_Route
 * @subpackage Doozr_Route_Dispatcher
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2015 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version    Git: $Id$
 * @link       http://clickalicious.github.com/Doozr/
 */
class Doozr_Route_Dispatcher extends Doozr_Base_Class
{
    /**
     * The object of active route.
     * E.g. for [../foo/bar/baz/...] = [foo]
     *
     * @var string
     * @access protected
     */
    protected $object;

    /**
     * The action of the current route
     *
     * @var string
     * @access protected
     */
    protected $action;

    /**
     * The route currently processed
     *
     * @var array
     * @access protected
     */
    protected $route;

    /**
     * The translation for current route
     *
     * @var array
     * @access protected
     */
    protected $translation;

    /**
     * holds the directory separator
     *
     * this var is a shortcut to DIRECTORY_SEPARATOR
     *
     * @var string
     * @access protected
     */
    protected $separator = DIRECTORY_SEPARATOR;

    /**
     * holds the instance of model
     *
     * this var holds the instance of the model
     *
     * @var object
     * @access protected
     */
    protected $model;

    /**
     * holds the instance of view
     *
     * this var holds the instance of the view
     *
     * @var object
     * @access protected
     */
    protected $view;

    /**
     * Instance of Presentor if MVP-pattern is used
     *
     * @var object
     * @access protected
     */
    protected $connector;

    /**
     * Instance of configuration
     *
     * @var Doozr_Configuration_Interface
     * @access protected
     */
    protected $configuration;

    /**
     * Logger instance
     *
     * @var Doozr_Logging_Interface
     * @access protected
     */
    protected $logger;

    /**
     * Instance of filesystem service.
     *
     * @var Doozr_Filesystem_Service
     * @access protected
     */
    protected $filesystem;

    /**
     * Doozr caching service instance.
     *
     * @var Doozr_Cache_Service
     * @access protected
     */
    protected $cache;

    /**
     * HTTP Status 400
     *
     * @var int
     * @access public
     * @const
     */
    const HTTP_STATUS_400 = 400;

    /**
     * HTTP Status 404
     *
     * @var int
     * @access public
     * @const
     */
    const HTTP_STATUS_404 = 404;


    /*------------------------------------------------------------------------------------------------------------------
    | INIT
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Constructor.
     *
     * @param Doozr_Registry $registry Registry containing all kernel components
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @access public
     */
    public function __construct(
        Doozr_Registry $registry = null
    ) {
        $this
            ->registry($registry)
            ->configuration($registry->getConfiguration())
            ->logger($registry->getLogger())
            ->filesystem($registry->getFilesystem())
            ->cache($registry->getCache());

    }

    /*------------------------------------------------------------------------------------------------------------------
    | PUBLIC API
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Runs the request passed as argument by dispatching it to the backend layers.
     * This can be "Model" "View" "Presenter" in MVP runtimeEnvironment
     *
     * @param Doozr_Request_State $requestState The request state
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return Doozr_Response The response instance with final data for chaining send()
     * @access public
     */
    public function createFromRequestState(Doozr_Request_State $requestState)
    {
        $this->setRoute($requestState->getActiveRoute());

        $route = $requestState->getActiveRoute();

        $this->setObject($route[0]);
        $this->setAction($route[1]);

        // MODEL
        $this->setModel(
            $this->initModel(
                $this->getObject(),
                array(
                    $this->getRegistry(),
                    $requestState,
                    $this->getRoute(),
                    $this->getCache(),
                    $this->getConfiguration(),
                    $this->getTranslation(),
                )
            )
        );

        // VIEW
        $this->setView (
            $this->initView(
                $this->getObject(),
                array(
                    $this->getRegistry(),
                    $requestState,
                    $this->getRoute(),
                    $this->getCache(),
                    $this->getConfiguration(),
                    $this->getTranslation(),
                )
            )
        );

        // CONNECTOR => PRESENTER
        $this->setConnector(
            $this->initConnector(
                $this->getObject(),
                'Presenter',
                array(
                    $this->getRegistry(),
                    $requestState,
                    $this->getRoute(),
                    $this->getConfiguration(),
                    $this->model,
                    $this->view,
                    $this->getTranslation(),
                )
            )
        );


        // Dispatch the prepared objects
         $this->dispatch(
            $this->getConnector(),
            $this->getModel(),
            $this->getView(),
            $this->getObject(),
            $this->getAction()
        );

        // Retrieve prepared response from registry
        $response = $this->getRegistry()->getResponse();

        #$response->foo = dies;
        #$response->bar = das;

        // Return read to deliver configured response instance
        return $response;
    }

    /*------------------------------------------------------------------------------------------------------------------
    | INTERNAL API
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Dispatches a call.
     *
     * @param $connector
     * @param $model
     * @param $view
     * @param $object
     * @param $action
     *
     * @return $this Instance for chaining
     * @access protected
     * @throws Doozr_Connector_Exception
     */
    protected function dispatch($connector, $model, $view, $object, $action)
    {
        // Adjust to non official standard fooAction() for actions in presenter.
        $method = $action . 'Action';

        /**
         * We must respond with an exception here cause this should never ever happen and so its an
         * exceptional state and nothing we must handle with a nice response! This can be a client or server
         * triggered error/exception so we decide to give the client the responsibility by returning 400 resp. 404
         */
        if (($status = $this->validateRequest($connector, $method)) !== true) {

            switch ($status) {
                case self::HTTP_STATUS_400:
                    $message = 'No connector instance to execute route ("/' . $object .'/' . $action . '") on. Sure it exists?';
                    break;

                case self::HTTP_STATUS_404:
                default:
                    $message = 'Method: "' . $method . '()" in instance of class: "' . $object . '" not callable. Sure it exists?';
                    break;
            }

            throw new Doozr_Connector_Exception(
                $message,
                $status
            );

        } else {
            if ($view !== null && $view instanceof Doozr_Base_View) {
                $this->connector->attach($this->view);
            }

            // We try to execute the call to presenter e.g. $presenter->Main()
            try {
                // Call the requested Action on requested Presenter (Presenter:Action)
                $this->connector->{$method}();

                echo 'Look here: ';
                echo $this->connector->getStore()['view'];
                exit;

            } catch (Doozr_Base_Presenter_Rest_Exception $exception) {
                // Send JSON response on REST requests
                //$this->sendHttpResponse($exception->getCode(), $exception->getMessage(), true);

                /**
                 * At this point the exception was populated throughout the whole subsystem.
                 * Its totally clear what the error was and now the question should! be
                 * Is this a dev session? (debug === true) then send the whole exception + our
                 * default format for REST responses! If not debug send only the default fields!
                 */
                $this->sendJsonResponse($this->repackExceptionData($exception));

            } catch (Doozr_Base_Presenter_Exception $exception) {
                // Send "normal" (default) response on default requests
                $this->sendHttpResponse($exception->getCode(), $exception->getMessage());

            }
        }

        // Return instance for chaining
        return $this;
    }

    /**
     * Disassemble exception to a usable format.
     *
     * @param Exception $exception The exception to disassemble
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return \stdClass New assembled object
     * @access protected
     */
    protected function repackExceptionData(Exception $exception)
    {
        // Get debug state
        $debug = (defined('DOOZR_DEBUGGING') === true) ? DOOZR_DEBUGGING : true;

        $data           = new \stdClass();
        $data->message  = $exception->getMessage();
        $data->code     = $exception->getCode();
        $data->meta     = array();
        $data->security = array();

        if (isset($exception->token) === true) {
            $data->security['token'] = $exception->token;
        }

        if ($debug === true) {
            $data->meta['code']     = $exception->getCode();
            $data->meta['file']     = $exception->getFile();
            $data->meta['line']     = $exception->getLine();
            $data->meta['previous'] = object_to_array($exception->getPrevious());
        }

        return $data;
    }

    /**
     * Sends a HTTP Response to client using front controller.
     * Is used in case of core errors which can't be processed through system.
     *
     * @param string $code    The code used for response e.g. 404 ...
     * @param string $message The message for response
     * @param bool   $json    TRUE to send as JSON response, FALSE to send plain status
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function sendHttpResponse($code, $message, $json = false)
    {
        /* @var $front Doozr_Controller_Front */
        //$front = Doozr_Controller_Front::getInstance();

        /* @var $response Doozr_Response_Web */
        $response = $this->getResponse();

        if ($json === true) {
            $response->sendJson(
                json_encode(
                    array('error' => array($message))
                ),
                null,
                'UTF-8',
                true,
                false,
                true,
                $code
            );
        } else {
            $response->sendHttpStatus(
                $code,
                null,
                true,
                $message
            );
        }

        exit;
    }


    protected function sendJsonResponse($e)
    {
        /* @var $front Doozr_Controller_Front */
        //$front = Doozr_Controller_Front::getInstance();

        /* @var $response Doozr_Response_Web */
        $response = $this->getResponse();

        $response->sendJson(
            $e,
            null,
            null,
            false,
            false,
            true,
            $e->code
        );
    }


    /**
     * Validates the existing request data. A request needs at least a connector-instance
     * (Presenter) and an entry point (e.g. Main()) to be valid.
     *
     * @param string $instance The name of the connector class (Presenter, Controller)
     * @param string $method   The name of the method
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return boolean|integer TRUE if request is valid, otherwise HTTP-Error like 400 ...
     * @access protected
     */
    protected function validateRequest($instance, $method)
    {
        // Assume valid
        $valid = true;

        // no connector instance = Bad Request = 400
        if ($instance === null) {
            $valid = self::HTTP_STATUS_400;

        } elseif (method_exists($instance, $method) === false) {
            // No action to call after existing connector exist = Not Found = 404
            $valid = self::HTTP_STATUS_404;
        }

        return $valid;
    }

    /**
     * Setter for object.
     *
     * @param string $object The object of current request
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setObject($object)
    {
        $this->object = $object;
    }

    /**
     * Setter for object.
     *
     * @param string $object The object of current request
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function object($object)
    {
        $this->setObject($object);
        return $this;
    }

    /**
     * Getter for object.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string|null The object is set, otherwise NULL
     * @access protected
     */
    protected function getObject()
    {
        return $this->object;
    }

    /**
     * Setter for route.
     *
     * @param string $route The route of current request
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * Setter for route.
     *
     * @param string $route The route of current request
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function route($route)
    {
        $this->setRoute($route);
        return $this;
    }

    /**
     * Getter for route.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The route if set, otherwise NULL
     * @access protected
     */
    protected function getRoute()
    {
        return $this->route;
    }

    /**
     * Setter for translation.
     *
     * @param array $translation The translation of current request
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setTranslation(array $translation)
    {
        $this->translation = $translation;
    }

    /**
     * Setter for translation.
     *
     * @param array $translation The translation of current request
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function translation(array $translation)
    {
        $this->setTranslation($translation);
        return $this;
    }

    /**
     * Getter for translation.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array|null Translation as array if set, otherwise NULL
     * @access protected
     */
    protected function getTranslation()
    {
        return $this->translation;
    }

    /**
     * Setter for action.
     *
     * @param string $action The current action
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Setter for action.
     *
     * @param string $action The current action
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function action($action)
    {
        $this->setAction($action);
        return $this;
    }

    /**
     * Getter for action.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string|null The action if set, otherwise NULL
     * @access protected
     */
    protected function getAction()
    {
        return $this->action;
    }

    /**
     * Setter for configuration.
     *
     * @param Doozr_Configuration_Interface $configuration Instance
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Setter for configuration.
     *
     * @param Doozr_Configuration_Interface $configuration Instance
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function configuration($configuration)
    {
        $this->setConfiguration($configuration);
        return $this;
    }

    /**
     * Getter for configuration.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return Doozr_Configuration_Interface|null Doozr_Configuration_Interface if set, otherwise NULL
     * @access protected
     */
    protected function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Setter for logger.
     *
     * @param Doozr_Logging_Interface $logger Instance of logger
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setLogger(Doozr_Logging_Interface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Setter for logger.
     *
     * @param Doozr_Logging_Interface $logger Instance of logger
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function logger(Doozr_Logging_Interface $logger)
    {
        $this->setLogger($logger);
        return $this;
    }

    /**
     * Getter for logger.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return Doozr_Logging_Interface|null Doozr_Logging_Interface if set, otherwise NULL
     * @access protected
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * Setter for filesystem.
     *
     * @param Doozr_Filesystem_Service $filesystem The filesystem service instance
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setFilesystem(Doozr_Filesystem_Service $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Setter for filesystem.
     *
     * @param Doozr_Filesystem_Service $filesystem The filesystem service instance
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function filesystem(Doozr_Filesystem_Service $filesystem)
    {
        $this->setFilesystem($filesystem);
        return $this;
    }

    /**
     * Getter for filesystem.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return Doozr_Filesystem_Service|null Doozr_Filesystem_Service if set, otherwise NULL
     * @access protected
     */
    protected function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Setter for cache.
     *
     * @param Doozr_Cache_Service $cache Instance of Doozr cache service
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setCache(Doozr_Cache_Service $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Setter for cache.
     *
     * @param Doozr_Cache_Service $cache Instance of Doozr cache service
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function cache(Doozr_Cache_Service $cache)
    {
        $this->setCache($cache);
        return $this;
    }

    /**
     * Getter for cache.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return Doozr_Cache_Service|null Doozr_Cache_Service if set, otherwise NULL
     * @access protected
     */
    protected function getCache()
    {
        return $this->cache;
    }

    /**
     * Setter for connector.
     *
     * @param Doozr_Base_Connector_Interface $connector The connector instance
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setConnector(Doozr_Base_Connector_Interface $connector = null)
    {
        $this->connector = $connector;
    }

    /**
     * Setter for connector.
     *
     * @param Doozr_Base_Connector_Interface $connector The connector instance
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function connector(Doozr_Base_Connector_Interface $connector)
    {
        $this->setConnector($connector);
        return $this;
    }

    /**
     * Getter for connector.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return Doozr_Base_Connector_Interface|null Doozr_Base_Connector_Interface if set, otherwise NULL
     * @access protected
     */
    protected function getConnector()
    {
        return $this->connector;
    }

    /**
     * Setter for model.
     *
     * @param Doozr_Base_Model_Interface $model The model
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setModel(Doozr_Base_Model_Interface $model = null)
    {
        $this->model = $model;
    }

    /**
     * Setter for model.
     *
     * @param Doozr_Base_Model_Interface $model The model
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function model($model)
    {
        $this->setModel($model);
        return $this;
    }

    /**
     * Getter for model.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return Doozr_Base_Model_Interface|null Doozr_Base_Model_Interface if set, otherwise NULL
     * @access protected
     */
    protected function getModel()
    {
        return $this->model;
    }

    /**
     * Setter for view.
     *
     * @param Doozr_Base_View_Interface $view The view
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setView(Doozr_Base_View_Interface $view = null)
    {
        $this->view = $view;
    }

    /**
     * Setter for view.
     *
     * @param Doozr_Base_View_Interface $view The view
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function view(Doozr_Base_View_Interface $view)
    {
        $this->setView($view);
        return $this;
    }

    /**
     * @return Doozr_Base_View_Interface
     */
    protected function getView()
    {
        return $this->view;
    }

    /**
     * Returns the model layer.
     *
     * @param string     $model     The name of the current model.
     * @param null|array $arguments The optional arguments to pass to model.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return Doozr_Base_Model
     * @access protected
     */
    protected function initModel($model, $arguments = null)
    {
        return $this->initLayer($model, 'Model', $arguments);
    }

    /**
     * Returns the view layer.
     *
     * @param string     $view      The name of the current view.
     * @param null|array $arguments The optional arguments to pass to view.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return Doozr_Base_View
     * @access protected
     */
    protected function initView($view, $arguments = null)
    {
        return $this->initLayer($view, 'View', $arguments);
    }

    /**
     * Returns the connector layer.
     *
     * @param string     $connector The name of the current connector.
     * @param string     $type      The type of the current connector.
     * @param null|array $arguments The optional arguments to pass to connector.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return Doozr_Base_Connector_Interface
     * @access protected
     */
    protected function initConnector($connector, $type, $arguments = null)
    {
        return $this->initLayer($connector, $type, $arguments);
    }

    /**
     * Creates and returns an instance of a layer
     *
     * This method is intend to instantiate a new layer
     * (can be either Model|View|Presenter).
     *
     * @param string $request   The resource requested
     * @param string $layer     The part/layer of the MVP structure to instantiate and return
     * @param array  $arguments An array of Parameters to append at instantiation
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return Doozr_Base_Connector_Interface|Doozr_Base_Model|Doozr_Base_View
     * @access protected
     */
    protected function initLayer($request, $layer = 'Model', $arguments = null)
    {
        // assume instance won't be created
        $instance = null;

        // build classname
        $classname = $layer . '_' . ucfirst($request);

        // build location (path and filename)
        $classFileAndPath = DOOZR_APP_ROOT . str_replace('_', $this->separator, $classname) . '.php';

        // check if requested layer file exists
        if ($this->getFilesystem()->exists($classFileAndPath)) {
            include_once $classFileAndPath;
            $instance = $this->instanciate($classname, $arguments);
        }

        return $instance;
    }









    /**
     * Setter for request state.
     *
     * @param Doozr_Request_State $requestState The request state.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setRequestState(Doozr_Request_State $requestState)
    {
        $this->requestState = $requestState;
    }

    /**
     * Setter for request state.
     *
     * @param Doozr_Request_State $requestState The request state.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function requestState(Doozr_Request_State $requestState)
    {
        $this->setRequestState($requestState);
        return $this;
    }

    /**
     * Getter for request state.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return Doozr_Request_State|null The stored instance if set, otherwise NULL
     * @access protected
     */
    protected function getRequestState()
    {
        return $this->requestState;
    }

    /**
     * Setter for response state.
     *
     * @param Doozr_Response_State $responseState The response state.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function setResponseState(Doozr_Response_State $responseState)
    {
        $this->responseState = $responseState;
    }

    /**
     * Setter for response state.
     *
     * @param Doozr_Response_State $responseState The response state.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return $this Instance for chaining
     * @access protected
     */
    protected function responseState(Doozr_Response_State $responseState)
    {
        $this->setResponseState($responseState);
        return $this;
    }

    /**
     * Getter for response state.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return Doozr_Response_State|null The stored instance if set, otherwise NULL
     * @access protected
     */
    protected function getResponseState()
    {
        return $this->responseState;
    }

    /**
     * Returns the request state for userland (developer) as "request".
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return Doozr_Request_State The request state
     * @access public
     */
    public function getRequest()
    {
        return $this->getRequestState();
    }

    /**
     * Returns the response state for userland (developer) as "response".
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return Doozr_Response_State The response state
     * @access public
     */
    public function getResponse()
    {
        return $this->getResponseState();
    }
}

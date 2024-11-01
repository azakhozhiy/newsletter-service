<?php

namespace WaHelp\Core\Routing;

use ReflectionException;
use ReflectionMethod;
use WaHelp\Core\Application;
use WaHelp\Core\Exception\RouterException;
use WaHelp\Core\Http\Request;
use WaHelp\Core\Http\Response;

class Router
{
    public const GET = 'GET';
    public const POST = 'POST';

    protected array $routes;
    protected Request $request;
    protected string $defaultModule = 'main';
    protected string $defaultAction = 'index';
    protected Application $app;

    public function __construct(Application $app, array $routes)
    {
        $this->request = $app->make(Request::class);
        $this->routes = $routes;
        $this->app = $app;
    }

    private function validateModule(array $module): void
    {
        if (!isset($module['controller'], $module['actions'])) {
            throw new RouterException('Module is invalid.');
        }
    }

    private function validateAction(array $action): void
    {
        if (!isset($action['method'])) {
            throw new RouterException('Action must have method name.');
        }

        if (!isset($action['method_type'])) {
            throw new RouterException('Action must have method type.');
        }
    }

    private function actionIsExistInModule(array $module, string $action_name): bool
    {
        return isset($module['actions'][$action_name]);
    }

    /**
     * @param  string  $class
     * @param $method
     * @return array
     */
    public function getMethodDependencies(string $class, $method): array
    {
        if (method_exists($class, $method)) {
            $refMethod = new ReflectionMethod($class, $method);

            $parameters = $refMethod->getParameters();
            $dependencies = [];

            foreach ($parameters as $parameter) {
                if ($parameter->getType()) {
                    $argName = $parameter->getType()->getName();
                    $dependencies[] = $this->app->make($argName);
                }
            }

            return $dependencies;
        }

        return [];
    }

    /**
     * @param $controller
     * @param  string  $method_name
     * @return Response
     * @throws ReflectionException
     */
    public function call($controller, string $method_name): Response
    {
        $controllerInstance = new $controller(...$this->getMethodDependencies($controller, '__construct'));

        return $controllerInstance->{$method_name}(...$this->getMethodDependencies($controller, $method_name));
    }

    /**
     * @return Response
     * @throws ReflectionException
     */
    public function dispatch(): Response
    {
        $module_name = $this->request->get('module', $this->defaultModule);
        $action_name = $this->request->get('action', $this->defaultAction);
        $method_type = $this->request->getMethod();

        $controller = null;
        $method = null;

        if (!$module_name || !$action_name) {
            throw new RouterException("Empty module name or action name.");
        }

        if (isset($this->routes[$module_name])) {
            //validate module
            $module = $this->routes[$module_name];
            $this->validateModule($module);

            $controller = $module['controller'];

            if ($this->actionIsExistInModule($module, $action_name)) {
                $action = $module['actions'][$action_name];
                $this->validateAction($action);

                if ($action['method_type'] !== $method_type) {
                    throw new RouterException("Unsupported method type.");
                }

                $method = $action['method'];
            }
        }

        if (!class_exists($controller)) {
            throw new RouterException("Controller $controller does not exist.");
        }

        if (!method_exists($controller, $method)) {
            throw new RouterException("Action $action_name does not exist.");
        }

        return $this->call($controller, $method);
    }
}
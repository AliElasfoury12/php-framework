<?php

namespace core;

use core\database\DB;
use core\database\Model\MainModel;
use core\request\Request;
use core\router\Router;
use app\models\User;

class App  {
    const EVENT_BEFORE_REQUEST = 'beforeRequest';
    const EVENT_AFTER_REQUEST = 'afterRequest';
    protected array $eventListeners = [];
    public Request $request;
    public Router $router;
    public Middleware $middleware;
    public View $view;
    public Session $session;
    public DB $db;
    public ?User $user = null;
    public MainModel $model;
    public MainController $controller;
    public static App $app;
   

    public function __construct () {
        self::$app = $this;
        $this->router = new Router();
        $this->request = new Request();
        $this->middleware = new Middleware();
        $this->view = new View();
        $this->session = new Session();
        $this->db = new DB();
        $this->user = new User();
        $this->model = new MainModel();
        $this->controller = new MainController();
       
        if($this->session->get('user')){
            $user = $this->session->get('user');
            foreach ($user as $key => $value) {
                $this->user->$key = $value;
            }
        }
    }

    public function run() {
        $this->triggerEvent(self::EVENT_BEFORE_REQUEST);
        echo $this->router->resolve();
    }

    public static  function dd (array $vars) {
       self::dump($vars);
        exit;
    }

    public static  function dump (array $vars) {
        echo "<pre>";
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo "</pre> \n";
    }

    public function on($eventName, $callback) {
      
        $this->eventListeners[$eventName][] = $callback;
    }

    public function triggerEvent($eventName) {
      
        $callbacks = $this->eventListeners[$eventName] ?? [] ;

        foreach ($callbacks as $callback) {
            call_user_func($callback);
        }
    }
}
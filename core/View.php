<?php

namespace core;

use core\Model\MainModel;
use core\request\Validator;

class View   {

    private static function prepare ($view) {
        $view = str_replace('.', '/', $view);
        $viewContent = file_get_contents(__DIR__."/../src/views/$view.php");
        return $viewContent;
    }

    public  function layoutView ($view, $params = [], $layout = 'main') 
    {
        $title = $view;
        extract($params);

        $errors = Validator::getErrorMessages();
        $errors = (object) $errors;

        $layoutContent = $this->prepare("layouts/$layout");
        $viewContent = $this->prepare($view);
        
        $content = str_replace('@content', $viewContent, $layoutContent);

        $content = self::blade($content);

        //file_put_contents(__DIR__.'/../cache/main.php', $content);

       // $content = $this->addCss($content);

        $content = eval("?> $content");

        return $content;
    }

    private function addCss($content) {
        preg_match("/@css\('(.*?)'\)/", $content, $match);
        $fileName = $match[1];
        $css = file_get_contents("../src/css/$fileName.css");
        $content = str_replace($match[0], "<link rel='stylesheet' href='../src/css/$fileName.css'>", $content);
        return $content;
    }

     public function view ($view, $params = []) {
        $title = $view;
        extract($params);

        $errors = Validator::getErrorMessages();
        $errors = (object) $errors;

        $content = self::prepare($view);

        //$content = $this->addCss($content);

        $content = self::blade($content);


        $content = eval("?>". $content);

        return $content;
    }
 
    private static function blade ($content) {
        $guest = App::$app->user->isGuest() ? 1 : 0;
        $user = $guest ? 0 : 1;

        preg_match("/@include\(\s*'(.*?)'\s*\)/", $content, $match);
        if ($match) $content = str_replace($match[0], self::prepare($match[1]),$content);

        $content = preg_replace('/{{\s*(.*?)\s*}}/', '<?php echo $1 ;?>', $content);

        $content = preg_replace('/@if\s*\(\s*(.*?)\s*\)/', '<?php if($1): ?>', $content);
        $content = str_replace('@else', '<?php else: ?>', $content);
        $content = str_replace('@endif', '<?php endif; ?>', $content); 

        $content = preg_replace('/@foreach\s*\(\s*(.*?)\s*\)/', '<?php foreach($1): ?>', $content);
        $content = str_replace('@endforeach', '<?php endforeach ?>', $content);

        $content = str_replace('@guest', "<?php if($guest): ?>", $content);
        $content = str_replace('@endGuest', '<?php endif; ?>', $content);

        $content = str_replace('@auth', "<?php if($user): ?>", $content);
        $content = str_replace('@endAuth', '<?php endif; ?>', $content); 

        return $content;
    }
}
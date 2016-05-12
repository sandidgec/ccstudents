<?php
/**
 * automatically loads the class on demand
 *
 * @param string $className class name to load
 * @return bool true if the class loaded correctly, false if not
 **/
function loadClass($className) {
    $className[0] = strtolower($className[0]);
    $className = preg_replace_callback("/([A-Z])/", function($matches) {
        return("-" . strtolower($matches[0]));
    }, $className);
    $classFile = __DIR__ . "/" . $className . ".php";
    if(is_readable($classFile) === true && require_once($classFile)) {
        return(true);
    } else {
        return(false);
    }
}
// tell PHP to use the loadClass() function to automatically load class files
spl_autoload_register("loadClass");
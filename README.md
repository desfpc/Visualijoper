# Visualijoper
PHP class for rendering variables and objects. Used when debugging code.

How to install and use:

Add to your composer.json in "require" or "require-dev" section: 

    "desfpc/visualijoper": "2.*"



Use static method "visualijop":

    desfpc\Visualijoper\Visualijoper::visualijop($yourValue, $name, $printScripts);

Or use object "Visualijoper" and method "render": 

    $vj = new desfpc\Visualijoper\Visualijoper($yourValue, $name, $printScripts);
    
    echo $vj->render();

- string $name - optional title of block
- bool $printScripts - optional (by default - true) - echo css ad js
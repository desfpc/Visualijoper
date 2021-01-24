# Visualijoper
PHP class for rendering variables and objects. Used when debugging code.

How to install and use:
1) include class file in your php script.
2) Connect css and js of the class.
3) Use function visualijop:

    visualijop($yourValue, $name); //$name - optional
4) Or use object visualijoper: 

    $vj = new visualijoper($yourValue, $name);
    
    echo $vj->render();



TODO: The class now requires jQuery. The task is to make a version in pure js.

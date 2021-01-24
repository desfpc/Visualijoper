<?php
/**
 * visualijoper v1.0
 * Created by Peshalov Sergey https://github.com/desfpc
 * PHP class for rendering variables and objects. Used when debugging code.
 * https://github.com/desfpc/Visualijoper
 */

class visualijoper
{
    public $name = false;//заголовок (не обязательно) для понимания, что вообще выводится
    public $var;//переданная для визуализации
    public $type;//тип переменной var
    private $types = [
        'Null' => [
            'body' => false
        ],
        'String' => [
            'body' => true
        ],
        'Integer' => [
            'body' => false
        ],
        'Float' => [
            'body' => false
        ],
        'Array' => [
            'body' => true
        ],
        'Object' => [
            'body' => true
        ],
        'Boolean' => [
            'body' => false
        ],
        'Unknown' => [
            'body' => false
        ]
    ];


    public function __construct($var, $name = false)
    {
        $this->var = $var;
        if($name && $name != ''){
            $this->name = $name;
        }
        $this->type = $this->checkType($this->var);
    }

    //Определяем тип переменной. Поддерживаются сейчас переменные Null, String, Float, Integer, Array, Object, Boolean
    public function checkType($var){
        if (is_null($var)){
            return 'Null';
        }
        if (is_string($var)) {
            return 'String';
        }
        if (is_integer($var)){
            return 'Integer';
        }
        if (is_float($var)){
            return 'Float';
        }
        if(is_array($var)){
            return 'Array';
        }
        if (is_object($var)){
            return 'Object';
        }
        elseif (is_bool($var)){
            return 'Boolean';
        }
        return 'Unknown';
    }

    public function valueForHeaderPrint($value, $type){
        switch ($type){
            case 'String':
                $addHellip = false;
                $len = mb_strlen($value, 'UTF8');
                if($len > 100){
                    $value = mb_substr($value, 0, 100, 'UTF8');
                    $addHellip = true;
                }
                $value = htmlspecialchars($value, ENT_QUOTES);
                if($addHellip){
                    $value.='&hellip;';
                }
                $value.=' <span class="vj-header__size">('.$len.' symbols)</span>';
                break;
            case 'Boolean':
                if($value){
                    $value = 'True';
                }
                else {
                    $value = 'False';
                }
                break;
            case 'Object':
                //$objClass = get_class($value);
                $value = get_class($value);
                break;
            case 'Array':
                $cnt = count($value);
                if($cnt == 1){
                    $cntName = 'element';
                }
                else {
                    $cntName = 'elements';
                }
                $value = '('.$cnt.' '.$cntName.')';
                break;
        }
        return $value;
    }

    private function makeHeader(){
        //если предпологается тело, делаем заголовок кликабельным
        if($this->types[$this->type]['body']){
            $moreClass = 'vj-header_clickable';
        }
        else {
            $moreClass = '';
        }
        $out = '<div class="visualijoper"><div class="visualijoper__header vj-header '.$moreClass.'">';
        if($this->name){
            $out.='<p class="vj-header__name">'.$this->name.':</p>';
        }
        $out .= '<span class="vj-header__type">'.$this->type.'</span>';
        $out.=': <span class="vj-header__value">'.$this->valueForHeaderPrint($this->var, $this->type).'</span>';
        $out.='</div>';
        return $out;
    }
    private function makeFooter(){
        $backtrace = debug_backtrace();
        $fromFile = $backtrace[2]['file'];
        $fromLine = $backtrace[2]['line'];
        return '<div class="visualijoper__footer">Called from <strong>'.$fromFile.'</strong>, line <strong>'.$fromLine.'</strong><a target="_blank" href="https://github.com/desfpc/Visualijoper">powered by Visualijoper</a></div></div>';
    }

    private function makeObjectBody(object $value, $level=0){
        ++$level;
        if($level == 6){
            $level = 1;
        }
        if(!is_object($value)){
            return;
        }
        return $this->makeArrayBody(get_object_vars($value), $level);
    }

    private function makeArrayBody(array $value, $level=0){

        $out = '';
        ++$level;

        if($level == 6){
            $level = 1;
        }

        if(!is_array($value) || count($value) == 0){
            return;
        }

        foreach ($value as $key => $item) {

            //получаем тип значения
            $tempType = $this->checkType($item);
            $moreClass = 'visualijoper__row_level'.$level.' ';
            //раскрываемая строка
            if($this->types[$tempType]['body']){
                $moreClass .= 'visualijoper__row_clickable';
                $typeSymbol = '&hellip;';
                $body = $this->makeBody($tempType,$item,$level);
            }
            else {
                $typeSymbol = '=>';
                $body='';
            }

            $out.='<div class="visualijoper__row vj-row '.$moreClass.'">
                <p class="vj-row__header">
                    <span class="vj-row__key">'.$key.'</span>
                    <span class="vj-row__type">'.$tempType.'</span>
                    '.$typeSymbol.'
                    <span class="vj-row__value">'.$this->valueForHeaderPrint($item, $tempType).'</span>
                </p>'.$body;



            $out.='</div>';
        }

        return $out;

    }

    private function makeBody($type, $value, $level=0){
        $out = '<div class="vj-body">';

        switch ($type){
            case 'String':
                $out.='<div class="vj-body__content vj-body__content_string"><pre>'.htmlspecialchars($value, ENT_QUOTES).'</pre></div>';
                break;
            case 'Array':
                $out.='<div class="vj-body__content vj-body__content_array">'.$this->makeArrayBody($value, $level).'</div>';
                break;
            case 'Object':
                $out.='<div class="vj-body__content vj-body__content_object">'.$this->makeObjectBody($value, $level).'</div>';
                break;
        }

        $out .= '</div>';

        return $out;
    }

    public function render(){

        //формирование заголовка
        $out = $this->makeHeader($this->var);

        //если нужно, формироуем детализацию
        if($this->types[$this->type]['body'])
        {
            $out .= $this->makeBody($this->type, $this->var);
        }

        //формирование подвала
        $out .= $this->makeFooter();

        return $out;

    }

}

/**
 * Alias for convenient use of a class as a function
 * @param $var - some type variable
 */
function visualijop($var, $name = false){
    $vj = new visualijoper($var, $name);
    echo $vj->render();
}
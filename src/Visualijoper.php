<?php
/**
 * Visualijoper v2.0
 * Created by Sergey Peshalov https://github.com/desfpc
 * PHP class for rendering variables and objects. Used when debugging code.
 * https://github.com/desfpc/Visualijoper
 */

namespace desfpc\Visualijoper;

class Visualijoper
{
    /** @var bool|string Title for Visualijoper block */
    public $name = false;

    /** @var mixed Passed for visualization variable */
    public $var;

    /** @var string Passed variable type  */
    public $type;

    /** @var bool print scripts (CSS/JS) flag */
    public $printScripts;

    /** @var array<string, array{body: bool}> Possible types of the passed variable */
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

    /** @var bool CSS/JS Scripts printed flag */
    private static $_ifScriptsPrinted = false;

    /**
     * Visualijoper constructor
     *
     * @param mixed $var
     * @param bool|string $name
     * @param bool $printScripts
     */
    public function __construct($var, $name = false, bool $printScripts = true)
    {
        $this->var = $var;
        if ($name && $name != '') {
            $this->name = $name;
        }
        $this->type = $this->_checkType($this->var);
        $this->printScripts = $printScripts;
    }

    /**
     * Check type of passed variable
     * Supported types: Null, String, Float, Integer, Array, Object, Boolean
     * For a variable of other types, returning Unknown type
     *
     * @param mixed $var
     * @return string
     */
    private function _checkType($var): string
    {
        if (is_null($var)) {
            return 'Null';
        }
        if (is_string($var)) {
            return 'String';
        }
        if (is_integer($var)) {
            return 'Integer';
        }
        if (is_float($var)) {
            return 'Float';
        }
        if (is_array($var)) {
            return 'Array';
        }
        if (is_object($var)) {
            return 'Object';
        }
        if (is_bool($var)) {
            return 'Boolean';
        }
        return 'Unknown';
    }

    /**
     * Render Visualijoper block
     *
     * @return string
     */
    public function render(): string
    {
        $out = '';

        if ($this->printScripts && !self::$_ifScriptsPrinted) {
            self::$_ifScriptsPrinted = true;
            $out .= '<style>' . file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'visualijoper.css') . '</style>';
            $out .= '<script>' . file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'visualijoper.js') . '</script>';
        }

        $out .= $this->_makeHeader();

        if ($this->types[$this->type]['body']) {
            $out .= $this->_makeBody($this->type, $this->var);
        }

        $out .= $this->_makeFooter();

        return $out;
    }

    /**
     * Make Visualijoper block header html string
     *
     * @return string
     */
    private function _makeHeader(): string
    {
        if ($this->types[$this->type]['body']) {
            $moreClass = 'vj-header_clickable';
        } else {
            $moreClass = '';
        }

        $out = '<div class="visualijoper"><div class="visualijoper__header vj-header ' . $moreClass . '">';
        if ($this->name && $this->name != '') {
            $out .= '<p class="vj-header__name">' . $this->name . ':</p>';
        }
        $out .= '<span class="vj-header__type">' . $this->type . '</span>';
        $out .= ': <span class="vj-header__value">' . $this->_valueForHeaderPrint($this->var, $this->type) . '</span>';
        $out .= '</div>';
        return $out;
    }

    /**
     * Make Visualijoper block footer html string
     *
     * @return string
     */
    private function _makeFooter(): string
    {
        $backtrace = debug_backtrace();
        $fromFile = $backtrace[2]['file'];
        $fromLine = $backtrace[2]['line'];
        return '<div class="visualijoper__footer">Called from <strong>' . $fromFile . '</strong>, line <strong>' . $fromLine . '</strong><a target="_blank" href="https://github.com/desfpc/Visualijoper">powered by Visualijoper</a></div></div>';
    }

    /**
     * Make Visualijoper block body html string for object variable
     *
     * @param object $value
     * @param int $level
     * @return string|void
     */
    private function _makeObjectBody($value, int $level = 0)
    {
        ++$level;
        if ($level == 6) {
            $level = 1;
        }
        if (!is_object($value)) {
            return;
        }
        return $this->_makeArrayBody(get_object_vars($value), $level);
    }

    /**
     * Make Visualijoper block body html string for array variable
     *
     * @param array $value
     * @param int $level
     * @return string|void
     */
    private function _makeArrayBody(array $value, int $level = 0)
    {
        $out = '';
        ++$level;

        if ($level == 6) {
            $level = 1;
        }

        if (!is_array($value) || count($value) == 0) {
            return;
        }

        foreach ($value as $key => $item) {
            $tempType = $this->_checkType($item);
            $moreClass = 'visualijoper__row_level' . $level . ' ';

            if ($this->types[$tempType]['body']) {
                $moreClass .= 'visualijoper__row_clickable';
                $typeSymbol = '&hellip;';
                $body = $this->_makeBody($tempType, $item, $level);
            } else {
                $typeSymbol = '=>';
                $body = '';
            }

            $out .= '<div class="visualijoper__row vj-row ' . $moreClass . '">
                <p class="vj-row__header">
                    <span class="vj-row__key">' . $key . '</span>
                    <span class="vj-row__type">' . $tempType . '</span>
                    ' . $typeSymbol . '
                    <span class="vj-row__value">' . $this->_valueForHeaderPrint($item, $tempType) . '</span>
                </p>' . $body;

            $out .= '</div>';
        }
        return $out;
    }

    /**
     * Make Visualijoper block body html string
     *
     * @param string $type
     * @param mixed $value
     * @param int $level
     * @return string
     */
    private function _makeBody(string $type, $value, int $level = 0): string
    {
        $out = '<div class="vj-body">';

        switch ($type) {
            case 'String':
                $out .= '<div class="vj-body__content vj-body__content_string"><pre>' . htmlspecialchars($value, ENT_QUOTES) . '</pre></div>';
                break;
            case 'Array':
                $out .= '<div class="vj-body__content vj-body__content_array">' . $this->_makeArrayBody($value, $level) . '</div>';
                break;
            case 'Object':
                $out .= '<div class="vj-body__content vj-body__content_object">' . $this->_makeObjectBody($value, $level) . '</div>';
                break;
        }

        $out .= '</div>';

        return $out;
    }

    /**
     * Format value for show in Visualijoper block body
     *
     * @param mixed $value
     * @param string $type
     * @return mixed|string
     */
    private function _valueForHeaderPrint($value, string $type)
    {
        switch ($type) {
            case 'String':
                $addHellip = false;
                $len = mb_strlen($value, 'UTF8');
                if ($len > 100) {
                    $value = mb_substr($value, 0, 100, 'UTF8');
                    $addHellip = true;
                }
                $value = htmlspecialchars($value, ENT_QUOTES);
                if ($addHellip) {
                    $value .= '&hellip;';
                }
                $value .= ' <span class="vj-header__size">(' . $len . ' symbols)</span>';
                break;
            case 'Boolean':
                if ($value) {
                    $value = 'True';
                } else {
                    $value = 'False';
                }
                break;
            case 'Object':
                $value = get_class($value);
                break;
            case 'Array':
                $cnt = count($value);
                if ($cnt == 1) {
                    $cntName = 'element';
                } else {
                    $cntName = 'elements';
                }
                $value = '(' . $cnt . ' ' . $cntName . ')';
                break;
        }
        return $value;
    }

    /**
     * Alias for convenient use of a class as a function
     *
     * @param mixed $var - some type variable
     * @param bool|string $name - name of visualijoper block
     */
    static public function visualijop($var, $name = false, $printScripts = true){
        $vj = new visualijoper($var, $name, $printScripts);
        echo $vj->render();
    }

}
<?php

namespace MrCat\SugarCrmOrmApi\Render;

use MrCat\GenerateForm\Html as HTML;

class GenerateHtml
{
    protected $types = [
        'varchar' => 'inputText',
        'text' => 'textArea',
        'date' => 'date',
        'enum' => 'select',
        'multienum' => 'checkbox',
        'radioenum' => 'radio',
        'phone' => 'number',
        'int' => 'number',
        'name' => 'text',
        'url' => 'url',
        'file' => 'file',
        'image' => 'file',
        'dynamicenum' => 'select',
    ];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * GenerateHtml constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function inputText()
    {
        return HTML::text(
            $this->options['name'],
            $this->options['default'],
            $this->options['attributes']
        );
    }

    /**
     * @return string
     */
    public function url()
    {
        return HTML::input(
            'url',
            $this->options['name'],
            $this->options['default'],
            $this->options['attributes']
        );
    }

    /**
     * @return string
     */
    public function select()
    {
        return HTML::select(
            $this->options['name'],
            $this->options['options'],
            $this->options['default'],
            $this->options['attributes']
        );
    }

    /**
     * @return string
     */
    public function multiSelect()
    {
        return HTML::selectMultiple(
            $this->options['name'],
            $this->options['options'],
            $this->options['default'],
            $this->options['attributes']
        );
    }

    /**
     * @return mixed
     */
    public function textArea()
    {
        return HTML::textArea(
            $this->options['name'],
            $this->options['default'],
            $this->options['attributes']
        );
    }

    /**
     * @return string
     */
    public function date()
    {
        return HTML::input(
            'date',
            $this->options['name'],
            $this->options['default'],
            $this->options['attributes']
        );
    }

    /**
     * @return string
     */
    public function radio()
    {
        $html = "";
        foreach ($this->options['options'] as $key => $value) {
            $html .= "<label style='padding-right: 10px; padding-left: 10px;'> {$value} </label>" .
                HTML::radio(
                    $this->options['name'],
                    false,
                    array_merge(['value' => $key], $this->options["attributes"])
                );
        }

        return $html;
    }
    /**
     * @return string
     */
    public function checkbox()
    {
        $html = "";
        foreach ($this->options['options'] as $key => $value) {
            $html .= HTML::checkbox(
                    $this->options['name'].'[]',
                    false,
                    array_merge(['value' => $key], $this->options["attributes"])
                ). "<span style='padding-left:5px;'>{$value}</span> <br>";
        }

        return $html;
    }

    /**
     * @return string
     */
    public function number()
    {
        return HTML::input('number',
            $this->options['name'],
            $this->options['default'],
            $this->options['attributes']
        );
    }

    /**
     * @return string
     */
    public function file()
    {
        return HTML::input('file',
            $this->options['name'],
            $this->options['default'],
            $this->options['attributes']
        );
    }

    public function label()
    {
        return Html::label($this->options['default']);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string $method
     * @param  array $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $method = $this->types[$method];
        return call_user_func_array([$this, $method], $parameters);
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string $method
     * @param  array $parameters
     *
     * @return mixed
     */
    public static function input($method, $parameters)
    {
        $instance = new static($parameters);
        return call_user_func_array([$instance, $method], $parameters);
    }
}

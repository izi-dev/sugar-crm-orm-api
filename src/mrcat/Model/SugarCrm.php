<?php

namespace MrCat\SugarCrmOrmApi\Model;

use MrCat\SugarCrmOrmApi\Collection\Collection;
use MrCat\SugarCrmOrmApi\Render\Form;

abstract class  SugarCrm
{
    /**
     * Module Name SuiteCrm
     *
     * @var string
     */
    protected $module = '';

    /**
     * Select Fields.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Select Fields for FORM.
     *
     * @var array
     */
    protected $formFields = [];

    /**
     * The model's attributes.
     *
     * @var array
     */
    public $attributes = [];

    /**
     * Relations Model
     *
     * @var array
     */
    protected $relations = [];

    /**
     * Get Module Name
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Save a new model and return the instance.
     *
     * @param  array $attributes
     * <pre>
     *      [
     *          [
     *              'field' => 'new value',
     *              'other_field' => 'other new value',
     *          ],
     *          [
     *              'field' => 'new value',
     *              'other_field' => 'other new value',
     *          ],
     *      ]
     * </pre>
     * @return array
     */
    public static function create(array  $attributes)
    {
        $instance = new static();

        return $instance->builder()->create($attributes);
    }

    /**
     * Save the model in the database.
     *
     * @param  array $attributes
     * <pre>
     *      [
     *          'field' => 'new value',
     *      ]
     * </pre>
     * @return Collection
     */
    public function save(array $attributes = [])
    {
        $attributes = array_merge($this->attributes, $attributes);

        $this->makeAttributes($attributes);

        return $this->builder()->save($this->attributes);
    }

    /**
     * Execute a query for a single record by ID.
     *
     * @param  int $id
     *
     * @return Collection
     */
    public static function find($id)
    {
        $instance = new static();

        return $instance->builder()->find($id);
    }

    /**
     * Get relations of model
     *
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Get Fields Module
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Append array relations
     *
     * @param $sugarCrm
     * @param $linkFieldName
     * @param $query
     *
     * @return Builder
     */
    public function addRelation($sugarCrm, $linkFieldName, $query = '')
    {
        $instance = new $sugarCrm();

        $this->relations = [
            'class_relation'       => $sugarCrm,
            'link_field_name'      => $linkFieldName,
            'related_fields'       => $instance->getFields(),
            'related_module_query' => $query,
        ];

        return $this->builder();
    }

    /**
     * Delete a record from the database.
     *
     * @return int
     */
    public function delete()
    {
        return $this->builder()->delete($this->attributes);
    }

    /**
     * Upload Documents
     *
     * @param $attributes
     * @return mixed
     */
    public static function setDocumentsSugarCrm(array $attributes)
    {
        $instance = new static();

        return $instance->builder()->setDocument($attributes);
    }

    /**
     * Upload Documents
     *
     * @param $attributes
     * @return mixed
     */
    public function getDocumentsSugarCrm()
    {
        return $this->builder()->getDocument($this->attributes['document_revision_id']);
    }

    /**
     * Upload Documents
     *
     * @param $attributes
     * @return mixed
     */
    public function setRelation($moduleRelation, array $relatedId, array $data = [])
    {
        $instance = new static();

        return $instance->builder()->setRelation($this->attributes['id'], $moduleRelation, $relatedId, $data);
    }

    /**
     * Instance new Builder
     *
     * @param null $model
     * @return Builder
     */
    private function builder($model = null)
    {
        if (is_null($model)) {
            $model = $this;
        }

        return new Builder($model);
    }

    /**
     * Instance new Builder
     *
     * @param array $select
     * <pre>
     *      [
     *          'field',
     *          'other_field',
     *      ]
     * </pre>
     * @return Builder;
     */
    public static function all(array $select = [])
    {
        $instance = new static();

        return $instance->builder()->select($select)->scopeAll();
    }

    /**
     * Recupera la lista campos para un módulo específico.
     */
    public static function getModuleFields()
    {
        $instance = new static();

        return $instance->builder()->getModuleFIelds($instance->getModule());
    }

    /**
     * Instance new Form
     */
    public static function form()
    {
        $instance = new static();

        return new Form($instance->module, $instance->formFields);
    }

    /**
     * Generates the attributes of the User class
     *
     * @param $attributes
     */
    public function makeAttributes($attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    /**
     * SuiteCrm constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->makeAttributes($attributes);
    }

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param  string $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        return method_exists($this, 'get' . $this->studly($key) . 'Attribute');
    }

    /**
     * @param $key
     * @return mixed
     */
    public function studly($key)
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $key));

        return str_replace(' ', '', $value);
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        return $this->{'get' . $this->studly($key) . 'Attribute'}($value);
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param  string $key
     * @return mixed
     */
    protected function getAttributeFromArray($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
    }

    /**
     * Get a plain attribute.
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        return $value;
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param  string $key
     * @return bool
     */
    public function hasSetMutator($key)
    {
        return method_exists($this, 'set' . $this->studly($key) . 'Attribute');
    }


    /**
     * Set a given attribute on the model.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        // First we will check for the presence of a mutator for the set operation
        // which simply lets the developers tweak the attribute as it is set on
        // the model, such as "json_encoding" an listing of data for storage.
        if ($this->hasSetMutator($key)) {
            $method = 'set' . $this->studly($key) . 'Attribute';

            return $this->{$method}($value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes) || $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }
    }

    /**
     * Dynamically retrieve attributes on the User model.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the User model.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }
}

<?php

namespace MrCat\SugarCrmOrmApi\Model;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use MrCat\SugarCrmOrmApi\Collection\Collection;
use MrCat\SugarCrmOrmApi\Repository\Bean;

class Builder
{
    /**
     * Option get Entries Api.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Response data Api.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Model Sugar Crm
     *
     * @var SugarCrm
     */
    protected $model;

    /**
     * Get Total Records
     *
     * @var integer
     */
    protected $total;

    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected $limit;

    /**
     * The list of fields to be returned in the results.
     *
     * @param array $fields
     *
     * @return $this
     */
    public function select(array $fields = [])
    {
        $this->options['select_fields'] = $fields;

        return $this;
    }

    public function scopeAll()
    {
        $this->options['scope_all'] = true;

        return $this;
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param int $page
     * @param int $limit
     *
     * @return $this
     */
    private function setPaginate($page = 0, $limit)
    {
        $this->max($limit);

        $this->page = $page;

        $this->limit = $limit;

        $this->offset($this->setPage($page, $limit));

        return $this;
    }

    /**
     * Calculate Offset.
     *
     * @param $page
     * @param $limit
     *
     * @return int
     */
    private function setPage($page, $limit)
    {
        if (isset($page) && !is_null($page) && $page > 1) {
            $page = $page - 1;

            return $page * $limit;
        }

        return 0;
    }

    /**
     * @param $module
     * @return array
     */
    public function getModuleFields($module)
    {
        return Bean::getModuleField($module);
    }

    /**
     * @param $methods
     */
    public function isContainScopeTrue($methods)
    {
        foreach ($methods as $method) {
            $this->model->$method();

            $relationsModel = $this->model->getRelations();

            $modelNameRelation = $relationsModel['class_relation'];

            $instanceRelation = new $modelNameRelation();

            $this->options['link_name_to_fields_array'][] = [
                $relationsModel['link_field_name'] => $instanceRelation->getFields(),
            ];

            $this->options['class_relation'][$relationsModel['link_field_name']] = $modelNameRelation;
        }
    }

    /**
     * @param $methods
     */
    public function isContainScopeFalse($methods)
    {
        $relationsModel = $this->model->getRelations();

        foreach ($methods as $method) {
            $modelNameRelation = $relationsModel['class_relation'];

            $instanceRelation = new $modelNameRelation();

            if (method_exists($instanceRelation, $method)) {
                $instanceRelation->{$method}();

                $relations = $instanceRelation->getRelations();

                $relation = $relations['class_relation'];

                $this->options['class_relation'][$relations['link_field_name']] = $relation;

                $instance = new $relation();

                $this->options['related_module_link_name_to_fields_array'][] = [
                    $relations['link_field_name'] => $instance->getFields(),
                ];
            }
        }
    }

    /**
     * Contain relations
     *
     * @param array $methods
     *
     * @return $this
     */
    public function contain(array $methods)
    {
        if (isset($this->options['scope_all']) && $this->options['scope_all']) {
            $this->isContainScopeTrue($methods);
        } else {
            $this->isContainScopeFalse($methods);
        }

        return $this;
    }

    /**
     * Get All Entries
     *
     * @return $this
     */
    public function all()
    {
        $data = Bean::all($this->model->getModule(), $this->options);

        if (isset($data['data'])) {
            $this->data = $data['data'];

            $this->total = $data['count']['total_count'];

            $this->addRelationClass($this->options);

            $this->data = $this->collection($this->data);
        }
    }

    /**
     * Get Relations
     *
     * @return Builder
     */
    public function relations()
    {
        if (isset($this->model->attributes['id'])) {

            $relation = $this->model->getRelations();

            $modelRelation = $relation['class_relation'];

            $instance = new $modelRelation();

            $options = array_merge($relation, $this->options);

            $data = Bean::getRelation(
                $this->model->getModule(),
                $this->model->attributes['id'],
                $options
            );

            $this->total = count($data);

            $this->data = $data;

            $this->addRelationClass($options);

            $this->model = $instance;

            if (count($this->data) > 0) {
                $this->data = $this->collection($this->data)->forPage($this->page, $this->limit);
            }
        }
    }

    /**
     * Get Data Query
     *
     * @return array
     */
    private function data()
    {
        if (isset($this->options['scope_all']) && $this->options['scope_all']) {
            $this->all();
        } else {
            $this->relations();
        }

        return $this->data;
    }

    /**
     * Add Class Attribute
     *
     * @param $options
     */
    private function addRelationClass($options)
    {
        if (isset($options['class_relation'])) {
            foreach ($this->data as $i => $value) {
                foreach (array_keys($value) as $key) {
                    if (is_array($options['class_relation'])) {
                        if (array_key_exists($key, $options['class_relation'])) {

                            $class = $options['class_relation'][$key];

                            $name = $this->getClassName($class);

                            $sugarRelationClass = new $class();

                            $this->data[$i][$name] = $this->collection($this->setTransformOptions($value[$key], $sugarRelationClass->getModule()), new $class());
//                            $this->data[$i][$name] = $this->collection($value[$key], new $class());

                            unset($this->data[$i][$key]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Strip the namespace from the class to get the actual class name
     * @param $class
     * @return string
     */
    private function getClassName($class)
    {
        if (preg_match('@\\\\([\w]+)$@', $class, $matches)) {
            $class = $matches[1];
        }

        return $class;
    }

    /**
     * Order query field
     *
     * @param string $field
     *
     * @return $this
     */
    public function orderBy($field = '')
    {
        $this->options['order_by'] = $field;

        return $this;
    }

    /**
     * The record offset from which to start.
     *
     * @param int $offset
     *
     * @return $this
     */
    private function offset($offset = 0)
    {
        $this->options['offset'] = $offset;

        return $this;
    }

    /**
     * The maximum number of results to return.
     *
     * @param int $max
     *
     * @return $this
     */
    private function max($max = 0)
    {
        $this->options['max_results'] = $max;

        return $this;
    }

    /**
     * If deleted records should be included in the results.
     *
     * @param $bool
     *
     * @return $this
     */
    public function withDeleted($bool = false)
    {
        $this->options['deleted'] = $bool;

        return $this;
    }

    /**
     * Execute a query for a single record by ID.
     *
     * @param $id
     * @return Collection
     */
    public function find($id)
    {
        $module = $this->model->getModule();

        $data = Bean::find($module, $id, [
            'select_fields' => $this->model->getFields(),
        ]);

        return $this->first($this->setTransformOptions([$data], $module)[0]);
    }

    /**
     * @param $data
     * @param $module
     * @return array
     */
    public function setTransformOptions($data, $module)
    {
//        $field = collect($this->getModuleFields($module))
//            ->whereIn("type", ["dynamicenum", "enum", "multienum"])
//            ->values();
//
//
//        $return = [];
//        foreach ($data as $value) {
//            $return[] = collect($value)->transform(function ($values, $key) use ($field) {
//                if (in_array($key, $field->pluck('name')->toArray())) {
//                    $options = collect(
//                        $field->where('name', $key)->first()["options"]
//                    )->filter(function ($v, $key) use ($values) {
//                        if (is_array($values)) {
//                            return in_array($key, $values);
//                        }
//                        return $key == $values;
//                    })->toArray();
//
//                    if (count(array_filter($options)) > 0) {
//                        return $options;
//                    }
//                    return null;
//                }
//                return $values;
//            })->toArray();
//        }

        return $data;
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
        $data = Bean::save($this->model->getModule(), $attributes);

        $attributes['id'] = $data;

        return $this->first($attributes);
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    public function setDocument(array $attributes = [])
    {
        $data = Bean::setDocument($this->model->getModule(), $attributes);

        $attributes['id'] = $data;

        return $this->first($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getDocument($id)
    {
        $data = Bean::getDocument($id);

        return $this->first($data);
    }

    /**
     * @param array $attributes
     * @return array
     */
    public function delete(array $attributes = [])
    {
        return Bean::delete($this->model->getModule(), $attributes);
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
    public function create(array  $attributes)
    {
        $data = Bean::create($this->model->getModule(), $attributes);

        return $data;
    }

    /**
     * Sets relationships between two records. You can relate multiple records to a single record using this.
     */
    public function setRelation($id, $moduleRelation, $relatedId, $data = [])
    {
        $data = Bean::setRelation(
            $this->model->getModule(),
            $id,
            $data,
            [
                'link_field_name' => $moduleRelation,
                'related_ids'     => $relatedId,
            ]);

        return $data;
    }

    /**
     * Get Firts Object Collection
     *
     * @param $data
     * @return mixed
     */
    public function first($data)
    {
        return $this->collection([$data])->first();
    }

    /**
     * The SQL WHERE clause without the word "where". You should remember to specify the table name for the fields to
     * avoid any ambiguous column errors.
     *
     * @param string $where
     *
     * @return $this
     */
    public function query($where = "")
    {
        $this->options['query'] = $where;

        return $this;
    }

    /**
     * @param $model
     * @param $data
     * @return \Illuminate\Support\Collection
     */
    protected function collection($data, $model = null)
    {
        if (is_null($model)) {
            $model = $this->model;
        }

        return Collection::make($model, $data)->get();
    }

    /**
     * Get Records All.
     */
    public function get()
    {
        return $this->data();
    }

    /**
     * Paginate the given query.
     *
     * @param  int      $perPage
     * @param  string   $pageName
     * @param  int|null $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @throws \InvalidArgumentException
     */
    public function paginate($perPage = null, $page = null, $pageName = 'page')
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $perPage = $perPage ?: 15;

        $this->setPaginate($page, $perPage);

        $results = $this->data();

        $total = $this->total;

        return new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * SuiteCrm constructor.
     *
     * @param $model
     */
    public function __construct(SugarCrm $model)
    {
        $this->model = $model;
        $this->orderBy();
        $this->query();
        $this->offset();
        $this->max();
        $this->withDeleted();
    }
}

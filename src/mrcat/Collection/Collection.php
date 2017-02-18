<?php
namespace MrCat\SugarCrmOrmApi\Collection;

use \Illuminate\Support\Collection as Collect;
use MrCat\SugarCrmOrmApi\Model\SugarCrm;

class Collection
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var SugarCrm
     */
    protected $model;

    /**
     * @return Collect
     */
    public function get()
    {
        return new Collect($this->toArrayModel());
    }

    /**
     * Collection constructor.
     * @param string $model
     * @param array  $data
     */
    public function __construct($model, $data = [])
    {
        $this->data = $data;
        $this->model = $model;
    }

    /**
     * @param $data
     * @return SugarCrm
     */
    public function model($data)
    {
        $model = get_class($this->model);
        $model = new $model($data);
        return $model;
    }

    /**
     * @return array
     */
    public function toArrayModel()
    {
        $data = [];
        if (!is_null($this->data)) {
            if (!$this->isMulti()) {
                array_push($data, $this->model($this->data));
            } else {
                foreach ($this->data as $item) {
                    array_push($data, $this->model($item));
                }
            }
        }

        return $data;
    }
    
    /**
     * @param SugarCrm $model
     * @param array    $data
     * @return static
     */
    public static function make(SugarCrm $model, array $data = [])
    {
        $instance = new static($model, $data);
        return $instance;
    }

    public function isMulti()
    {
        $data = array_filter($this->data, 'is_array');
        if (count($data) > 0) {
            return true;
        }
        return false;
    }
}
<?php

namespace MrCat\SugarCrmOrmApi\Render;

use MrCat\SugarCrmOrmApi\Capsule\ManagerSugarCrm;
use \Illuminate\Support\Collection as Collect;

class Form
{
    /**
     * Get Select Fields Form
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Name Module Api.
     *
     * @var string
     */
    protected $module = '';

    /**
     * Form constructor.
     *
     * @param       $module
     * @param array $fields
     */
    public function __construct($module, array $fields = [])
    {
        $this->fields = $fields;
        $this->module = $module;
    }

    /**
     * Select Fields Array
     *
     * @return Collect
     */
    private function selectFields()
    {
        $data = ManagerSugarCrm::get()->getModuleFields($this->module, true);

        $data = $this->arrayFiltersKey($data['module_fields'], $this->fields);

        usort($data, function ($a, $b) {

            $a = array_search($a['name'], $this->fields);

            $b = array_search($b['name'], $this->fields);

            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });

        return $this->collection($data);
    }

    /**
     * Get Keys Array Filters
     *
     * @param $filter
     * @param $data
     *
     * @return array
     */
    private function arrayFiltersKey($data, $filter)
    {
        return array_filter($data, function ($key) use ($filter) {
            return in_array($key, $filter);
        },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Generarte Form.
     *
     * @param array $parameters
     *
     * @return array
     */
    public function generateFields($parameters = [])
    {
        $data = [];

        $parameters = $this->setParameters($parameters);

        foreach ($this->selectFields() as $key => $value) {
            $data[$value['name']] = [
                'label' => $value['label'],
                'name' => $value['name'],
                'default' => $this->generateValuesDefaultLabel($value, $parameters['default']),
                'option' => $this->getOptionsForm($value['options']),
                'field' => GenerateHtml::input(
                    $value['type'],
                    [
                        'name' => $value['name'],
                        'attributes' => $this->setAttributesForm(
                            $value['required'],
                            $this->setAttributes(
                                $parameters['attributes'],
                                $value['name']
                            )
                        ),
                        'default' => $this->generateValuesDefault($value, $parameters['default']),
                        'options' => $this->getOptionsForm($value['options']),
                    ]),
            ];
        }

        return $data;
    }

    /**
     * Attributes For Input
     *
     * @param $required
     * @param $attributes
     *
     * @return array
     */
    private function setAttributesForm($required, $attributes)
    {
        $data = [];
        if ($required == 1) {
            $data = [
                'required' => 'required',
            ];
        }
        return array_merge($attributes, $data);
    }

    /**
     * Generate Values Fields Form.
     *
     * @param       $value
     * @param array $default
     *
     * @return mixed|null
     */
    private function generateValuesDefault($value, $default = [])
    {
        if (count($default) > 0 && array_key_exists($value['name'], $default)) {
            return $default[$value['name']];
        }
        return isset($value['default']) ? $value['default'] : null;
    }


    /**
     * Validate parameters
     *
     * @param $parameters
     * @return mixed
     */
    private function setParameters($parameters)
    {
        if (isset($parameters['default']) && !is_array($parameters['default']) || !isset($parameters['default'])) {
            $parameters['default'] = [];
        }

        if (isset($parameters['attributes']) && !is_array($parameters['attributes']) || !isset($parameters['attributes'])) {
            $parameters['attributes'] = [];
        }

        return $parameters;
    }

    /**
     * Generate Values Fields Form.
     *
     * @param       $value
     * @param array $default
     *
     * @return mixed|null
     */
    private function generateValuesDefaultLabel($value, $default = [])
    {
        if (count($default) > 0 && array_key_exists($value['name'], $default)) {
            if (in_array($value['type'], ['enum', 'multienum', 'radioenum'])) {
                if (is_array($default[$value['name']])) {
                    $data = [];

                    foreach ($default[$value['name']] as $item) {
                        $data[] = $value['options'][$item]['value'];
                    }

                    return $data;
                }

                return isset($value['options'][$default[$value['name']]]['value'])
                && $value['options'][$default[$value['name']]]['value'] != ''
                    ? $value['options'][$default[$value['name']]]['value']
                    : null;
            }

            return $default[$value['name']] != '' ? $default[$value['name']] : null;
        }

        return null;
    }

    /**
     * Genarete Options for Input Multiple
     *
     * @param $options
     *
     * @return array
     */
    private function getOptionsForm($options)
    {
        $data = [];
        foreach ($options as $key => $value) {
            $data[$value['name']] = $value['value'];
        }
        return $data;
    }


    public function setAttributes($data, $key)
    {
        if (isset($data[$key])) {
            return $data[$key];
        }
        if (isset($data['all'])) {
            return $data['all'];
        }

        return [];
    }

    /**
     * Generate Rules For Create Records
     *
     * @return array
     */
    public function rules()
    {
        $fields = [];
        foreach ($this->selectFields() as $key => $value) {
            if ($value['type'] !== 'id') {
                $fields[$value['name']] = [
                    $this->isRequiredField($value['required']),
                    key_exists($value['type'], $this->fieldsRules) ? $this->fieldsRules[$value['type']] : '',
                ];
            }
        }
        return $fields;
    }

    /**
     * Validate Field Required Rule
     *
     * @param $field
     *
     * @return string
     */
    private function isRequiredField($field)
    {
        if ($field == 1) {
            return 'required';
        }
        return '';
    }

    /**
     * @param $data
     * @return Collect
     */
    public function collection($data)
    {
        return new Collect($data);
    }
}

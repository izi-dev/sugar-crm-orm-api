<?php

namespace MrCat\SugarCrmOrmApi\Repository;

use MrCat\SugarCrmOrmApi\Capsule\ManagerSugarCrm;

class Bean implements BeanInterface
{
    /**
     * Get All Records.
     *
     * @param string $module
     * @param array  $options
     *
     * @return mixed
     */
    public static function all($module, array $options = [])
    {
        return ManagerSugarCrm::get()->getEntryList($module, $options);
    }

    /**
     * Find Entry Record.
     *
     * @param string $module
     * @param string $id
     * @param array  $options
     *
     * @return array
     */
    public static function find($module, $id, array $options = [])
    {
        return ManagerSugarCrm::get()->getEntry($module, $id, $options);
    }

    /**
     * Update Records
     *
     * @param string $module
     * @param array  $data
     *
     * @return array
     */
    public static function save($module, $data = [])
    {
        return ManagerSugarCrm::get()->setEntry($module, $data);
    }

    /**
     * Create New Records.
     *
     * @param string $module
     * @param array  $data
     *
     * @return array
     */
    public static function create($module, array $data = [])
    {
        return ManagerSugarCrm::get()->setEntries($module, $data);
    }

    /**
     * Get Relation
     *
     * @param string $module
     * @param string $id
     * @param array  $options
     *
     * @return array
     */
    public static function getRelation($module, $id, array $options = [])
    {
        return ManagerSugarCrm::get()->getRelationships($module, $id, $options);
    }

    /**
     * Set Relation
     *
     * @param       $module
     * @param       $id
     * @param array $data
     * @param array $options
     * @return array
     */
    public static function setRelation($module, $id, $data = [], $options = [])
    {
        return ManagerSugarCrm::get()->setRelationship($module, $id, $data, $options);
    }

    /**
     * Delete Record.
     *
     * @param string $module
     * @param array  $data
     *
     * @return array
     */
    public static function delete($module, $data = [])
    {
        return ManagerSugarCrm::get()->setEntry($module, array_merge($data, [
            'deleted' => 1,
        ]));
    }

    /**
     * @param       $module
     * @param array $data
     * @return array
     */
    public static function setDocument($module, array $data = [])
    {
        return ManagerSugarCrm::get()->setDocument($module, $data);
    }

    /**
     * Get Document for id
     *
     * @param $id
     * @return array
     */
    public static function getDocument($id)
    {
        return ManagerSugarCrm::get()->getDocument($id);
    }

    /**
     * @param $module
     * @return array
     */
    public static function getModuleField($module)
    {
        return ManagerSugarCrm::get()->getModuleFields($module);
    }
}

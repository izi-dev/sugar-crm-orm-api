<?php

namespace MrCat\SugarCrmOrmApi\Repository;


interface BeanInterface
{
    /**
     * Get All Records.
     *
     * @param string $module
     * @param array  $options
     *
     * @return mixed
     */
    public static function all($module, array $options = []);

    /**
     * Find Entry Record.
     *
     * @param string $module
     * @param string $id
     * @param array  $options
     *
     * @return array
     */
    public static function find($module, $id, array $options = []);

    /**
     * Update Records
     *
     * @param string $module
     * @param array  $data
     *
     * @return array
     */
    public static function save($module, $data = []);

    /**
     * Create New Records.
     *
     * @param string $module
     * @param array  $data
     *
     * @return array
     */
    public static function create($module, array $data = []);

    /**
     * Delete Record.
     *
     * @param string $module
     * @param array  $data
     *
     * @return array
     */
    public static function delete($module, $data = []);

    /**
     * Create New Relation.
     *
     * @param string $module
     * @param string $id
     * @param array  $options
     *
     * @return array
     */
    public static function getRelation($module, $id, array $options = []);

    /**
     * @param       $module
     * @param       $id
     * @param       $data
     * @param array $options
     */
    public static function setRelation($module, $id, $data = [], $options = []);

    /**
     * @param       $id
     */
    public static function getDocument($id);

    /**
     * @param       $module
     * @param array $options
     */
    public static function setDocument($module, array $options = []);

    /**
     * @param $module
     * @return array
     */
    public static function getModuleField($module);
}
<?php

namespace Pouzor\MongoDBBundle\Services;


class ArrayAccessor
{

    /**
     * Replace an array key with another while keeping value
     *
     * @param array $array
     * @param $key1
     * @param $key2
     * @return array
     */
    public static function replaceKey(&$array, $key1, $key2)
    {
        $keys = array_keys($array);
        $index = array_search($key1, $keys, true);

        if ($index !== false) {
            $keys[$index] = $key2;
            $array = array_combine($keys, $array);
        }

        return $array;
    }

    /**
     * Get a reference from an array using a path with dot notation. Ex:
     *
     * array(
     *      'person' => [
     *           'name' => 'john',
     *           'lastname' => 'smith',
     *           'childs' => [
     *               [ 'name' => 'victor'],
     *               [ 'name' => 'david']
     *           ]
     *      ]
     * )
     *
     * will return for:
     *
     * person.name = john
     * person.childs.0.name = victor
     *
     * @param array $data
     * @param $path
     * @param $default
     * @return array|null
     */
    public static function dget(array &$data, $path, $default = null)
    {
        $keys = explode('.', $path);
        foreach ($keys as $k) {
            if (isset($data[$k])) {
                $data =& $data[$k];
            } else {
                return $default;
            }
        }
        return $data;
    }

    /**
     * Set a reference from an array using a path with dot notation. Ex:
     *
     * array(
     *      'person' => [
     *           'name' => 'john',
     *           'lastname'='smith',
     *           'childs': [
     *               [ 'name' => 'victor'],
     *               [ 'name' => 'david']
     *           ]
     *      ]
     * )
     *
     * will set for:
     *
     * person.name = john
     * person.childs.0.name = victor
     *
     *
     * @param array $data
     * @param $path
     * @param $value
     */
    public static function dset(array &$data, $path, $value)
    {
        $keys = explode('.', $path);
        $last = array_pop($keys);
        foreach ($keys as $k) {
            if (isset($data[$k]) && is_array($data[$k])) {
                $data =& $data[$k];
            } else {
                $data[$k] = array();
                $data =& $data[$k];
            }
        }
        $data[$last] = $value;
    }

    /**
     * Count a reference from an array using a path with do notation. Ex:
     *
     * array(
     *      'person' => [
     *           'name' => 'john',
     *           'lastname'='smith',
     *           'childs': [
     *               [ 'name' => 'victor'],
     *               [ 'name' => 'david']
     *           ]
     *      ]
     * )
     *
     * will return for:
     *
     * person.name = 1
     * person.childs = 2
     *
     * @param array $data
     * @param $path
     * @return int|null
     */
    public static function dcount(array &$data, $path)
    {
        $keys = explode('.', $path);
        $last = array_pop($keys);
        foreach ($keys as $k) {
            if (isset($data[$k]) && is_array($data[$k])) {
                $data =& $data[$k];
            } else {
                return null;
            }
        }
        return isset($data[$last]) && is_array($data[$last]) ? count($data[$last]) : null;
    }

    /**
     * Deletes a reference from an array using a path with do notation. Ex:
     *
     * array(
     *      'person' => [
     *           'name' => 'john',
     *           'lastname'='smith',
     *           'childs': [
     *               [ 'name' => 'victor'],
     *               [ 'name' => 'david']
     *           ]
     *      ]
     * )
     *
     * will delete for:
     *
     * person.name
     *
     * will result in
     *
     * array(
     *      'person' => [
     *           'lastname'='smith',
     *           'childs': [
     *               [ 'name' => 'victor'],
     *               [ 'name' => 'david']
     *           ]
     *      ]
     * )
     *
     * @param array $data
     * @param $path
     */
    public static function ddel(array &$data, $path)
    {
        $keys = explode('.', $path);
        $last = array_pop($keys);
        foreach ($keys as $k) {
            if (isset($data[$k]) && is_array($data[$k])) {
                $data =& $data[$k];
            } else {
                return;
            }
        }
        unset($data[$last]);
    }

    /**
     * @param $array
     * @param $key
     * @param null $default
     * @return null
     */
    public static function get_key_exist($array, $key, $default = null) {

        if (isset($array[$key]))
            return $array[$key];

        return $default;

    }
}

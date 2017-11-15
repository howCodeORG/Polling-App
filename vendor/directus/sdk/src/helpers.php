<?php

if (!function_exists('get_user_timezone')) {
    function get_user_timezone()
    {
        return 'UTC';
    }
}

if (!function_exists('get_request_ip')) {
    function get_request_ip()
    {
        if (isset($_SERVER['X_FORWARDED_FOR'])) {
            return $_SERVER['X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['CLIENT_IP'])) {
            return $_SERVER['CLIENT_IP'];
        }

        return $_SERVER['REMOTE_ADDR'];
    }
}


if (!function_exists('to_name_value')) {
    function to_name_value($array, $keys = null)
    {
        $data = [];
        foreach ($array as $name => $value) {
            $row = ['name' => $name, 'value' => $value];
            if (isset($keys)) $row = array_merge($row, $keys);
            array_push($data, $row);
        }

        return $data;
    }
}

if (!function_exists('sorting_by_key')) {
    /**
     * Sorting callable helper
     *
     * @param string $key
     * @param string $order
     *
     * @return Closure
     */
    function sorting_by_key($key, $order = 'ASC')
    {
        return function ($a, $b) use ($key, $order) {
            if ($a[$key] === $b[$key]) {
                return 0;
            }

            $value = $a[$key] < $b[$key] ? -1 : 1;
            if ($order === 'DESC') {
                $value *= -1;
            }

            return $value;
        };
    }
}


<?php

namespace Directus\SDK;

class Container
{
    protected $data = [];

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function get($key)
    {
        if (!isset($this->data[$key])) {
            throw new \InvalidArgumentException(sprintf('Key "%s" is not defined.', $key));
        }

        $value = $this->data[$key];
        if (is_callable($value)) {
            $value = call_user_func_array($value, [$this]);
        }

        return $value;
    }

    public function singleton($key, $value)
    {
        $this->set($key, function($container) use ($value) {
            static $object = null;
            if ($object == null) {
                $object = call_user_func_array($value, [$container]);
            }

            return $object;
        });
    }
}
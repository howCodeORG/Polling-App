<?php

/**
 * Directus – <http://getdirectus.com>
 *
 * @link      The canonical repository – <https://github.com/directus/directus>
 * @copyright Copyright 2006-2016 RANGER Studio, LLC – <http://rangerstudio.com>
 * @license   GNU General Public License (v3) – <http://www.gnu.org/copyleft/gpl.html>
 */

namespace Directus\SDK\Response;
use Directus\Util\ArrayUtils;

/**
 * Entry
 *
 * @author Welling Guzmán <welling@rngr.org>
 */
class Entry implements ResponseInterface, \ArrayAccess
{
    /**
     * @var array
     */
    protected $data = null;

    /**
     * @var array
     */
    protected $rawData = null;

    /**
     * @var Entry
     */
    protected $metadata = null;

    /**
     * Entry constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->rawData = $data;
        if (!is_array($data)) {
            return;
        }

        // Support API 1.1
        if (isset($data['data']) && is_array($data['data'])) {
            $this->metadata = new static(ArrayUtils::get($data, 'meta', []));
            unset($data['meta']);

            $data = $data['data'];
        }

        foreach($data as $field => $value) {
            if (isset($value['rows']) || (isset($value['data']) && ArrayUtils::isNumericKeys($value['data']))) {
                $this->data[$field] = new EntryCollection($value);
            } else if (is_array($value)) {
                $this->data[$field] = new static($value);
            } else {
                $this->data[$field] = $value;
            }
        }
    }

    /**
     * Get the entry data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the entry metadata
     *
     * @return Entry
     */
    public function getMetaData()
    {
        return $this->metadata;
    }

    /**
     * @return array
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('Entry is read only');
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Entry is read only');
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        throw new \InvalidArgumentException('Invalid property: ' . $name);
    }

    public function __set($name, $value)
    {
        throw new \BadMethodCallException('Entry is read only');
    }

    /**
     * Gets the object representation of this entry
     *
     * @return object
     */
    public function jsonSerialize()
    {
        return (object) [
            'metadata' => $this->getMetaData(),
            'data' => $this->getData()
        ];
    }
}
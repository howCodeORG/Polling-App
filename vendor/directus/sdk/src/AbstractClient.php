<?php

namespace Directus\SDK;

use Directus\SDK\Response\EntryCollection;
use Directus\SDK\Response\Entry;
use Directus\Util\ArrayUtils;
use Directus\Util\StringUtils;

abstract class AbstractClient implements RequestsInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Creates a response object
     *
     * @param $data
     *
     * @return \Directus\SDK\Response\EntryCollection|\Directus\SDK\Response\Entry
     */
    protected function createResponseFromData($data)
    {
        if (isset($data['rows']) || (isset($data['data']) && ArrayUtils::isNumericKeys($data['data']))) {
            $response = new EntryCollection($data);
        } else {
            $response = new Entry($data);
        }

        return $response;
    }

    protected function processData($tableName, array $data)
    {
        $method = 'processDataOn' . StringUtils::underscoreToCamelCase($tableName, true);
        if (method_exists($this, $method)) {
            $data = call_user_func_array([$this, $method], [$data]);
        }

        return $data;
    }

    protected function processFile(File $file)
    {
        $data = $file->toArray();
        // Not container, we are using remote :)
        if (!$this->container) {
            return $data;
        }

        $Files = $this->container->get('files');

        if (!array_key_exists('type', $data) || strpos($data['type'], 'embed/') === 0) {
            $recordData = $Files->saveEmbedData($data);
        } else {
            $recordData = $Files->saveData($data['data'], $data['name']);
        }

        return array_merge($recordData, ArrayUtils::omit($data, ['data', 'name']));
    }

    protected function processDataOnDirectusUsers($data)
    {
        $data = ArrayUtils::omit($data, ['id', 'user', 'access_token', 'last_login', 'last_access', 'last_page']);
        if (ArrayUtils::has($data, 'password')) {
            // @TODO: use Auth hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => 12]);
        }

        if (ArrayUtils::has($data, 'avatar_file_id')) {
            $data['avatar_file_id'] = $this->processFile($data['avatar_file_id']);
        }

        return $data;
    }

    protected function processOnDirectusFiles($data)
    {
        // @NOTE: omit columns such id or user.
        $data = ArrayUtils::omit($data, ['id', 'user']);

        return $data;
    }

    protected function processDataOnDirectusPreferences($data)
    {
        // @NOTE: omit columns such id or user.
        $data = ArrayUtils::omit($data, ['id']);

        return $data;
    }

    protected function processDataOnDirectusBookmarks($data)
    {
        // @NOTE: omit columns such id or user.
        $data = ArrayUtils::omit($data, ['id']);

        return $data;
    }

    protected function parseColumnData($data)
    {
        $requiredAttributes = ['name', 'table', 'type', 'ui'];
        if (!ArrayUtils::contains($data, $requiredAttributes)) {
            throw new \Exception(sprintf('%s are required', implode(',', $requiredAttributes)));
        }

        $data = ArrayUtils::aliasKeys($data, [
            'table_name' => 'table',
            'column_name' => 'name',
            'data_type' => 'type',
            'char_length' => 'length'
        ]);

        return $data;
    }

    protected function requiredAttributes(array $attributes, array $data)
    {
        if (!ArrayUtils::contains($data, $attributes)) {
            throw new \Exception(sprintf('These attributes are required: %s', implode(',', $attributes)));
        }
    }

    protected function requiredOneAttribute(array $attributes, array $data)
    {
        if (!ArrayUtils::containsSome($data, $attributes)) {
            throw new \Exception(sprintf('These attributes are required: %s', implode(',', $attributes)));
        }
    }

    protected function getMessagesTo(array $data)
    {
        $isGroup = ArrayUtils::has($data, 'toGroup');
        $to = ArrayUtils::get($data, 'to', ArrayUtils::get($data, 'toGroup', []));

        if (!is_array($to)) {
            $to = explode(',', $to);
        }

        $toIds = array_map(function($id) use ($isGroup) {
            return sprintf('%s_%s', ($isGroup ? 1 : 0), $id);
        }, $to);

        return implode(',', $toIds);
    }
}
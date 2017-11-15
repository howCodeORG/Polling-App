<?php

/**
 * Directus – <http://getdirectus.com>
 *
 * @link      The canonical repository – <https://github.com/directus/directus>
 * @copyright Copyright 2006-2016 RANGER Studio, LLC – <http://rangerstudio.com>
 * @license   GNU General Public License (v3) – <http://www.gnu.org/copyleft/gpl.html>
 */

namespace Directus\SDK;

use Directus\Util\ArrayUtils;

/**
 * Client Remote
 *
 * @author Welling Guzmán <welling@rngr.org>
 */
class ClientRemote extends BaseClientRemote
{
    /**
     * @inheritdoc
     */
    public function getTables(array $params = [])
    {
        return $this->performRequest('GET', static::TABLE_LIST_ENDPOINT);
    }

    /**
     * @inheritdoc
     */
    public function getTable($tableName)
    {
        $path = $this->buildPath(static::TABLE_INFORMATION_ENDPOINT, $tableName);

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function getColumns($tableName, array $params = [])
    {
        $path = $this->buildPath(static::COLUMN_LIST_ENDPOINT, $tableName);

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function getColumn($tableName, $columnName)
    {
        $path = $this->buildPath(static::COLUMN_INFORMATION_ENDPOINT, [$tableName, $columnName]);

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function getItems($tableName, array $options = [])
    {
        $path = $this->buildPath(static::TABLE_ENTRIES_ENDPOINT, $tableName);

        return $this->performRequest('GET', $path, ['query' => $options]);
    }

    /**
     * @inheritdoc
     */
    public function getItem($tableName, $id, array $options = [])
    {
        $path = $this->buildPath(static::TABLE_ENTRY_ENDPOINT, [$tableName, $id]);

        return $this->performRequest('GET', $path, ['query' => $options]);
    }

    /**
     * @inheritdoc
     */
    public function getUsers(array $params = [])
    {
        return $this->getItems('directus_users', $params);
    }

    /**
     * @inheritdoc
     */
    public function getUser($id, array $params = [])
    {
        return $this->getItem($id, 'directus_users', $params);
    }

    /**
     * @inheritdoc
     */
    public function getGroups()
    {
        return $this->performRequest('GET', static::GROUP_LIST_ENDPOINT);
    }

    /**
     * @inheritdoc
     */
    public function getGroup($groupID)
    {
        $path = $this->buildPath(static::GROUP_INFORMATION_ENDPOINT, $groupID);

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function getGroupPrivileges($groupID)
    {
        $path = $this->buildPath(static::GROUP_PRIVILEGES_ENDPOINT, $groupID);

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function getFiles()
    {
        return $this->performRequest('GET', static::FILE_LIST_ENDPOINT);
    }

    /**
     * @inheritdoc
     */
    public function getFile($fileID)
    {
        $path = $this->buildPath(static::FILE_INFORMATION_ENDPOINT, $fileID);

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function getSettings()
    {
        return $this->performRequest('GET', static::SETTING_LIST_ENDPOINT);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsByCollection($collectionName)
    {
        $path = $this->buildPath(static::SETTING_COLLECTION_GET_ENDPOINT, $collectionName);

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function updateSettings($collection, array $data)
    {
        $path = $this->buildPath(static::SETTING_COLLECTION_UPDATE_ENDPOINT, $collection);

        return $this->performRequest('PUT', $path, [
            'body' => $data
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getMessages($userId = null)
    {
        if ($userId !== null) {
            $path = $this->buildPath(static::MESSAGES_USER_LIST_ENDPOINT, $userId);
        } else {
            $path = $this->buildPath(static::MESSAGES_LIST_ENDPOINT);
        }

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function getMessage($id)
    {
        $path = $this->buildPath(static::MESSAGES_GET_ENDPOINT, $id);

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function createItem($tableName, array $data)
    {
        $path = $this->buildPath(static::TABLE_ENTRY_CREATE_ENDPOINT, $tableName);
        $data = $this->processData($tableName, $data);

        return $this->performRequest('POST', $path, ['body' => $data]);
    }

    /**
     * @inheritdoc
     */
    public function updateItem($tableName, $id, array $data)
    {
        $path = $this->buildPath(static::TABLE_ENTRY_UPDATE_ENDPOINT, [$tableName, $id]);
        $data = $this->processData($tableName, $data);

        return $this->performRequest('PUT', $path, ['body' => $data]);
    }

    /**
     * @inheritdoc
     */
    public function deleteItem($tableName, $id, $hard = false)
    {
        $path = $this->buildPath(static::TABLE_ENTRY_DELETE_ENDPOINT, [$tableName, $id]);
        $options = [];

        if ($hard !== true) {
            $options = [
                'query' => ['soft' => true]
            ];
        }

        return $this->performRequest('DELETE', $path, $options);
    }

    /**
     * @inheritdoc
     */
    public function createUser(array $data)
    {
        return $this->createItem('directus_users', $data);
    }

    /**
     * @inheritdoc
     */
    public function updateUser($id, array $data)
    {
        return $this->updateItem('directus_users', $id, $data);
    }

    /**
     * @inheritdoc
     */
    public function deleteUser($ids, $hard = false)
    {
        return $this->deleteItem('directus_users', $ids, $hard);
    }

    /**
     * @inheritdoc
     */
    public function createFile(File $file)
    {
        $data = $this->processFile($file);

        return $this->performRequest('POST', static::FILE_CREATE_ENDPOINT, ['body' => $data]);
    }

    /**
     * @inheritdoc
     */
    public function updateFile($id, $data)
    {
        if ($data instanceof File) {
            $data = $data->toArray();
        }

        $data['id'] = $id;
        $path = $this->buildPath(static::FILE_UPDATE_ENDPOINT, $id);
        $data = $this->processData('directus_files', $data);

        return $this->performRequest('POST', $path, ['body' => $data]);
    }

    /**
     * @inheritdoc
     */
    public function deleteFile($id, $hard = false)
    {
        return $this->deleteItem('directus_files', $id, $hard);
    }

    public function createPreferences($data)
    {
        $this->requiredAttributes(['title', 'table_name'], $data);

        $tableName = ArrayUtils::get($data, 'table_name');
        $path = $this->buildPath(static::TABLE_PREFERENCES_ENDPOINT, $tableName);
        $data = $this->processData($tableName, $data);

        return $this->performRequest('POST', $path, ['body' => $data]);
    }

    /**
     * @inheritdoc
     */
    public function createBookmark($data)
    {
        $preferences = $this->createPreferences(ArrayUtils::pick($data, [
            'title', 'table_name', 'sort', 'status', 'search_string', 'sort_order', 'columns_visible'
        ]));

        $title = $preferences->title;
        $tableName = $preferences->table_name;
        $bookmarkData = [
            'section' => 'search',
            'title' => $title,
            'url' => 'tables/' . $tableName . '/pref/' . $title
        ];

        $path = $this->buildPath(static::BOOKMARKS_CREATE_ENDPOINT);
        $bookmarkData = $this->processData($tableName, $bookmarkData);

        return $this->performRequest('POST', $path, ['body' => $bookmarkData]);
    }

    /**
     * @inheritdoc
     */
    public function getBookmark($id)
    {
        $path = $this->buildPath(static::BOOKMARKS_READ_ENDPOINT, $id);

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function getBookmarks($userId = null)
    {
        if ($userId !== null) {
            $path = $this->buildPath(static::BOOKMARKS_USER_ENDPOINT, $userId);
        } else {
            $path = $this->buildPath(static::BOOKMARKS_ALL_ENDPOINT);
        }

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function createColumn($data)
    {
        $data = $this->parseColumnData($data);

        return $this->performRequest('POST', $this->buildPath(static::COLUMN_CREATE_ENDPOINT, $data['table_name']), [
            'body' => $data
        ]);
    }

    /**
     * @inheritdoc
     */
    public function createGroup(array $data)
    {
        return $this->performRequest('POST', static::GROUP_CREATE_ENDPOINT, [
            'body' => $data
        ]);
    }

    /**
     * @inheritdoc
     */
    public function createMessage(array $data)
    {
        $this->requiredAttributes(['from', 'message', 'subject'], $data);
        $this->requiredOneAttribute(['to', 'toGroup'], $data);

        $data['recipients'] = $this->getMessagesTo($data);
        ArrayUtils::remove($data, ['to', 'toGroup']);

        return $this->performRequest('POST', static::MESSAGES_CREATE_ENDPOINT, [
            'body' => $data
        ]);
    }

    /**
     * @inheritdoc
     */
    public function sendMessage(array $data)
    {
        return $this->createMessage($data);
    }

    /**
     * @inheritdoc
     */
    public function createPrivileges(array $data)
    {
        $this->requiredAttributes(['group_id', 'table_name'], $data);

        return $this->performRequest('POST', static::GROUP_PRIVILEGES_CREATE_ENDPOINT, [
            'body' => $data
        ]);
    }

    public function createTable($name, array $params = [])
    {
        $data = [
            'addTable' => true,
            'table_name' => $name
        ];

        return $this->performRequest('POST', static::TABLE_CREATE_ENDPOINT, [
            'body' => $data
        ]);
    }

    /**
     * @inheritdoc
     */
    public function createColumnUIOptions(array $data)
    {
        $this->requiredAttributes(['table', 'column', 'ui', 'options'], $data);

        $path = $this->buildPath(static::COLUMN_OPTIONS_CREATE_ENDPOINT, [
            $data['table'],
            $data['column'],
            $data['ui']
        ]);

        $data = ArrayUtils::get($data, 'options');

        return $this->performRequest('POST', $path, [
            'body' => $data
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getPreferences($table, $user = null)
    {
        return $this->performRequest('POST', $this->buildPath(static::TABLE_PREFERENCES_ENDPOINT, $table));
    }

    /**
     * @inheritdoc
     */
    public function deleteBookmark($id, $hard = false)
    {
        return $this->deleteItem('directus_bookmarks', $id, $hard);
    }

    /**
     * @inheritdoc
     */
    public function deleteColumn($name, $table)
    {
        $path = $this->buildPath(static::COLUMN_DELETE_ENDPOINT, [$name, $table]);

        return $this->performRequest('DELETE', $path);
    }

    /**
     * @inheritdoc
     */
    public function deleteGroup($id, $hard = false)
    {
        return $this->deleteItem('directus_groups', $id, $hard);
    }

    /**
     * @inheritdoc
     */
    public function deleteTable($name)
    {
        $path = $this->buildPath(static::TABLE_DELETE_ENDPOINT, $name);

        return $this->performRequest('DELETE', $path);
    }

    /**
     * @inheritdoc
     */
    public function getActivity(array $params = [])
    {
        $path = $this->buildPath(static::ACTIVITY_GET_ENDPOINT);

        return $this->performRequest('GET', $path, [
            'query' => $params
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getRandom(array $options = [])
    {
        $path = $this->buildPath(static::UTILS_RANDOM_ENDPOINT);

        return $this->performRequest('POST', $path, ['body' => $options]);
    }

    /**
     * @inheritdoc
     */
    public function getHash($string, array $options = [])
    {
        $path = $this->buildPath(static::UTILS_HASH_ENDPOINT);

        $data = [
            'string' => $string
        ];

        if (ArrayUtils::has($options, 'hasher')) {
            $data['hasher'] = ArrayUtils::pull($options, 'hasher');
        }

        $data['options'] = $options;

        return $this->performRequest('POST', $path, ['body' => $data]);
    }
}

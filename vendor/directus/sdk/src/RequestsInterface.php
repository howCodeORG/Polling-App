<?php

/**
 * Directus – <http://getdirectus.com>
 *
 * @link      The canonical repository – <https://github.com/directus/directus>
 * @copyright Copyright 2006-2016 RANGER Studio, LLC – <http://rangerstudio.com>
 * @license   GNU General Public License (v3) – <http://www.gnu.org/copyleft/gpl.html>
 */

namespace Directus\SDK;

use Directus\SDK\Response\Entry;
use Directus\SDK\Response\EntryCollection;

/**
 * Requests Interface
 *
 * @author Welling Guzmán <welling@rngr.org>
 */
interface RequestsInterface
{
    /**
     * Gets list of all tables
     *
     * @param array $params
     *
     * @return EntryCollection
     */
    public function getTables(array $params = []);

    /**
     * Gets the details of the given table
     *
     * @param $tableName
     *
     * @return Entry
     */
    public function getTable($tableName);

    /**
     * Gets columns of a given table
     *
     * @param $tableName
     * @param $params
     *
     * @return EntryCollection
     */
    public function getColumns($tableName, array $params = []);

    /**
     * Gets the details of a given table's column
     *
     * @param $tableName
     * @param $columnName
     *
     * @return Entry
     */
    public function getColumn($tableName, $columnName);

    /**
     * Fetch Items from a given table
     *
     * @param string $tableName
     * @param array $options
     *
     * @return EntryCollection
     */
    public function getItems($tableName, array $options = []);

    /**
     * Get an entry in a given table by the given ID
     *
     * @param mixed $id
     * @param string $tableName
     * @param array $options
     *
     * @return Entry
     */
    public function getItem($tableName, $id, array $options = []);

    /**
     * Gets the list of users
     *
     * @param array $params
     *
     * @return EntryCollection
     */
    public function getUsers(array $params = []);

    /**
     * Gets a user by the given id
     *
     * @param $id
     * @param array $params
     *
     * @return Entry
     */
    public function getUser($id, array $params = []);

    /**
     * Gets a list of User groups
     *
     * @return EntryCollection
     */
    public function getGroups();

    /**
     * Gets the information of a given user group
     *
     * @param $groupID
     *
     * @return Entry
     */
    public function getGroup($groupID);

    /**
     * Get a given group privileges
     *
     * @param $groupID
     *
     * @return EntryCollection
     */
    public function getGroupPrivileges($groupID);

    /**
     * Gets a list fo files
     *
     * @return EntryCollection
     */
    public function getFiles();

    /**
     * Gets the information of a given file ID
     *
     * @param $fileID
     *
     * @return Entry
     */
    public function getFile($fileID);

    /**
     * Gets all settings
     *
     * @return object
     */
    public function getSettings();

    /**
     * Gets all settings in a given collection name
     *
     * @param $collectionName
     *
     * @return EntryCollection
     */
    public function getSettingsByCollection($collectionName);

    /**
     * Updates settings in the given collection
     *
     * @param $collection
     * @param $data
     *
     * @return Entry
     */
    public function updateSettings($collection, array $data);

    /**
     * Gets messages with the given ID
     *
     * @param $id
     *
     * @return Entry
     */
    public function getMessage($id);

    /**
     * Gets all messages from the given user ID
     *
     * @param $userId
     *
     * @return EntryCollection
     */
    public function getMessages($userId = null);

    /**
     * Create a new item in the given table name
     *
     * @param $tableName
     * @param array $data
     *
     * @return Entry
     */
    public function createItem($tableName, array $data);

    /**
     * Update the item of the given table and id
     *
     * @param $tableName
     * @param $id
     * @param array $data
     *
     * @return mixed
     */
    public function updateItem($tableName, $id, array $data);

    /**
     * Deletes the given item id(s)
     *
     * @param string $tableName
     * @param string|array|Entry|EntryCollection $ids
     * @param bool $hard
     *
     * @return int
     */
    public function deleteItem($tableName, $ids, $hard = false);

    /**
     * Creates a new user
     *
     * @param array $data
     *
     * @return Entry
     */
    public function createUser(array $data);

    /**
     * Updates the given user id
     *
     * @param $id
     * @param array $data
     *
     * @return mixed
     */
    public function updateUser($id, array $data);

    /**
     * Deletes the given user id(s)
     *
     * @param string|array|Entry|EntryCollection $ids
     * @param bool $hard
     *
     * @return int
     */
    public function deleteUser($ids, $hard = false);

    /**
     * Creates a new file
     *
     * @param File $file
     *
     * @return Entry
     */
    public function createFile(File $file);

    /**
     * Updates the given file id
     *
     * @param $id
     * @param array|File $data
     *
     * @return mixed
     */
    public function updateFile($id, $data);

    /**
     * Deletes the given file id(s)
     *
     * @param string|array|Entry|EntryCollection $ids
     * @param bool $hard
     *
     * @return int
     */
    public function deleteFile($ids, $hard = false);

    /**
     * Creates a new Bookmark
     *
     * @param $data
     *
     * @return Entry
     */
    public function createBookmark($data);

    /**
     * Gets a Bookmark with the given id
     *
     * @param int $id
     *
     * @return Entry
     */
    public function getBookmark($id);

    /**
     * Gets a Bookmarks
     *
     * @param int $userId
     *
     * @return Entry
     */
    public function getBookmarks($userId = null);

    /**
     * Creates a new Table preferences
     *
     * @param $data
     *
     * @return Entry
     */
    public function createPreferences($data);

    /**
     * Creates a new Column
     *
     * @param $data
     *
     * @return Entry
     */
    public function createColumn($data);

    /**
     * Creates a new group
     *
     * @param $data
     *
     * @return Entry
     */
    public function createGroup(array $data);

    /**
     * Creates new message
     *
     * @param array $data
     *
     * @return Entry
     */
    public function createMessage(array $data);

    /**
     * Sends a new message
     *
     * Alias of createMessage
     *
     * @param array $data
     *
     * @return Entry
     */
    public function sendMessage(array $data);

    /**
     * Creates a new privileges/permissions
     *
     * @param array $data
     *
     * @return Entry
     */
    public function createPrivileges(array $data);

    /**
     * Creates
     *
     * @param $name
     * @param array $data
     *
     * @return Entry
     */
    public function createTable($name, array $data = []);

    /**
     * Creates/Updates column ui options
     *
     * @param array $data
     *
     * @return Entry
     */
    public function createColumnUIOptions(array $data);

    /**
     * Gets preferences
     *
     * @param $table
     * @param $user
     *
     * @return Entry
     */
    public function getPreferences($table, $user);

    /**
     * Deletes a bookmark
     *
     * @param $id
     * @param bool $hard
     *
     * @return Entry
     */
    public function deleteBookmark($id, $hard = false);

    /**
     * Deletes a column
     *
     * @param $name
     * @param $table
     *
     * @return Entry
     */
    public function deleteColumn($name, $table);

    /**
     * Deletes a group
     *
     * @param $id
     * @param bool $hard
     *
     * @return Entry
     */
    public function deleteGroup($id, $hard = false);

    /**
     * Deletes a table
     *
     * @param $name
     *
     * @return Entry
     */
    public function deleteTable($name);

    /**
     * Gets activity records
     *
     * @param array $params
     *
     * @return Entry
     */
    public function getActivity(array $params = []);

    /**
     * Gets a random alphanumeric string
     *
     * @param array $options
     *
     * @return Entry
     */
    public function getRandom(array $options = []);

    /**
     * Gets a hashed value from the given string
     *
     * @param string $string
     * @param array $options
     *
     * @return Entry
     */
    public function getHash($string, array $options = []);
}

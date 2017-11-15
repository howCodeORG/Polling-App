<?php

/**
 * Directus – <http://getdirectus.com>
 *
 * @link      The canonical repository – <https://github.com/directus/directus>
 * @copyright Copyright 2006-2016 RANGER Studio, LLC – <http://rangerstudio.com>
 * @license   GNU General Public License (v3) – <http://www.gnu.org/copyleft/gpl.html>
 */

namespace Directus\SDK;

use Directus\Config\Config;
use Directus\Database\Connection;
use Directus\Database\TableGateway\BaseTableGateway;
use Directus\Database\TableGateway\DirectusSettingsTableGateway;
use Directus\Filesystem\Files;
use Directus\Filesystem\Filesystem;
use Directus\Filesystem\FilesystemFactory;
use Directus\Hash\HashManager;
use Directus\Hook\Emitter;
use Directus\Util\ArrayUtils;
use Directus\Database\TableSchema;

/**
 * Client
 *
 * @author Welling Guzmán <welling@rngr.org>
 */
class ClientFactory
{
    /**
     * @var ClientFactory
     */
    protected static $instance = null;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $config = [];

    protected $settings = [];

    protected $emitter;

    protected $files;

    /**
     * @var array
     */
    protected $defaultConfig = [
        'environment' => 'development',
        'database' => [
            'driver'  => 'pdo_mysql',
            'charset' => 'utf8mb4',
            'port'    => 3306
        ],
        'status' => [
            'column_name' => 'status',
            'deleted_value' => 0,
            'active_value' => 1,
            'draft_value' => 2,
            'mapping' => [
                0 => [
                    'name' => 'Deleted',
                    'text_color' => '#FFFFFF',
                    'background_color' => '#F44336',
                    'subdued_in_listing' => true,
                    'show_listing_badge' => true,
                    'hidden_globally' => true,
                    'hard_delete' => false,
                    'published' => false,
                    'sort' => 3
                ],
                1 => [
                    'name' => 'Published',
                    'text_color' => '#FFFFFF',
                    'background_color' => '#3498DB',
                    'subdued_in_listing' => false,
                    'show_listing_badge' => false,
                    'hidden_globally' => false,
                    'hard_delete' => false,
                    'published' => true,
                    'sort' => 1
                ],
                2 => [
                    'name' => 'Draft',
                    'text_color' => '#999999',
                    'background_color' => '#EEEEEE',
                    'subdued_in_listing' => true,
                    'show_listing_badge' => true,
                    'hidden_globally' => false,
                    'hard_delete' => false,
                    'published' => false,
                    'sort' => 2
                ]
            ]
        ],
        'filesystem' => [
            'adapter' => 'local',
            // By default media directory are located at the same level of directus root
            // To make them a level up outsite the root directory
            // use this instead
            // Ex: 'root' => realpath(BASE_PATH.'/../storage/uploads'),
            // Note: BASE_PATH constant doesn't end with trailing slash
            'root' => '/storage/uploads',
            // This is the url where all the media will be pointing to
            // here all assets will be (yourdomain)/storage/uploads
            // same with thumbnails (yourdomain)/storage/uploads/thumbs
            'root_url' => '/storage/uploads',
            'root_thumb_url' => '/storage/uploads/thumbs',
            //   'key'    => 's3-key',
            //   'secret' => 's3-key',
            //   'region' => 's3-region',
            //   'version' => 's3-version',
            //   'bucket' => 's3-bucket'
        ],
    ];

    /**
     * @param $userToken
     * @param array $options
     *
     * @return ClientLocal|ClientRemote
     */
    public static function create($userToken, $options = [])
    {
        if (static::$instance == null) {
            static::$instance = new static;
        }

        if (!is_array($userToken)) {
            $newClient = static::$instance->createRemoteClient($userToken, $options);
        } else {
            $options = $userToken;
            $newClient = static::$instance->createLocalClient($options);
        }

        return $newClient;
    }

    /**
     * Creates a new local client instance
     *
     * @param array $options
     *
     * @return ClientLocal
     */
    public function createLocalClient(array $options)
    {
        $this->container = $container = new Container();

        $options = ArrayUtils::defaults($this->defaultConfig, $options);
        $container->set('config', new Config($options));

        $dbConfig = ArrayUtils::get($options, 'database', []);

        $config = ArrayUtils::omit($options, 'database');
        // match the actual directus status mapping config key
        $config['statusMapping'] = $config['status']['mapping'];
        unset($config['status']['mapping']);

        if (!defined('STATUS_DELETED_NUM')) {
            define('STATUS_DELETED_NUM', ArrayUtils::get($config, 'status.deleted_value', 0));
        }

        if (!defined('STATUS_ACTIVE_NUM')) {
            define('STATUS_ACTIVE_NUM', ArrayUtils::get($config, 'status.active_value', 1));
        }

        if (!defined('STATUS_DRAFT_NUM')) {
            define('STATUS_DRAFT_NUM', ArrayUtils::get($config, 'status.draft_value', 2));
        }

        if (!defined('STATUS_COLUMN_NAME')) {
            define('STATUS_COLUMN_NAME', ArrayUtils::get($config, 'status.column_name', 'status'));
        }

        if (!defined('DIRECTUS_ENV')) {
            define('DIRECTUS_ENV', ArrayUtils::get($config, 'environment', 'development'));
        }

        $connection = new Connection($dbConfig);
        $connection->connect();
        $container->set('connection', $connection);
        $container->set('zendDb', $connection);

        $acl = new \Directus\Permissions\Acl();
        $acl->setUserId(1);
        $acl->setGroupId(1);
        $acl->setGroupPrivileges([
            '*' => [
                'id' => 1,
                'table_name' => '*',
                'group_id' => 1,
                'read_field_blacklist' => [],
                'write_field_blacklist' => [],
                'nav_listed' => 1,
                'status_id' => 0,
                'allow_view' => 2,
                'allow_add' => 1,
                'allow_edit' => 2,
                'allow_delete' => 2,
                'allow_alter' => 1
            ]
        ]);
        $container->set('acl', $acl);

        $schema = new \Directus\Database\Schemas\Sources\MySQLSchema($connection);
        $container->set('schemaSource', $schema);
        $schema = new \Directus\Database\SchemaManager($schema);
        $container->set('schemaManager', $schema);
        TableSchema::setSchemaManagerInstance($schema);
        TableSchema::setAclInstance($acl);
        TableSchema::setConnectionInstance($connection);
        TableSchema::setConfig(new Config($config));

        $container->singleton('emitter', function() {
            return $this->getEmitter();
        });
        $container->set('files_settings', function(Container $container) {
            $adapter = $container->get('connection');
            $acl = $container->get('acl');
            $Settings = new DirectusSettingsTableGateway($adapter, $acl);

            return $Settings->fetchCollection('files', [
                'thumbnail_size', 'thumbnail_quality', 'thumbnail_crop_enabled'
            ]);
        });
        $container->set('app.settings', function (Container $container) {
            $DirectusSettingsTableGateway = new \Zend\Db\TableGateway\TableGateway('directus_settings', $container->get('zendDb'));
            $rowSet = $DirectusSettingsTableGateway->select();

            $settings = [];
            foreach ($rowSet as $setting) {
                $settings[$setting['collection']][$setting['name']] = $setting['value'];
            }

            return $settings;
        });

        $container->singleton('hashManager', function () {
            return new HashManager();
        });

        $container->singleton('files', function() {
            return $this->getFiles();
        });

        BaseTableGateway::setHookEmitter($container->get('emitter'));
        BaseTableGateway::setContainer($container);

        $client = new ClientLocal($connection);
        $client->setContainer($container);

        return $client;
    }

    public function getFiles()
    {
        static $files = null;
        if ($files == null) {
            $config = $this->container->get('config');
            $filesystemConfig = $config->get('filesystem', []);
            $filesystem = new Filesystem(FilesystemFactory::createAdapter($filesystemConfig));
            $settings = $this->container->get('files_settings');
            $emitter = $this->container->get('emitter');
            $files = new Files($filesystem, $filesystemConfig, $settings, $emitter);
        }

        return $files;
    }

    protected function getEmitter()
    {
        static $emitter = null;
        if ($emitter) {
            return $emitter;
        }

        $emitter = new Emitter();
        $acl = $this->container->get('acl');
        $adapter = $this->container->get('connection');

        $emitter->addAction('table.insert.directus_groups', function ($data) use ($acl, $adapter) {
            $privilegesTable = new DirectDirectusPrivilegesTableGateway($adapter, $acl);

            $privilegesTable->insertPrivilege([
                'group_id' => $data['id'],
                'allow_view' => 1,
                'allow_add' => 0,
                'allow_edit' => 1,
                'allow_delete' => 0,
                'allow_alter' => 0,
                'table_name' => 'directus_users',
                'read_field_blacklist' => 'token',
                'write_field_blacklist' => 'group,token'
            ]);
        });

        $emitter->addFilter('table.insert.directus_files:before', function ($payload) {
            unset($payload['data']);
            $payload['user'] = 1;

            return $payload;
        });

        // Add file url and thumb url
        $config = $this->container->get('config');
        $files = $this->container->get('files');
        $emitter->addFilter('table.select', function ($payload) use ($config, $files) {
            $selectState = $payload->attribute('selectState');

            if ($selectState['table'] == 'directus_files') {
                $rows = $payload->toArray();
                foreach ($rows as &$row) {
                    $fileURL = $config->get('filesystem.root_url', '');
                    $thumbnailURL = $config->get('filesystem.root_thumb_url', '');
                    $thumbnailFilenameParts = explode('.', $row['name']);
                    $thumbnailExtension = array_pop($thumbnailFilenameParts);

                    $row['url'] = $fileURL . '/' . $row['name'];
                    if (in_array($thumbnailExtension, ['tif', 'tiff', 'psd', 'pdf'])) {
                        $thumbnailExtension = 'jpg';
                    }

                    $thumbnailFilename = $row['id'] . '.' . $thumbnailExtension;
                    $row['thumbnail_url'] = $thumbnailURL . '/' . $thumbnailFilename;

                    // filename-ext-100-100-true.jpg
                    // @TODO: This should be another hook listener
                    $row['thumbnail_url'] = null;
                    $filename = implode('.', $thumbnailFilenameParts);
                    if ($row['type'] == 'embed/vimeo') {
                        $oldThumbnailFilename = $row['name'] . '-vimeo-220-124-true.jpg';
                    } else {
                        $oldThumbnailFilename = $filename . '-' . $thumbnailExtension . '-160-160-true.jpg';
                    }

                    // 314551321-vimeo-220-124-true.jpg
                    // hotfix: there's not thumbnail for this file
                    $row['old_thumbnail_url'] = $thumbnailURL . '/' . $oldThumbnailFilename;
                    $row['thumbnail_url'] = $thumbnailURL . '/' . $thumbnailFilename;

                    /*
                    $embedManager = Bootstrap::get('embedManager');
                    $provider = $embedManager->getByType($row['type']);
                    $row['html'] = null;
                    if ($provider) {
                        $row['html'] = $provider->getCode($row);
                    }
                    */
                }

                $payload->replace($rows);
            }

            return $payload;
        });

        return $emitter;
    }

    /**
     * Create a new remote client instance
     *
     * @param $userToken
     * @param array $options
     *
     * @return ClientRemote
     */
    public function createRemoteClient($userToken, array $options = [])
    {
        return new ClientRemote($userToken, $options);
    }
}

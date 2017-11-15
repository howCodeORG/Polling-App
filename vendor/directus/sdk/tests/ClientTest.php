<?php

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Directus\SDK\ClientRemote
     */
    protected $client;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    public function setUp()
    {
        parent::setUp();

        $this->client = \Directus\SDK\ClientFactory::create('token');
        $this->httpClient = $this->client->getHTTPClient();
    }

    public function testClient()
    {
        $client = $this->client;
        $this->assertInstanceOf('\GuzzleHttp\Client', $client->getDefaultHTTPClient());
        $this->assertInstanceOf('\GuzzleHttp\Client', $client->getHTTPClient());
        $this->assertSame('token', $client->getAccessToken());

        $client->setAccessToken('newToken');
        $this->assertSame('newToken', $client->getAccessToken());

        $this->assertEquals(1, $client->getAPIVersion());
        $this->assertNull($client->getInstanceKey());
    }

    public function testOptions()
    {
        $client = \Directus\SDK\ClientFactory::create('token', [
            'base_url' => 'http://directus.local'
        ]);

        $this->assertSame('http://directus.local/api/1', $client->getBaseEndpoint());

        $client = \Directus\SDK\ClientFactory::create('token', [
            'base_url' => 'http://directus.local',
            'version' => 2
        ]);

        $this->assertSame('http://directus.local/api/2', $client->getBaseEndpoint());

        $this->assertEquals(2, $client->getAPIVersion());
    }

    public function testHostedClient()
    {
        $instanceKey = 'account--instance';
        $client = \Directus\SDK\ClientFactory::create('token', ['instance_key' => $instanceKey]);

        $expectedBaseUrl = 'https://'.$instanceKey.'.directus.io';
        $expectedEndpoint = $expectedBaseUrl . '/api/1';
        $this->assertSame($expectedBaseUrl, $client->getBaseUrl());
        $this->assertSame($expectedEndpoint, $client->getBaseEndpoint());

        $client = \Directus\SDK\ClientFactory::create('token', [
            'base_url' => 'http://directus.local',
            'instance_key' => $instanceKey
        ]);

        $this->assertSame($expectedEndpoint, $client->getBaseEndpoint());
        $this->assertEquals($instanceKey, $client->getInstanceKey());
    }

    public function testRequest()
    {
        $client = $this->client;
        $path = $client->buildPath($client::TABLE_ENTRIES_ENDPOINT, 'articles');
        $request = $this->client->buildRequest('GET', $path);
        $this->assertInstanceOf('\GuzzleHttp\Message\Request', $request);
    }

    public function testEndpoints()
    {
        $client =  $this->client;

        $endpoint = $this->client->buildPath($client::TABLE_LIST_ENDPOINT);
        $this->assertSame($endpoint, 'tables');

        $endpoint = $this->client->buildPath($client::TABLE_INFORMATION_ENDPOINT, 'articles');
        $this->assertSame($endpoint, 'tables/articles');

        $endpoint = $this->client->buildPath($client::TABLE_ENTRIES_ENDPOINT, 'articles');
        $this->assertSame($endpoint, 'tables/articles/rows');

        $endpoint = $this->client->buildPath($client::TABLE_ENTRIES_ENDPOINT, ['articles']);
        $this->assertSame($endpoint, 'tables/articles/rows');

        $endpoint = $this->client->buildPath($client::TABLE_ENTRY_ENDPOINT, ['articles', 1]);
        $this->assertSame($endpoint, 'tables/articles/rows/1');

        $endpoint = $this->client->buildPath($client::TABLE_PREFERENCES_ENDPOINT, 'articles');
        $this->assertSame($endpoint, 'tables/articles/preferences');

        $endpoint = $this->client->buildPath($client::COLUMN_LIST_ENDPOINT, ['articles']);
        $this->assertSame($endpoint, 'tables/articles/columns');

        $endpoint = $this->client->buildPath($client::COLUMN_INFORMATION_ENDPOINT, ['articles', 'body']);
        $this->assertSame($endpoint, 'tables/articles/columns/body');

        $endpoint = $this->client->buildPath($client::GROUP_LIST_ENDPOINT);
        $this->assertSame($endpoint, 'groups');

        $endpoint = $this->client->buildPath($client::GROUP_INFORMATION_ENDPOINT, 1);
        $this->assertSame($endpoint, 'groups/1');

        $endpoint = $this->client->buildPath($client::GROUP_PRIVILEGES_ENDPOINT, 1);
        $this->assertSame($endpoint, 'privileges/1');

        $endpoint = $this->client->buildPath($client::FILE_LIST_ENDPOINT);
        $this->assertSame($endpoint, 'files');

        $endpoint = $this->client->buildPath($client::FILE_INFORMATION_ENDPOINT, 1);
        $this->assertSame($endpoint, 'files/1');

        $endpoint = $this->client->buildPath($client::SETTING_LIST_ENDPOINT);
        $this->assertSame($endpoint, 'settings');

        $endpoint = $this->client->buildPath($client::SETTING_COLLECTION_GET_ENDPOINT, 'global');
        $this->assertSame($endpoint, 'settings/global');
    }

    public function testFetchTables()
    {
        $this->mockResponse('fetchTables.txt');
        $response = $this->client->getTables();
        $this->assertInstanceOf('\Directus\SDK\Response\Entry', $response);

        $this->mockResponse('fetchTablesEmpty.txt');
        $response = $this->client->getTables();
        $this->assertInstanceOf('\Directus\SDK\Response\Entry', $response);
    }

    public function testFetchTableInformation()
    {
        $this->mockResponse('fetchTableInformation.txt');
        $response = $this->client->getTable('articles');
        $this->assertInstanceOf('\Directus\SDK\Response\Entry', $response);

        $this->mockResponse('fetchTableInformationEmpty.txt');
        $response = $this->client->getTable('articles');
        $this->assertFalse($response->getRawData());
    }

    public function testFetchTablePreferences()
    {
        $this->mockResponse('fetchTablePreferences.txt');
        $response = $this->client->getTable('articles');
        $this->assertInternalType('object', $response);

        $this->mockResponse('fetchTablePreferencesEmpty.txt');
        $response = $this->client->getTable('articles');
        $this->assertFalse($response->getRawData());
    }

    public function testFetchItems()
    {
        $this->mockResponse('fetchItems.txt');
        $response = $this->client->getUsers();

        $this->assertInstanceOf('\Directus\SDK\Response\EntryCollection', $response);
        $this->assertArrayHasKey('Active', $response->getMetaData()->getRawData());
        $this->assertArrayHasKey('Draft', $response->getMetaData()->getRawData());
        $this->assertArrayHasKey('Delete', $response->getMetaData()->getRawData());

        $this->mockResponse('fetchItemsEmpty.txt');
        $response = $this->client->getUsers();

        $this->assertInstanceOf('\Directus\SDK\Response\EntryCollection', $response);
        $this->assertArrayHasKey('Active', $response->getMetaData()->getRawData());
        $this->assertArrayHasKey('Draft', $response->getMetaData()->getRawData());
        $this->assertArrayHasKey('Delete', $response->getMetaData()->getRawData());
    }

    public function testFetchItem()
    {
        $this->mockResponse('fetchItem.txt');
        $response = $this->client->getUser(3);
        $this->assertInternalType('object', $response);

        $this->mockResponse('fetchItemEmpty.txt');
        $response = $this->client->getUser(3);
        $this->assertNull($response->getRawData());
    }

    public function testFetchColumns()
    {
        $this->mockResponse('fetchColumns.txt');
        $response = $this->client->getColumns('articles');
        $this->assertInstanceOf('\Directus\SDK\Response\Entry', $response);

        $this->mockResponse('fetchColumnsEmpty.txt');
        $response = $this->client->getColumns('articles');
        $this->assertInstanceOf('\Directus\SDK\Response\Entry', $response);
    }

    public function testFetchColumnInformation()
    {
        $this->mockResponse('fetchColumnInfo.txt');
        $response = $this->client->getColumn('articles', 'title');
        $this->assertInternalType('object', $response);

        $this->mockResponse('fetchColumnInfoEmpty.txt');
        $response = $this->client->getColumn('articles', 'name');
        $this->assertInstanceOf('\Directus\SDK\Response\Entry', $response);
        $this->assertArrayHasKey('message', $response->getRawData());
        $this->assertInternalType('array', $response->getRawData());
    }

    public function testFetchGroups()
    {
        $this->mockResponse('fetchGroups.txt');
        $response = $this->client->getGroups();
        $this->assertInstanceOf('\Directus\SDK\Response\EntryCollection', $response);
        $this->assertSame(1, $response->count());

        $this->mockResponse('fetchGroupsEmpty.txt');
        $response = $this->client->getGroups();
        $this->assertInstanceOf('\Directus\SDK\Response\EntryCollection', $response);
        $this->assertSame(0, $response->count());
    }

    public function testFetchGroupInformation()
    {
        $this->mockResponse('fetchGroupInfo.txt');
        $response = $this->client->getGroup(1);
        $this->assertInternalType('object', $response);

        $this->mockResponse('fetchGroupInfoEmpty.txt');
        $response = $this->client->getGroup(2);
        $this->assertFalse($response->getRawData());
    }

    public function testFetchGroupPrivileges()
    {
        $this->mockResponse('fetchGroupPrivileges.txt');
        $response = $this->client->getGroupPrivileges(1);
        $this->assertInstanceOf('\Directus\SDK\Response\Entry', $response);
        $this->assertInstanceOf('\Directus\SDK\Response\Entry', $response[0]);
        $this->assertArrayHasKey('allow_view', $response[0]->getRawData());

        $this->mockResponse('fetchGroupPrivilegesEmpty.txt');
        $response = $this->client->getGroupPrivileges(30);
        $this->assertInstanceOf('\Directus\SDK\Response\Entry', $response);
        $this->assertInstanceOf('\Directus\SDK\Response\Entry', $response[0]);
        $this->assertArrayNotHasKey('allow_view', $response[0]->getRawData());
    }

    public function testFetchFiles()
    {
        $this->mockResponse('fetchFiles.txt');
        $response = $this->client->getFiles();
        $this->assertInstanceOf('\Directus\SDK\Response\EntryCollection', $response);

        $this->mockResponse('fetchFilesEmpty.txt');
        $response = $this->client->getFiles();
        $this->assertInstanceOf('\Directus\SDK\Response\EntryCollection', $response);
    }

    public function testFetchFileInformation()
    {
        $this->mockResponse('fetchFileInformation.txt');
        $response = $this->client->getFile(1);
        $this->assertInternalType('object', $response);

        $this->mockResponse('fetchFileInformationEmpty.txt');
        $response = $this->client->getFile(2);
        $this->assertNull($response->getRawData());
    }

    public function testFetchSettings()
    {
        $this->mockResponse('fetchSettings.txt');
        $response = $this->client->getSettings();
        $this->assertInternalType('object', $response);
    }

    public function testFetchSettingCollection()
    {
        $this->mockResponse('fetchSettingsCollection.txt');
        $response = $this->client->getSettingsByCollection('global');
        $this->assertInternalType('object', $response);
    }

    protected function mockResponse($path)
    {
        static $mock = null;
        if ($mock === null) {
            $mock = new \GuzzleHttp\Subscriber\Mock();
        }

        $mockPath = __DIR__.'/Mock/'.$path;
        $mockContent = file_get_contents($mockPath);
        $mock->addResponse($mockContent);

        $this->httpClient->getEmitter()->attach($mock);
    }
}

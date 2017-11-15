<?php

class EntryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var \Directus\SDK\Response\Entry
     */
    protected $entry;

    public function setUp()
    {
        $this->data = [
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 34
        ];

        $this->entry = new \Directus\SDK\Response\Entry($this->data);
    }

    public function testEntry11()
    {
        $data = [
            'meta' => ['table' => 'users'],
            'data' => $this->data
        ];

        $entry = new \Directus\SDK\Response\Entry($data);
        $this->assertEquals($this->data, $entry->getData());
        $this->assertNotNull($entry->getMetaData());
    }

    public function testEntry()
    {
        $entry = $this->entry;

        $this->assertEquals($this->data, $entry->getData());
        $this->assertNull($entry->getMetaData());
    }

    public function testAccessArray()
    {
        $this->assertSame(34, $this->entry['age']);
        $this->assertTrue(isset($this->entry['age']));
    }

    public function testJson()
    {
        $json = json_encode($this->entry);
        $array = json_decode($json, true);

        $this->assertInternalType('array', $array);
        $this->assertSame(34, $array['data']['age']);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testSetArrayAccessReadOnly()
    {
        $this->entry['age'] = 22;
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testUnsetArrayAccessReadOnly()
    {
        unset($this->entry['age']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetObjectAccess()
    {
        echo $this->entry->country;
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testUndefinedSetObjectAccess()
    {
        $this->entry->age = 22;
    }
}
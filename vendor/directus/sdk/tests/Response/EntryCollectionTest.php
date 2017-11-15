<?php

class EntryCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * API 1.1 data
     *
     * @var array
     */
    protected $data2 = [];

    /**
     * @var \Directus\SDK\Response\EntryCollection
     */
    protected $collection;

    /**
     * API 1.1 Collection
     *
     * @var \Directus\SDK\Response\EntryCollection
     */
    protected $collection2;

    public function setUp()
    {
        $this->data = [
            'Active' => 2,
            'Draft' => 0,
            'Delete' => 0,
            'total' => 2,
            'rows' => [
                [
                    'id' => 1,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'age' => 34,
                    'backpack' => [
                        'rows' => [
                            ['name' => 'phone'],
                            ['name' => 'wallet']
                        ]
                    ]
                ],
                [
                    'id' => 2,
                    'first_name' => 'Joseph',
                    'last_name' => 'Row',
                    'age' => 54,
                    'backpack' => [
                        'rows' => [
                            ['name' => 'pen'],
                            ['name' => 'note']
                        ]
                    ]
                ],
            ]
        ];
        $this->collection = new \Directus\SDK\Response\EntryCollection($this->data);

        $this->data2 = [
            'meta' => [
                'Active' => 2,
                'Draft' => 0,
                'Delete' => 0,
                'total' => 2,
            ],
            'data' => [
                [
                    'id' => 1,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'age' => 34,
                    'backpack' => [
                        'meta' => ['table' => 'items'],
                        'data' => [
                            ['name' => 'phone'],
                            ['name' => 'wallet']
                        ]
                    ]
                ],
                [
                    'id' => 2,
                    'first_name' => 'Joseph',
                    'last_name' => 'Row',
                    'age' => 54,
                    'backpack' => [
                        'meta' => ['table' => 'items'],
                        'data' => [
                            ['name' => 'pen'],
                            ['name' => 'note']
                        ]
                    ]
                ],
            ]
        ];
        $this->collection2 = new \Directus\SDK\Response\EntryCollection($this->data2);
    }

    public function testCollection()
    {
        $collection = $this->collection;
        $this->assertEquals($this->data, $collection->getRawData());
        $this->assertInternalType('array', $collection->getData());
        $this->assertCount(2, $collection->getData());
        $this->assertNotNull($collection->getMetaData());

        // 1.1
        $collection = $this->collection;
        $this->assertEquals($this->data, $collection->getRawData());
        $this->assertInternalType('array', $collection->getData());
        $this->assertCount(2, $collection->getData());
        $this->assertNotNull($collection->getMetaData());
    }

    public function testAccess()
    {
        $collection = $this->collection;
        $this->assertTrue(isset($collection[0]));

        $person = $collection[0];
        $this->assertSame('John', $person->first_name);

        // 1.1
        $collection = $this->collection2;
        $this->assertTrue(isset($collection[0]));

        $person = $collection[0];
        $this->assertSame('John', $person->first_name);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testUnsetItem()
    {
        unset($this->collection[0]);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testSetItem()
    {
        $this->collection[0] = [];
    }

    public function testCount()
    {
        $this->assertSame(2, count($this->collection));
    }

    public function testIterator()
    {
        $this->assertInstanceOf('\ArrayIterator', $this->collection->getIterator());
    }

    public function testJson()
    {
        $json = json_encode($this->collection);
        $array = json_decode($json, true);

        $this->assertInternalType('array', $array);
        $this->assertSame(2, count($array['data']));
    }
}
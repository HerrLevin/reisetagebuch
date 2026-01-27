<?php

namespace Integration\Traits;

use App\Traits\JsonResponseObject;
use Tests\TestCase;

class JsonResponseObjectTest extends TestCase
{
    private function getTestInstance(): TestClass
    {
        $details = new NestedClass('123 Main St', '555-1234');

        return new TestClass('John Doe', 30, $details, ['city' => 'Metropolis', 'zip' => '12345']);
    }

    public function test_array(): void
    {
        $instance = $this->getTestInstance();
        $expectedArray = [
            'name' => 'John Doe',
            'age' => 30,
            'details' => [
                'address' => '123 Main St',
                'phone' => '555-1234',
            ],
            'address' => [
                'city' => 'Metropolis',
                'zip' => '12345',
            ],
        ];
        $this->assertEquals($expectedArray, $instance->toArray());
    }

    public function test_json_string(): void
    {
        $instance = $this->getTestInstance();
        $expectedJson = json_encode([
            'name' => 'John Doe',
            'age' => 30,
            'details' => [
                'address' => '123 Main St',
                'phone' => '555-1234',
            ],
            'address' => [
                'city' => 'Metropolis',
                'zip' => '12345',
            ],
        ]);
        $this->assertEquals($expectedJson, (string) $instance);
    }
}

class TestClass
{
    use JsonResponseObject;

    public string $name;

    public int $age;

    public NestedClass $details;

    public array $address;

    public function __construct($name, $age, $details, $address = [])
    {
        $this->name = $name;
        $this->age = $age;
        $this->details = $details;
        $this->address = $address;
    }
}

class NestedClass
{
    use JsonResponseObject;

    public $address;

    public $phone;

    public function __construct($address, $phone)
    {
        $this->address = $address;
        $this->phone = $phone;
    }
}

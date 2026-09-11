<?php
namespace Tests\Legacy\LaravelIntegration\Services\Form;

use Faker\Factory;

class Model
{
    public $name;
    public $email;
    public $bio;
    public $group_id;

    public function __construct()
    {
        $faker = Factory::create();

        $this->name = $faker->name;
        $this->email = $faker->email;
        $this->bio = $faker->text();
        $this->group_id = 1;
    }

    public function groups()
    {
        return collect([
            ['id' => 2, 'name' => 'Admin'],
            ['id' => 8, 'name' => 'Unassigned Group'],
        ]);
    }

    public function __isset($name)
    {
        return true;
    }

    public function __get($name)
    {
        if ($name == 'groups') {
            return $this->groups();
        }
        return $this->$name;
    }
}

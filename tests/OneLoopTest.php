<?php

namespace Hassan\OneLoop\Tests;

use PHPUnit\Framework\TestCase;

class OneLoopTest extends TestCase
{
    public function test_objects()
    {
        $users = collect($this->getUsers());

        $ids = one_loop($users)
            ->reject(static function ($user) {
                return $user->age < 20;
            })->map(static function ($user) {
                return $user->id;
            })->apply();

        $this->assertCount(2, $ids);
    }

    private function getUsers()
    {
        $user1 = new \stdClass();
        $user1->id = 1;
        $user1->age = 32;

        $user2 = new \stdClass();
        $user2->id = 2;
        $user2->age = 2;

        $user3 = new \stdClass();
        $user3->id = 3;
        $user3->age = 42;

        return [$user1, $user2, $user3];
    }

    public function test_numbers()
    {
        $numbers = one_loop([1, 2, 3])
            ->reject(static function ($item) {
                return $item === 2;
            })->map(static function ($item) {
                return $item * 3;
            })->apply();

        $this->assertCount(2, $numbers);
    }
}
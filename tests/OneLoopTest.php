<?php

namespace Hassan\OneLoop\Tests;

use PHPUnit\Framework\TestCase;

class OneLoopTest extends TestCase
{
    // Original tests preserved
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

    // New v2.0 tests
    public function test_filter()
    {
        $users = collect($this->getUsers());

        $result = one_loop($users)
            ->filter(static function ($user) {
                return $user->age >= 30;
            })
            ->apply();

        $this->assertCount(2, $result);
    }

    public function test_pluck_with_string()
    {
        $users = collect($this->getUsers());

        $ids = one_loop($users)
            ->pluck('id')
            ->apply();

        $this->assertCount(3, $ids);
        $this->assertEquals(1, $ids[0]);
        $this->assertEquals(2, $ids[1]);
        $this->assertEquals(3, $ids[2]);
    }

    public function test_pluck_with_callback()
    {
        $users = collect($this->getUsers());

        $result = one_loop($users)
            ->pluck(static function ($user) {
                return $user->id * 2;
            })
            ->apply();

        $this->assertCount(3, $result);
        $this->assertEquals(2, $result[0]);
        $this->assertEquals(4, $result[1]);
        $this->assertEquals(6, $result[2]);
    }

    public function test_unique()
    {
        $items = [
            (object)['id' => 1, 'type' => 'A'],
            (object)['id' => 2, 'type' => 'B'],
            (object)['id' => 3, 'type' => 'A'],
            (object)['id' => 4, 'type' => 'B'],
        ];

        $result = one_loop($items)
            ->pluck('type')
            ->unique()
            ->apply();

        $this->assertCount(2, $result);
    }

    public function test_unique_with_key()
    {
        $items = [
            (object)['id' => 1, 'email' => 'test@example.com'],
            (object)['id' => 2, 'email' => 'test2@example.com'],
            (object)['id' => 3, 'email' => 'test@example.com'],
        ];

        $result = one_loop($items)
            ->unique('email')
            ->apply();

        $this->assertCount(2, $result);
    }

    public function test_group_by()
    {
        $users = collect($this->getUsers());

        $result = one_loop($users)
            ->groupBy(static function ($user) {
                return $user->age < 30 ? 'young' : 'old';
            })
            ->apply();

        $this->assertArrayHasKey('young', $result);
        $this->assertArrayHasKey('old', $result);
        $this->assertCount(1, $result['young']);
        $this->assertCount(2, $result['old']);
    }

    public function test_limit()
    {
        $users = collect($this->getUsers());

        $result = one_loop($users)
            ->limit(2)
            ->apply();

        $this->assertCount(2, $result);
    }

    public function test_take()
    {
        $users = collect($this->getUsers());

        $result = one_loop($users)
            ->take(1)
            ->apply();

        $this->assertCount(1, $result);
    }

    public function test_when_condition_true()
    {
        $users = collect($this->getUsers());
        $shouldFilter = true;

        $result = one_loop($users)
            ->when($shouldFilter, static function ($loop) {
                $loop->filter(static function ($user) {
                    return $user->age >= 30;
                });
            })
            ->apply();

        $this->assertCount(2, $result);
    }

    public function test_when_condition_false()
    {
        $users = collect($this->getUsers());
        $shouldFilter = false;

        $result = one_loop($users)
            ->when($shouldFilter, static function ($loop) {
                $loop->filter(static function ($user) {
                    return $user->age >= 100; // Would filter everything
                });
            })
            ->apply();

        $this->assertCount(3, $result);
    }

    public function test_when_with_default()
    {
        $users = collect($this->getUsers());
        $condition = false;

        $result = one_loop($users)
            ->when(
                $condition,
                static function ($loop) {
                    $loop->filter(static function ($user) {
                        return $user->age >= 50;
                    });
                },
                static function ($loop) {
                    $loop->filter(static function ($user) {
                        return $user->age < 50;
                    });
                }
            )
            ->apply();

        $this->assertCount(3, $result);
    }

    public function test_complex_chain()
    {
        $users = collect($this->getUsers());

        $result = one_loop($users)
            ->filter(static function ($user) {
                return $user->age >= 20;
            })
            ->reject(static function ($user) {
                return $user->age > 40;
            })
            ->map(static function ($user) {
                return [
                    'id' => $user->id,
                    'age' => $user->age
                ];
            })
            ->apply();

        $this->assertCount(1, $result);
        $this->assertEquals(['id' => 1, 'age' => 32], $result[0]);
    }

    public function test_filter_pluck_unique_chain()
    {
        $items = [
            (object)['id' => 1, 'active' => true, 'type' => 'A'],
            (object)['id' => 2, 'active' => true, 'type' => 'B'],
            (object)['id' => 3, 'active' => false, 'type' => 'A'],
            (object)['id' => 4, 'active' => true, 'type' => 'A'],
        ];

        $result = one_loop($items)
            ->filter(static function ($item) {
                return $item->active;
            })
            ->pluck('type')
            ->unique()
            ->apply();

        $this->assertCount(2, $result);
    }

    public function test_limit_with_filter()
    {
        $users = collect($this->getUsers());

        $result = one_loop($users)
            ->filter(static function ($user) {
                return $user->age >= 20;
            })
            ->limit(1)
            ->apply();

        $this->assertCount(1, $result);
    }

    // Helper method
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
}
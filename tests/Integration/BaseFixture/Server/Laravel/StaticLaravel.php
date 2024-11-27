<?php
namespace Tests\Integration\BaseFixture\Server\Laravel;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Integration\BaseFixture\Server\Laravel;

class StaticLaravel
{
    private static ?Laravel\TestCase $laravel = null;

    public static function get(\PHPUnit\Framework\TestCase $testCase): ?Laravel\TestCase
    {
        if (!self::isLaravelApplication($testCase)) {
            return null;
        }
        if (self::$laravel === null) {
            $transactional = self::isTransactional($testCase);
            self::$laravel = self::getInstance($testCase->name(), $transactional, self::bootTest($testCase));
            self::$laravel->setUp();
        }
        return self::$laravel;
    }

    private static function getInstance(string $name, bool $transactional, callable $beforeBoot): Laravel\TestCase
    {
        if ($transactional) {
            return new class ($name, $beforeBoot) extends Laravel\TestCase {
                use DatabaseTransactions;
            };
        }
        return new Laravel\TestCase($name, $beforeBoot);
    }

    public static function destroy(): void
    {
        self::$laravel?->tearDown();
        self::$laravel = null;
    }

    private static function isLaravelApplication($testCase): bool
    {
        return self::hasMarker($testCase, Laravel\Application::class);
    }

    private static function isTransactional($testCase): bool
    {
        return self::hasMarker($testCase, Laravel\Transactional::class);
    }

    private static function hasMarker($testCase, string $markerClass): bool
    {
        return \in_array($markerClass, \class_uses_recursive($testCase));
    }

    private static function bootTest(\PHPUnit\Framework\TestCase $testCase): \Closure
    {
        return fn(Application $app) => self::beforeBoot($testCase, $app);
    }

    private static function beforeBoot(\PHPUnit\Framework\TestCase $testCase, Application $app): void
    {
        /**
         * In order to polymorphically call {@see Test::beforeBoot},
         * there would have to be a feature in PhpUnit which allows
         * to call a "register" method before setting up laravel
         * application.
         *
         * This is a workaround, but at least when we override this method.
         * PHP type system will check it, since traits can't have duplicated
         * methods.
         */
        if (\method_exists($testCase, 'beforeBoot')) {
            /** @var \stdClass $testCase */
            $testCase->beforeBoot($app);
        }
    }
}

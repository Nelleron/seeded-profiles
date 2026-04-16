<?php

declare(strict_types=1);

// Stub file for PHPStan only — actual methods are provided by Codeception modules at runtime.

namespace Tests\Support;

use Codeception\Actor;

/**
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 * @method void seeNumRecords($expected, $tableName, array $criteria = [])
 * @method void seeRecord($className, array $criteria = [])
 * @method void dontSeeRecord($className, array $criteria = [])
 * @method void grabRecord($className, array $criteria = [])
 * @method void assertEquals($expected, $actual, $message = '')
 * @method void assertNotEquals($expected, $actual, $message = '')
 * @method void assertCount($expectedCount, $haystack, $message = '')
 * @method void assertNotNull($value, $message = '')
 * @method void assertNull($value, $message = '')
 * @method void assertTrue($condition, $message = '')
 * @method void assertFalse($condition, $message = '')
 * @method void assertStringContainsString($needle, $haystack, $message = '')
 * @method void assertEmpty($value, $message = '')
 * @method void assertNotEmpty($value, $message = '')
 *
 * Browser methods (from Laravel module)
 * @method void amOnPage(string $page)
 * @method void amOnRoute(string $route, array $params = [])
 * @method void amOnUrl(string $url)
 * @method void see(string $text, string $selector = null)
 * @method void dontSee(string $text, string $selector = null)
 * @method void seeResponseCodeIs(int $code)
 */
class FunctionalTester extends Actor {}

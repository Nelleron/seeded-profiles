<?php

declare(strict_types=1);

namespace Tests\Support;

use Codeception\Actor;

/**
 * Inherited Methods
 *
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
 *
 * Browser Methods
 * @method void amOnPage(string $page)
 * @method void amOnRoute(string $route, array $params = [])
 * @method void amOnUrl(string $url)
 * @method void see(string $text, string $selector = null)
 * @method void dontSee(string $text, string $selector = null)
 * @method void seeResponseCodeIs(int $code)
 * @method void seeResponseContains(string $text)
 * @method void sendMethod(string $method)
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends Actor
{
    use _generated\FunctionalTesterActions;

    /**
     * Define custom actions here
     */
}

<?php
declare(strict_types=1);

namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;

class GetRemovedSubsFromAMonthIntTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function testDayBeforeFirstOfTheMonthIsLastOfThePrevious() {
        $aFirstDayOfMonth = new \DateTimeImmutable("2018-12-01");

        $dayBefore = $aFirstDayOfMonth->modify('-1 day');

        self::assertEquals($dayBefore, new \DateTimeImmutable("2018-11-30"));
    }
}

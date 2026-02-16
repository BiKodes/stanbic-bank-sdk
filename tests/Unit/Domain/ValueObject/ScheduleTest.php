<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\ValueObject\Schedule;

final class ScheduleTest extends TestCase
{
    public function testCreateSchedule(): void
    {
        $schedule = new Schedule(
            transferFrequency: 'MONTHLY',
            on: '12',
            startDate: '2021-02-13',
            endDate: '2022-01-03',
            repeat: '3',
            every: '1'
        );

        $this->assertSame('MONTHLY', $schedule->transferFrequency);
        $this->assertSame('12', $schedule->on);
        $this->assertSame('2021-02-13', $schedule->startDate);
        $this->assertSame('2022-01-03', $schedule->endDate);
        $this->assertSame('3', $schedule->repeat);
        $this->assertSame('1', $schedule->every);
    }

    public function testFromArray(): void
    {
        $data = [
            'transferFrequency' => 'DAILY',
            'on' => '1',
            'startDate' => '2026-02-16',
            'endDate' => '2026-02-20',
        ];

        $schedule = Schedule::fromArray($data);

        $this->assertSame('DAILY', $schedule->transferFrequency);
        $this->assertSame('1', $schedule->on);
        $this->assertSame('2026-02-16', $schedule->startDate);
        $this->assertSame('2026-02-20', $schedule->endDate);
    }

    public function testToArray(): void
    {
        $schedule = new Schedule(transferFrequency: 'WEEKLY');

        $array = $schedule->toArray();

        $this->assertSame('WEEKLY', $array['transferFrequency']);
    }

    public function testFromArrayWithSnakeCase(): void
    {
        $data = [
            'transfer_frequency' => 'MONTHLY',
            'on' => '15',
            'start_date' => '2026-02-01',
            'end_date' => '2026-03-01',
            'repeat' => '2',
            'every' => '1',
        ];

        $schedule = Schedule::fromArray($data);

        $this->assertSame('MONTHLY', $schedule->transferFrequency);
        $this->assertSame('15', $schedule->on);
        $this->assertSame('2026-02-01', $schedule->startDate);
        $this->assertSame('2026-03-01', $schedule->endDate);
        $this->assertSame('2', $schedule->repeat);
        $this->assertSame('1', $schedule->every);
    }

    public function testToArrayWithAllFields(): void
    {
        $schedule = new Schedule(
            transferFrequency: 'DAILY',
            on: '1',
            startDate: '2026-02-10',
            endDate: '2026-02-20',
            repeat: '5',
            every: '1'
        );

        $array = $schedule->toArray();

        $this->assertSame('DAILY', $array['transferFrequency']);
        $this->assertSame('1', $array['on']);
        $this->assertSame('2026-02-10', $array['startDate']);
        $this->assertSame('2026-02-20', $array['endDate']);
        $this->assertSame('5', $array['repeat']);
        $this->assertSame('1', $array['every']);
    }

    public function testToArrayEmptyWhenAllNull(): void
    {
        $schedule = new Schedule();

        $this->assertSame([], $schedule->toArray());
    }
}

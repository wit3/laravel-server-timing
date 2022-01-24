<?php

namespace BeyondCode\ServerTiming\Tests;

use PHPUnit\Framework\TestCase;
use BeyondCode\ServerTiming\ServerTiming;
use Symfony\Component\Stopwatch\Stopwatch;

class ServerTimingTest extends TestCase
{
    /** @test */
    public function it_can_set_custom_measures(): void
    {
        $timing = new ServerTiming(new Stopwatch());
        $timing->setDuration('key', 1000);

        $events = $timing->events();

        $this->assertCount(1, $events);
        $this->assertTrue(array_key_exists('key', $events));
        $this->assertSame(1000, $events['key']);
    }

    /** @test */
    public function it_can_start_and_stop_events(): void
    {
        $timing = new ServerTiming(new Stopwatch());
        $timing->start('key');
        sleep(1);
        $timing->stop('key');

        $events = $timing->events();

        $this->assertCount(1, $events);
        $this->assertTrue(array_key_exists('key', $events));
        $this->assertGreaterThanOrEqual(1000, $events['key']);
    }

    /** @test */
    public function it_can_start_and_stop_events_using_measure(): void
    {
        $timing = new ServerTiming(new Stopwatch());
        $timing->measure('key');
        sleep(1);
        $timing->measure('key');

        $events = $timing->events();

        $this->assertCount(1, $events);
        $this->assertTrue(array_key_exists('key', $events));
        $this->assertGreaterThanOrEqual(1000, $events['key']);
    }

    /** @test */
    public function it_can_set_multiple_events(): void
    {
        $timing = new ServerTiming(new Stopwatch());
        $timing->setDuration('key_1', 1000);
        $timing->setDuration('key_2', 2000);

        $events = $timing->events();

        $this->assertCount(2, $events);
        $this->assertTrue(array_key_exists('key_1', $events));
        $this->assertTrue(array_key_exists('key_2', $events));

        $this->assertSame(1000, $events['key_1']);
        $this->assertSame(2000, $events['key_2']);
    }

    /** @test */
    public function it_can_set_events_without_duration(): void
    {
        $timing = new ServerTiming(new Stopwatch());
        $timing->addMetric($metricText = 'Custom Metric');

        $events = $timing->events();

        $this->assertCount(1, $events);
        $this->assertTrue(array_key_exists($metricText, $events));
        $this->assertNull($events[$metricText]);


        $timing->addMessage($messageText = 'Custom Message');

        $events = $timing->events();

        $this->assertCount(2, $events);
        $this->assertTrue(array_key_exists($messageText, $events));
        $this->assertNull($events[$messageText]);
    }

    /** @test */
    public function it_can_stop_started_events(): void
    {
        $timing = new ServerTiming(new Stopwatch());
        $timing->start('Started');

        $timing->stopAllUnfinishedEvents();
        $events = $timing->events();

        $this->assertCount(1, $events);
        $this->assertTrue(array_key_exists('Started', $events));
        $this->assertNotNull($events['Started']);
    }

    /** @test */
    public function it_can_set_durations_with_callables(): void
    {
        $timing = new ServerTiming(new Stopwatch());
        $timing->setDuration('callable', function() {
            sleep(1);
        });

        $events = $timing->events();

        $this->assertCount(1, $events);
        $this->assertTrue(array_key_exists('callable', $events));
        $this->assertTrue($events['callable'] >= 1000);
    }
}

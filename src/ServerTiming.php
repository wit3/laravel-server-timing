<?php

namespace BeyondCode\ServerTiming;

use Symfony\Component\Stopwatch\Stopwatch;

class ServerTiming
{
    protected Stopwatch $stopwatch;

    /**
     * @var array<string, int|float|null> $finishedEvents
     */
    protected array $finishedEvents = [];

    /**
     * @var array<string, bool> $startedEvents
     */
    protected array $startedEvents = [];

    public function __construct(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    public function addMetric(string $metric): self
    {
        return $this->setDuration($metric, null);
    }

    public function addMessage(string $message): self
    {
        return $this->addMetric($message);
    }

    public function hasStartedEvent(string $key): bool
    {
        return array_key_exists($key, $this->startedEvents);
    }

    public function measure(string $key): self
    {
        if (! $this->hasStartedEvent($key)) {
            return $this->start($key);
        }

        return $this->stop($key);
    }

    public function start(string $key): self
    {
        $this->stopwatch->start($key);

        $this->startedEvents[$key] = true;

        return $this;
    }

    public function stop(string $key): self
    {
        if ($this->stopwatch->isStarted($key)) {
            $event = $this->stopwatch->stop($key);

            $this->setDuration($key, $event->getDuration());

            unset($this->startedEvents[$key]);
        }

        return $this;
    }

    public function stopAllUnfinishedEvents(): void
    {
        foreach (array_keys($this->startedEvents) as $startedEventName) {
            $this->stop($startedEventName);
        }
    }

    public function setDuration(string $key, callable|int|float|null $duration): self
    {
        if (is_callable($duration)) {
            $this->start($key);

            call_user_func($duration);

            $this->stop($key);
        } else {
            $this->finishedEvents[$key] = $duration;
        }

        return $this;
    }

    public function getDuration(string $key): int|float|null
    {
        return $this->finishedEvents[$key] ?? null;
    }

    /**
     * @return array<string, int|float|null>
     */
    public function events(): array
    {
        return $this->finishedEvents;
    }

    public function reset(Stopwatch $stopwatch): void
    {
        $this->finishedEvents = [];
        $this->startedEvents = [];
        $this->stopwatch = $stopwatch;
    }

}

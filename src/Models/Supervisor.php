<?php

namespace SMSkin\LaravelDynamicHorizon\Models;

use RuntimeException;

class Supervisor
{
    public string $hash;

    public function __construct(
        public string      $name,
        public array       $queues,
        public string|null $connection = 'redis',
        public string|null $balance = 'auto',
        public string|null $autoScalingStrategy = 'time',
        public int|null    $maxProcesses = 1,
        public int|null    $maxTime = 0,
        public int|null    $maxJobs = 0,
        public int|null    $memory = 128,
        public int|null    $tries = 1,
        public int|null    $timeout = 60,
        public int|null    $nice = 0,
        public string|null $workersName = 'default',
        public int|null    $backoff = 0,
        public int|null    $minProcesses = 1,
        public int|null    $sleep = 3,
        public bool|null   $force = false,
        public int|null    $balanceCooldown = 3,
        public int|null    $balanceMaxShift = 1,
        public int|null    $rest = 0,
    ) {
        $this->hash = $this->calculateHash();

        if ($this->minProcesses < 1) {
            throw new RuntimeException('The value of minProcesses must be greater than 0.');
        }
    }

    private function calculateHash(): string
    {
        return md5(implode('_', [
            implode(',', $this->queues),
            $this->connection,
            $this->balance,
            $this->autoScalingStrategy,
            $this->maxProcesses,
            $this->maxTime,
            $this->maxJobs,
            $this->memory,
            $this->tries,
            $this->timeout,
            $this->nice,
            $this->workersName,
            $this->backoff,
            $this->minProcesses,
            $this->sleep,
            $this->force,
            $this->balanceCooldown,
            $this->balanceMaxShift,
            $this->rest,
        ]));
    }
}

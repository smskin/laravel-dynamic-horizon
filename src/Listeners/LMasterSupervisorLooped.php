<?php

namespace SMSkin\LaravelDynamicHorizon\Listeners;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Horizon\Events\MasterSupervisorLooped;
use Laravel\Horizon\MasterSupervisor;
use Laravel\Horizon\MasterSupervisorCommands\AddSupervisor;
use Laravel\Horizon\SupervisorOptions;
use Laravel\Horizon\SupervisorProcess;
use SMSkin\LaravelDynamicHorizon\Contracts\IStorage;
use SMSkin\LaravelDynamicHorizon\Models\Supervisor;

class LMasterSupervisorLooped
{
    public function __construct(private IStorage $storage)
    {
    }

    public function handle(MasterSupervisorLooped $event): void
    {
        $supervisors = $this->getSupervisors($event->master);
        $activeSupervisors = $this->getActiveSupervisors($event->master);

        $toDrop = $this->getSupervisorsForDrop($activeSupervisors, $supervisors);
        $toAdd = $this->getSupervisorsForAdd($activeSupervisors, $supervisors);

        if ($toAdd->isNotEmpty()) {
            $toAdd->each(function (SupervisorOptions $options) use ($event) {
                (new AddSupervisor())->process($event->master, $options->toArray());
                $this->log('Added supervisor: ' . $options->name);
            });
        }

        if ($toDrop->isNotEmpty()) {
            $toDrop->each(function (SupervisorProcess $supervisor) {
                $supervisor->stop();
                $this->log('Stopped supervisor: ' . $supervisor->options->name);
            });
        }
        $this->log('Master supervisor looped');
    }

    /**
     * @param MasterSupervisor $master
     * @return Collection<SupervisorProcess>
     */
    private function getActiveSupervisors(MasterSupervisor $master): Collection
    {
        return $master->supervisors->filter(function (SupervisorProcess $supervisor) use ($master) {
            return str_starts_with($supervisor->options->name, $this->getSupervisorName($master, null));
        });
    }

    /**
     * @param Collection<SupervisorProcess> $activeSupervisors
     * @param Collection<SupervisorOptions> $supervisors
     * @return Collection<SupervisorOptions>
     */
    private function getSupervisorsForAdd(Collection $activeSupervisors, Collection $supervisors): Collection
    {
        $activeSupervisorNames = $activeSupervisors->pluck('options.name')->toArray();
        return $supervisors->filter(static function (SupervisorOptions $options) use ($activeSupervisorNames) {
            return !in_array($options->name, $activeSupervisorNames);
        });
    }

    /**
     * @param Collection<SupervisorProcess> $activeSupervisors
     * @param Collection<SupervisorOptions> $supervisors
     * @return Collection<SupervisorProcess>
     */
    private function getSupervisorsForDrop(Collection $activeSupervisors, Collection $supervisors): Collection
    {
        $supervisorNames = $supervisors->pluck('name')->toArray();
        return $activeSupervisors->filter(static function (SupervisorProcess $supervisor) use ($supervisorNames) {
            return !in_array($supervisor->options->name, $supervisorNames);
        });
    }

    private function getSupervisorName(MasterSupervisor $master, string|null $name): string
    {
        return $master->name . ':d-' . $name;
    }

    /**
     * @param MasterSupervisor $master
     * @return Collection<SupervisorOptions>
     */
    private function getSupervisors(MasterSupervisor $master): Collection
    {
        return $this->storage->all()->map(function (Supervisor $supervisor) use ($master) {
            return new SupervisorOptions(
                $this->getSupervisorName($master, $supervisor->name) . '-' . $supervisor->hash,
                $supervisor->connection,
                implode(',', $supervisor->queues),
                $supervisor->workersName,
                $supervisor->balance,
                $supervisor->backoff,
                $supervisor->maxTime,
                $supervisor->maxJobs,
                $supervisor->maxProcesses,
                $supervisor->minProcesses,
                $supervisor->memory,
                $supervisor->timeout,
                $supervisor->sleep,
                $supervisor->tries,
                $supervisor->force,
                $supervisor->nice,
                $supervisor->balanceCooldown,
                $supervisor->balanceMaxShift,
                $master->pid(),
                $supervisor->rest,
                $supervisor->autoScalingStrategy
            );
        });
    }

    private function log(string $message): void
    {
        Log::debug($message);
    }
}

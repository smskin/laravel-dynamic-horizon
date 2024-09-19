<?php

namespace SMSkin\LaravelDynamicHorizon;

use Illuminate\Support\Collection;
use SMSkin\LaravelDynamicHorizon\Contracts\IStorage;
use SMSkin\LaravelDynamicHorizon\Models\Supervisor;

class DynamicHorizon
{
    private IStorage $storage;

    public function __construct()
    {
        $this->storage = app(IStorage::class);
    }

    /**
     * @return Collection<Supervisor>
     */
    public function getSupervisors(): Collection
    {
        return $this->storage->all();
    }

    /**
     * @param Collection<Supervisor> $supervisors
     */
    public function setSupervisors(Collection $supervisors): void
    {
        $this->storage->set($supervisors);
    }

    /**
     * @throws Exceptions\ItemAlreadyExists
     */
    public function addSupervisor(Supervisor $supervisor): void
    {
        $this->storage->add($supervisor);
    }

    /**
     * @throws Exceptions\ItemNotFound
     */
    public function updateSupervisor(Supervisor $supervisor): void
    {
        $this->storage->update($supervisor);
    }

    /**
     * @throws Exceptions\ItemNotFound
     */
    public function stopSupervisor(Supervisor $supervisor): void
    {
        $this->storage->delete($supervisor);
    }

    public function stopAllSupervisors(): void
    {
        $this->storage->deleteAll();
    }
}

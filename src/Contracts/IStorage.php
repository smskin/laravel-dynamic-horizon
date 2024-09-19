<?php

namespace SMSkin\LaravelDynamicHorizon\Contracts;

use Illuminate\Support\Collection;
use SMSkin\LaravelDynamicHorizon\Exceptions\ItemAlreadyExists;
use SMSkin\LaravelDynamicHorizon\Exceptions\ItemNotFound;
use SMSkin\LaravelDynamicHorizon\Models\Supervisor;

interface IStorage
{
    /**
     * @return Collection<Supervisor>
     */
    public function all(): Collection;

    /**
     * @param Collection<Supervisor> $collection
     */
    public function set(Collection $collection): void;

    /**
     * @throws ItemAlreadyExists
     */
    public function add(Supervisor $supervisor): void;

    /**
     * @throws ItemNotFound
     */
    public function update(Supervisor $supervisor): void;

    /**
     * @throws ItemNotFound
     */
    public function delete(Supervisor $supervisor): void;

    public function deleteAll(): void;
}

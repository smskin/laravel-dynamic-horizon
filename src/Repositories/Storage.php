<?php

namespace SMSkin\LaravelDynamicHorizon\Repositories;

use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use SMSkin\LaravelDynamicHorizon\Contracts\IStorage;
use SMSkin\LaravelDynamicHorizon\Exceptions\ItemAlreadyExists;
use SMSkin\LaravelDynamicHorizon\Exceptions\ItemNotFound;
use SMSkin\LaravelDynamicHorizon\Models\Supervisor;

class Storage implements IStorage
{
    /**
     * @return Collection<Supervisor>
     */
    public function all(): Collection
    {
        return collect($this->getCache()->get($this->getKey()));
    }

    /**
     * @param Collection<Supervisor> $collection
     */
    public function set(Collection $collection): void
    {
        $this->getCache()->forever($this->getKey(), $collection->toArray());
    }

    /**
     * @throws ItemAlreadyExists
     */
    public function add(Supervisor $supervisor): void
    {
        try {
            $this->getIndexByName($supervisor->name);
            throw new ItemAlreadyExists();
        } catch (ItemNotFound) {
        }

        $collection = $this->all()->push($supervisor);
        $this->set($collection);
    }

    /**
     * @throws ItemNotFound
     */
    public function update(Supervisor $supervisor): void
    {
        $collection = $this->all()
            ->forget(
                $this->getIndexByName($supervisor->name)
            )->push($supervisor);
        $this->set($collection);
    }

    /**
     * @throws ItemNotFound
     */
    public function delete(Supervisor $supervisor): void
    {
        $collection = $this->all()->forget(
            $this->getIndexByName($supervisor->name)
        );
        $this->set($collection);
    }

    public function deleteAll(): void
    {
        $this->getCache()->forget($this->getKey());
    }

    private function getCache(): Store
    {
        if (Cache::supportsTags()) {
            return Cache::tags(['dynamic-horizon'])->getStore();
        }
        return Cache::getStore();
    }

    private function getKey(): string
    {
        return 'dynamic-horizon-supervisors';
    }

    /**
     * @throws ItemNotFound
     */
    private function getIndexByName(string $name): int
    {
        $key = $this->all()->search(static function (Supervisor $s) use ($name) {
            return $s->name === $name;
        });
        if ($key === false) {
            throw new ItemNotFound();
        }
        return $key;
    }
}

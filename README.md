## Dynamic Management of Horizon Supervisors
I encountered the need to programmatically manage processes and queues in Horizon to solve the following case:

> My users generate tasks in the queue. If one user generates 1 million tasks, the second user has to wait until the queue is cleared. The standard Horizon solution will not work, as consumers and queues are configured statically in the configuration file. Creating X queues for each user would be a very resource-intensive solution since there are many users, and the queues will remain idle most of the time (each Horizon consumer is a separate process that consumes operating system resources).

Solution: Create consumers as needed and stop them when not in use.

### Operation Principle
The library is based on listening to the standard Horizon MasterSupervisorLooped event (the completion of the Horizon master process cycle).

At each tick of the cycle, the current configuration of dynamic supervisors (stored in Redis) is polled, and supervisors are started/stopped as necessary.

### Library Feature
This library does not modify Horizon in any way. It is designed to "live alongside" and not interfere. When the configuration changes, it takes some time (the tick of the Horizon process) before new supervisors are launched.

### Usage
1. Getting the list of dynamic supervisors
```php
use SMSkin\LaravelDynamicHorizon\DynamicHorizon;

$supervisors = (new DynamicHorizon())->getSupervisors();
```

2. Setting the configuration for supervisors
```php
use SMSkin\LaravelDynamicHorizon\DynamicHorizon;  
use SMSkin\LaravelDynamicHorizon\Models\Supervisor;

(new DynamicHorizon())->setSupervisors(collect([
    new Supervisor(
        'user1-supervisor',
        [
            'user1-queue',
        ]
    ),
    new Supervisor(
        'user2-supervisor',
        [
            'user2-queue-1',
            'user2-queue-2',
        ]
    )
]))
```

3. Adding a supervisor to the configuration
```php
use SMSkin\LaravelDynamicHorizon\DynamicHorizon;  
use SMSkin\LaravelDynamicHorizon\Models\Supervisor;

(new DynamicHorizon())->addSupervisor(
    new Supervisor(
        'user3-supervisor',
        [
            'user3-queue',
        ]
    )
);
```

4. Updating the configuration of one of the supervisors
```php
use SMSkin\LaravelDynamicHorizon\DynamicHorizon;  
use SMSkin\LaravelDynamicHorizon\Models\Supervisor;

(new DynamicHorizon())->updateSupervisor(
    new Supervisor(
        'user3-supervisor',
        [
            'user3-queue-1',
            'user3-queue-2',
        ]
    )
);
```

5. Stopping one of the supervisors
```php
use SMSkin\LaravelDynamicHorizon\DynamicHorizon;  
use SMSkin\LaravelDynamicHorizon\Models\Supervisor;

(new DynamicHorizon())->stopSupervisor(
    new Supervisor(
        'user1-supervisor',
        [
            'user1-queue',
        ]
    )
);
```

6. Stopping all dynamic supervisors
```php
use SMSkin\LaravelDynamicHorizon\DynamicHorizon;  

(new DynamicHorizon())->stopAllSupervisors();
```

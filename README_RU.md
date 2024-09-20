## Динамическое управление супервизорами horizon
Столкнулся с необходимостью программно управлять процессами и очередями Horizon для решения следующего кейса:

> У меня пользователи порождают задачи в очереди. Если один пользователь генерирует 1 млн задач, то второму пользователю приходится ждать, пока очередь очистится. Стандартное решение Horizon не подойдет, т.к. консьюмеры и очереди конфигурируются статично в файле конфигурации. Создать Х очередей на каждого пользователя будет очень дорогим по ресурсам решением, т.к. пользователей очень много, а очереди будут подавляющее время простаивать (каждый консьюмер Horizon является отдельным процессом, который занимает ресурсы операционной системы).

Решение: создавать консьюмеры при необходимости и останавливать их в случае неиспользования.

### Принцип работы
Библиотека построена на основе прослушивания стандартного события Horizon MasterSupervisorLooped (завершения цикла мастер-процесса Horizon).

При каждом тике цикла опрашивается текущая конфигурация динамических супервизоров (хранится в Redis) и поднимает/останавливает супервизоры по необходимости.

### Особенность библиотеки
Данная библиотека никак не модифицирует Horizon. Она разработана так, чтобы "жить рядом" и не мешать. При изменении конфигурации требуется некоторое время (тик процесса Horizon), прежде чем запустятся новые супервизоры.

### Использование
1. Получение списка динамических супервизоров
```php
use SMSkin\LaravelDynamicHorizon\DynamicHorizon;

$supervisors = (new DynamicHorizon())->getSupervisors();
```
2. Установка конфигурации супервизоров
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
3. Добавление супервизора в конфигурацию
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
4. Обновление конфигурации одного из супервизоров
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
5. Остановка одного из супервизоров
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
6. Остановка всех динамических супервизоров
```php
use SMSkin\LaravelDynamicHorizon\DynamicHorizon;  

(new DynamicHorizon())->stopAllSupervisors();
```

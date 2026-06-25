<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Dsl;

use Tests\Legacy\IntegrationNew\BaseFixture\Dsl\Driver\DslDatabaseDriver;
use Tests\Legacy\IntegrationNew\BaseFixture\Dsl\Driver\DslHttpDriver;
use Tests\Legacy\IntegrationNew\BaseFixture\Dsl\Model\DslTopic;
use Tests\Legacy\IntegrationNew\BaseFixture\Dsl\Request\DslRequests;
use Tests\Legacy\IntegrationNew\BaseFixture\Forum\ModelsDriver;
use Tests\Legacy\IntegrationNew\BaseFixture\Server\Laravel;
use Tests\Legacy\IntegrationNew\BaseFixture\Server\Server;

class Dsl {
    private readonly DslHttpDriver $http;
    private readonly DslDatabaseDriver $database;
    private readonly DslRequests $requests;

    private ?DslTopic $contextTopic = null;

    public function __construct(
        private ModelsDriver $modelsDriver,
        Server               $server,
        Laravel\TestCase     $laravel,
    ) {
        $this->database = new DslDatabaseDriver($laravel);
        $this->http = new DslHttpDriver($server);
        $this->requests = new DslRequests();
    }

    public function loginUser(string $permissionName): void {
        $this->http->server->loginById($this->modelsDriver->newUserReturnId(
            permissionName:$permissionName,
            emailConfirmed:true,
        ));
    }

    public function loginUserNew(): void {
        $this->http->server->loginById($this->modelsDriver->newUserReturnId());
    }

    public function createTopic(string $discussMode = null): void {
        $this->createAndStoreTopic($this->requests->createTopic($discussMode));
    }

    private function createAndStoreTopic(Request\CreateTopic $request): void {
        $this->database->seedCategoryIfNotExists('Newbie');
        $this->http->postCreateTopic($request);
        $this->contextTopic = new DslTopic(
            $request->title,
            $this->http->lastResponseJsonField('url'));
    }

    public function assertTopicCreated(): void {
        $this->database->assertTopicExists($this->contextTopic);
    }

    public function assertTopicNotCreated(): void {
        $this->database->assertTopicNotExists($this->contextTopic);
    }

    public function assertTopicModeTree(): void {
        $this->database->assertTopicColumn($this->contextTopic, 'is_tree', true);
    }
}

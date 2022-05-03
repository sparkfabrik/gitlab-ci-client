<?php

namespace GitlabActionsTest\Unit;

use Gitlab\Api\MergeRequests;
use Gitlab\Client;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Sparkfabrik\GitlabCiClient\GitlabActionsCommand;
use Zenstruck\Console\Test\TestCommand;

/**
 * Unit tests on GitlabActionsCommand class.
 */
class GitlabActionsCommandTest extends TestCase {
  use ProphecyTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    foreach ([
      'GITLAB_PRIVATE_TOKEN',
      'CI_SERVER_URL',
    ] as $var) {
      putenv("$var=something");
    }
  }

  /**
   * Test the constructor.
   */
  public function testItCanBeInstantiated() {
    $client = $this->mockClient(FALSE);
    $this->assertInstanceOf(
      GitlabActionsCommand::class,
      new GitlabActionsCommand($client->reveal())
    );
  }

  /**
   * Test the execute method happy path.
   */
  public function testExecute() {
    $client = $this->mockClient();
    TestCommand::for(new GitlabActionsCommand($client->reveal()))
      ->execute('mergeRequests.all')
      ->assertSuccessful() // command exit code is 0
      ->assertOutputContains('"Fake Merge request"')
      ->assertOutputContains('"id": 123')
      ->assertOutputContains('"iid": 2345')
      ->assertOutputContains('"project_id": 12345');
  }

  /**
   * Test that some env var are required.
   */
  public function testMissingVars() {
    $required_variables = [
      'GITLAB_PRIVATE_TOKEN',
      'CI_SERVER_URL',
    ];
    $client = $this->mockClient(FALSE);
    foreach ($required_variables as $variable) {
      putenv("$variable=");
      TestCommand::for(new GitlabActionsCommand($client->reveal()))
        ->execute('mergeRequests.all')
        ->assertOutputContains("\"$variable\" env variable is not set.")
        ->assertStatusCode(1);
      putenv("$variable=something");
    }
  }

  /**
   * Test that invalid method or resource should make the command exit with code 1.
   */
  public function testInvalidMethodOrResource() {
    $client = $this->mockClient();
    TestCommand::for(new GitlabActionsCommand($client->reveal()))
      ->execute('foo.bar')
      ->assertOutputContains('foo is not a valid method')
      ->assertStatusCode(1);
    TestCommand::for(new GitlabActionsCommand($client->reveal()))
      ->execute('MergeRequests.bar')
      ->assertOutputContains('bar is not a valid method on "MergeRequests" resource')
      ->assertStatusCode(1);
  }

  /**
   * Mocks the gitlab client.
   *
   * @param bool $forRun
   *   Whether to execute calls should be mocked.
   *
   * @return \Prophecy\Prophecy\ObjectProphecy
   *   The mocked client.
   */
  protected function mockClient(bool $forRun = TRUE) {
    $client = $this->prophesize(Client::class);
    if ($forRun) {
      $mergeRequest = $this->prophesize(MergeRequests::class);
      $mergeRequest->all(Argument::any())->willReturn([
        [
          'id' => 123,
          'iid' => 2345,
          'project_id' => 12345,
          'title' => 'Fake Merge request',
        ],
      ]);
      $client->mergeRequests()->shouldBeCalled()->willReturn($mergeRequest->reveal());
      $client->setUrl(Argument::any())->shouldBeCalled();
      $client->authenticate(Argument::any(), Client::AUTH_HTTP_TOKEN)->shouldBeCalled();
    }

    return $client;
  }

}

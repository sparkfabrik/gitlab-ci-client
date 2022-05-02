<?php

namespace GitlabActionsTest\Unit;

use PHPUnit\Framework\TestCase;
use Sparkfabrik\GitlabCiClient\GitlabActionsCommand;

/**
 * Unit tests on GitlabActionsCommand class.
 */
class GitlabActionsCommandTest extends TestCase {

  /**
   * Test the constructor.
   */
  public function testItCanBeInstantiated() {
    $this->assertInstanceOf(
      GitlabActionsCommand::class,
      new GitlabActionsCommand()
    );
  }

}

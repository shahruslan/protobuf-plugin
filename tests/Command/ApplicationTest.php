<?php

namespace ProtobufCompilerTest;

use ProtobufCompilerTest\TestCase;
use Protobuf\Compiler\Command\Application;
use Protobuf\Compiler\Command\PluginCommand;
use Protobuf\Compiler\Command\GenerateCommand;

class ApplicationTest extends TestCase
{
     private ?\PHPUnit_Framework_MockObject_MockObject $generateCommand = null;

    private ?\PHPUnit_Framework_MockObject_MockObject $pluginCommand = null;

     private ?\PHPUnit_Framework_MockObject_MockObject $input = null;

    public function setUp()
    {
        $this->input   = $this->getMock('Symfony\Component\Console\Input\InputInterface');

        $this->generateCommand = $this->getMockBuilder(GenerateCommand::CLASS)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pluginCommand = $this->getMockBuilder(PluginCommand::CLASS)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSingleCommandApplication()
    {
        $this->generateCommand->expects($this->once())
            ->method('getName')
            ->willReturn('my-command');

        $application     = new Application($this->generateCommand, $this->pluginCommand);
        $definition      = $this->invokeMethod($application, 'getDefinition');
        $defaultCommands = $this->invokeMethod($application, 'getDefaultCommands');
        $commandName     = $this->invokeMethod($application, 'getCommandName', [$this->input]);

        $this->assertEquals('my-command', $commandName);
        $this->assertEquals([], $definition->getArguments());
        $this->assertSame($this->generateCommand, $defaultCommands[2]);
        $this->assertSame($this->pluginCommand, $defaultCommands[3]);
    }
}

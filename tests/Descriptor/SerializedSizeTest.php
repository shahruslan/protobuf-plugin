<?php

namespace ProtobufCompilerTest\Descriptor;

use Protobuf\Extension\ExtensionFieldMap;
use Protobuf\Binary\SizeCalculator;
use Protobuf\ComputeSizeContext;
use Protobuf\Stream;

use ProtobufCompilerTest\TestCase;
use ProtobufCompilerTest\Protos\Simple;
use ProtobufCompilerTest\Protos\Person;
use ProtobufCompilerTest\Protos\Repeated;
use ProtobufCompilerTest\Protos\AddressBook;

/**
 * @group functional
 */
class SerializedSizeTest extends TestCase
{
    protected function setUp()
    {
        $this->markTestIncompleteIfProtoClassNotFound();

        parent::setUp();
    }

    public function testSimpleSerializedSize()
    {
        $simple     = new Simple();
        $calculator = $this->createMock(SizeCalculator::CLASS);
        $context    = $this->createMock(ComputeSizeContext::CLASS);

        $simple->setBool(true);
        $simple->setBytes("bar");
        $simple->setString("foo");
        $simple->setFloat(12345.123);
        $simple->setUint32(123_456_789);
        $simple->setInt32(-123_456_789);
        $simple->setFixed32(123_456_789);
        $simple->setSint32(-123_456_789);
        $simple->setSfixed32(-123_456_789);
        $simple->setDouble(123_456_789.12345);
        $simple->setInt64(-123_456_789_123_456_789);
        $simple->setUint64(123_456_789_123_456_789);
        $simple->setFixed64(123_456_789_123_456_789);
        $simple->setSint64(-123_456_789_123_456_789);
        $simple->setSfixed64(-123_456_789_123_456_789);

        $context->expects($this->once())
            ->method('getSizeCalculator')
            ->willReturn($calculator);

        $calculator->expects($this->exactly(4))
            ->method('computeVarintSize')
            ->will($this->returnValueMap([
                [-123_456_789_123_456_789, 10],
                [123_456_789_123_456_789, 9],
                [-123_456_789, 10],
                [123_456_789, 4],
            ]));

        $calculator->expects($this->once())
            ->method('computeStringSize')
            ->with('foo')
            ->willReturn(3);

        $calculator->expects($this->once())
            ->method('computeByteStreamSize')
            ->with('bar')
            ->willReturn(3);

        $calculator->expects($this->once())
            ->method('computeZigzag32Size')
            ->with(-123_456_789)
            ->willReturn(4);

        $calculator->expects($this->once())
            ->method('computeZigzag64Size')
            ->with(-123_456_789_123_456_789)
            ->willReturn(9);

        $simple->serializedSize($context);
    }

    public function testRepeatedStringSerializedSize()
    {
        $repeated   = new Repeated();
        $calculator = $this->createMock(SizeCalculator::CLASS);
        $context    = $this->createMock(ComputeSizeContext::CLASS);

        $repeated->addString('one');
        $repeated->addString('two');
        $repeated->addString('three');

        $context->expects($this->once())
            ->method('getSizeCalculator')
            ->willReturn($calculator);

        $calculator->expects($this->exactly(3))
            ->method('computeStringSize')
            ->will($this->returnValueMap([
                ['one', 4],
                ['two', 4],
                ['three', 6]
            ]));

        $repeated->serializedSize($context);
    }

    public function testRepeatedInt32SerializedSize()
    {
        $repeated   = new Repeated();
        $calculator = $this->createMock(SizeCalculator::CLASS);
        $context    = $this->createMock(ComputeSizeContext::CLASS);

        $repeated->addInt(1);
        $repeated->addInt(2);
        $repeated->addInt(2);

        $context->expects($this->once())
            ->method('getSizeCalculator')
            ->willReturn($calculator);

        $calculator->expects($this->exactly(3))
            ->method('computeVarintSize')
            ->will($this->returnValueMap([
                [1, 1],
                [2, 1],
                [3, 1]
            ]));

        $repeated->serializedSize($context);
    }

    public function testAddressBookWithExtensionsSerializedSize()
    {
        $message    = new AddressBook();
        $person     = $this->createMock(Person::CLASS);
        $calculator = $this->createMock(SizeCalculator::CLASS);
        $extensions = $this->createMock(ExtensionFieldMap::CLASS);
        $context    = $this->createMock(ComputeSizeContext::CLASS);
        $personSize = 2;
        $extSize    = 4;

        $message->addPerson($person);
        $this->setPropertyValue($message, 'extensions', $extensions);

        $context->expects($this->once())
            ->method('getSizeCalculator')
            ->willReturn($calculator);

        $calculator->expects($this->once())
            ->method('computeVarintSize')
            ->with($this->equalTo($personSize))
            ->willReturn(1);

        $extensions->expects($this->once())
            ->method('serializedSize')
            ->willReturn($extSize)
            ->with($context);

        $person->expects($this->once())
            ->method('serializedSize')
            ->willReturn($personSize)
            ->with($context);

        $message->serializedSize($context);
    }
}

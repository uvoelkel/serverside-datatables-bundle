<?php

namespace Voelkel\DataTablesBundle\Tests\DataTables;

use Voelkel\DataTablesBundle\DataTables\DataToStringConverter;

class DataToStringConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertDateTimeToString()
    {
        $converter = new DataToStringConverter('en');

        $data = new \DateTime();
        $expected = $data->format('Y-m-d H:i:s');
        $this->assertSame($expected, $converter->convertDataToString($data));
    }

    public function testConvertBoolToString()
    {
        $converter = new DataToStringConverter('en');

        $this->assertSame('true', $converter->convertDataToString(true));
        $this->assertSame('false', $converter->convertDataToString(false));
    }

    public function testConvertObjectToString()
    {
        $converter = new DataToStringConverter('en');

        $data = new \stdClass();
        $this->assertEquals('stdClass', $converter->convertDataToString($data));
    }
}

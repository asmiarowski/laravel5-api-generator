<?php

namespace spec\Smiarowski\Generators\Migrations;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NameParserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Smiarowski\Generators\Migrations\NameParser');
    }
}

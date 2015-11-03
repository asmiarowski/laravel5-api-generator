<?php

namespace spec\Smiarowski\Generators\Commands;

use Illuminate\Foundation\Composer;
use Illuminate\Filesystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ApiResourceMakeCommandSpec extends ObjectBehavior
{
    function let()
    {
        $this->letItBeConstructedWith(new Filesystem, new Composer);
        $this->shouldHaveType('Smiarowski\Generators\Commands\ApiResourceMakeCommand');
    }
}

<?php

namespace Eniams\FooBundle\Tests\App\Func;

class FooServiceTest extends KernelTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testItSuccess(): void
    {
        $fooService = self::$container->get('eniams.foo.such_service');
        $this->assertInstanceOf(FooService::class, $fooService);
        //...
    }
}
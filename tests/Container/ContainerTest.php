<?php

namespace Resta\Core\Tests\Container;

use Resta\Foundation\Application;
use Resta\Core\Tests\AbstractTest;
use Resta\Core\Tests\Container\Dummy\ResolveDummy;

class ContainerTest extends AbstractTest
{
    /**
     * @return void|mixed
     */
    protected function setUp()
    {
        parent::setUp();
    }

    public function testInstanceResolve()
    {
        $this->assertSame(1,(new ResolveDummy(app()))->getCounter());
        $this->assertSame(2,(new ResolveDummy(app()))->getCounter());

        $this->assertInstanceOf(ResolveDummy::class,static::$app->resolve(ResolveDummy::class));
        $this->assertInstanceOf(Application::class,static::$app->resolve(ResolveDummy::class)->app());
        $this->assertSame(null,static::$app->resolve(ResolveDummy::class)->getDummy());
        $this->assertSame(3,static::$app->resolve(ResolveDummy::class)->getCounter());
        $this->assertSame(3,static::$app->resolve(ResolveDummy::class)->getCounter());
        $this->assertSame(1,app()->resolve(ResolveDummy::class,['dummy'=>1])->getDummy());
        $this->assertSame(4,app()->resolve(ResolveDummy::class,['dummy'=>1])->getCounter());

        $this->assertInstanceOf(ResolveDummy::class,app()->resolve(ResolveDummy::class));
        $this->assertSame(5,app()->resolve(ResolveDummy::class)->getCounter());
        $this->assertInstanceOf(Application::class,app()->resolve(ResolveDummy::class)->app());
        $this->assertSame(null,app()->resolve(ResolveDummy::class)->getDummy());
        $this->assertSame(5,app()->resolve(ResolveDummy::class)->getCounter());
        $this->assertSame(1,app()->resolve(ResolveDummy::class,['dummy'=>1])->getDummy());
        $this->assertSame(6,app()->resolve(ResolveDummy::class,['dummy'=>1])->getCounter());
    }


    /**
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function testShareContainer()
    {
        $this->assertTrue(true,static::$app->console());

        static::$app->share("consoleShareControl",function()
        {
           return ResolveDummy::class;
        });


        $this->assertTrue(true,static::$app['consoleShared']['consoleShareControl']);
        $this->assertSame("Resta\Core\Tests\Container\Dummy\ResolveDummy",static::$app['consoleShared']['consoleShareControl']);
        $this->assertSame("Resta\Core\Tests\Container\Dummy\ResolveDummy",get_class(static::$app['consoleShareControl']));
        $this->assertInstanceOf(ResolveDummy::class,static::$app['consoleShareControl']);
    }

    /**
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function testMakeContainer()
    {
        static::$app->make("consoleShareControl2",function()
        {
            return ResolveDummy::class;
        });

        $this->assertFalse(false,isset(static::$app['consoleShared']['consoleShareControl2']));
        $this->assertFalse(false,isset(static::$app['bindings']['consoleShareControl2']));

    }
}
<?php

/*
 * This file is part of the aether/aether.
 *
 * Copyright (C) 2024 Dominik Szamburski
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file for details.
 */

namespace Aether\Tests\DependencyInjection\Definition\Resolver;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Aether\DependencyInjection\Container;
use Aether\DependencyInjection\Definition\Binding\Alias;
use Aether\DependencyInjection\Definition\Binding\Scalar;
use Aether\DependencyInjection\Definition\Exception\CircularDependencyException;
use Aether\DependencyInjection\Definition\Resolver\DefinitionResolver;
use Aether\DependencyInjection\Definition\Resolver\ParameterResolver;
use Aether\DependencyInjection\Definition\State;
use Aether\DependencyInjection\Exception\ContainerException;
use Aether\DependencyInjection\Exception\EntryNotFoundException;
use Aether\Tests\DependencyInjection\Fixtures\ClassACircularDependency;
use Aether\Tests\DependencyInjection\Fixtures\SampleClass;
use Aether\Tests\DependencyInjection\Fixtures\SampleInterface;

#[CoversClass(Alias::class)]
#[CoversClass(Scalar::class)]
#[CoversClass(Container::class)]
#[CoversClass(DefinitionResolver::class)]
#[CoversClass(ParameterResolver::class)]
#[CoversClass(EntryNotFoundException::class)]
class DefinitionResolverTest extends TestCase
{
    private State $state;
    private DefinitionResolver $resolver;

    public function setUp(): void
    {
        $this->state = new State();
        $this->resolver = new DefinitionResolver($this->state, new Container());
    }

    public function testReturnInstanceIfWasPreviouslyResolved(): void
    {
        $this->state->instances[SampleClass::class] = new SampleClass();

        self::assertInstanceOf(
            SampleClass::class,
            $this->resolver->make(SampleClass::class)
        );
    }

    public function testAutowiringAbstractWhileBindingNotFound(): void
    {
        self::assertInstanceOf(
            SampleClass::class,
            $this->resolver->make(SampleClass::class)
        );
    }

    public function testCircularDependencyThrowsException(): void
    {
        $this->expectException(CircularDependencyException::class);
        $this->resolver->make(ClassACircularDependency::class);
    }

    public function testResolverThrowsExceptionIfClassNotFound(): void
    {
        self::expectException(EntryNotFoundException::class);
        self::expectExceptionMessage('Undefined entry `test`');

        $this->resolver->make('test');
    }

    public function testResolverThrowsExceptionIfClassIsNotInstantiable(): void
    {
        self::expectException(ContainerException::class);
        self::expectExceptionMessage(SampleInterface::class . ' is not instantiable.');

        $this->resolver->make(SampleInterface::class);
    }

    public function testResolverCanReturnScalarValue(): void
    {
        $this->state->bindings['foo'] = new Scalar('bar');

        self::assertSame(
            'bar',
            $this->resolver->make('foo')
        );
    }
}

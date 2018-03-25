<?php

namespace Symfony\Component\DependencyInjection;

interface ContainerInterface {
    public function hasParameter(string $name) : bool;
    public function getParameter(string $name) : string ;
}

class Container implements ContainerInterface {

    public function hasParameter(string $name): bool
    {
        return false;
    }

    public function getParameter(string $name): string
    {
        return false;
    }
}
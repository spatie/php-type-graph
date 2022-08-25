<?php

namespace Spatie\PhpTypeGraph\Actions;

use League\ConstructFinder\ConstructFinder;
use Spatie\PhpTypeGraph\Collections\InvertedClassReferenceMap;
use Spatie\PhpTypeGraph\ValueObjects\ClassReferences;
use Illuminate\Support\Collection;
use Spatie\LaravelAutoDiscoverer\Discover;

class ResolveInvertedClassReferenceMapAction
{
    /** @return InvertedClassReferenceMap<string, ClassReferences> */
    public function execute(): InvertedClassReferenceMap
    {
        /** @var InvertedClassReferenceMap<string, ClassReferences> $map */
        $map = new InvertedClassReferenceMap();

        foreach (ConstructFinder::locatedIn(__DIR__.'/../')->findAll() as $construct){
            $class = $construct->name();

            foreach (class_implements($class) as $interface) {
                if ($map->has($interface)) {
                    $map[$interface]->implementedBy[] = $class;
                } else {
                    $map[$interface] = new ClassReferences($interface, implementedBy: [$class]);
                }
            }

            foreach (class_parents($class) as $parent) {
                if ($map->has($parent)) {
                    $map[$parent]->extendedBy[] = $class;
                } else {
                    $map[$parent] = new ClassReferences($parent, extendedBy: [$class]);
                }

                foreach (class_uses($parent) as $trait) {
                    if ($map->has($trait)) {
                        $map[$trait]->usedBy[] = $class;
                    } else {
                        $map[$trait] = new ClassReferences($trait, usedBy: [$class]);
                    }
                }
            }

            foreach (class_uses($class) as $trait) {
                if ($map->has($trait)) {
                    $map[$trait]->usedBy[] = $class;
                } else {
                    $map[$trait] = new ClassReferences($trait, usedBy: [$class]);
                }
            }

            if (! $map->has($class)) {
                $map[$class] = new ClassReferences($class);
            }
        }

        return $map;
    }
}

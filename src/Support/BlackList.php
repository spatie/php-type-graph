<?php

namespace Spatie\PhpTypeGraph\Support;

/**
 * A list of classes which are incomplete and throw uncatchable errors
 */
class BlackList
{
    public static array $entries = [
        'Aws\Crypto\Polyfill\ByteArray',
        'Aws\Handler\GuzzleV5\GuzzleStream',
        'Aws\Handler\GuzzleV5\PsrStream',
        'Doctrine\Common\Cache\Psr6\CacheItem',
        'Google\Auth\Cache\Item',
        'Google\Cloud\Core\Logger\AppEngineFlexFormatter',
        'Google\Cloud\Core\Logger\AppEngineFlexHandler',
        'Google\Cloud\Core\Testing\GcTestListener',
        'League\Flysystem\Cached\Storage\Noop',
        'NunoMaduro\Larastan\ApplicationResolver',
        'Psy\Readline\Hoa\FileLink',
        'Psy\Readline\Hoa\FileFinder',
        'Psy\Readline\Hoa\FileLinkRead',
        'Psy\Readline\Hoa\FileLinkReadWrite',
        'Psy\Readline\Hoa\FileLinkWrite',
        'Symfony\Bridge\PsrHttpMessage\Tests\Fixtures\App\Kernel',
        'Symfony\Bridge\PsrHttpMessage\Tests\Fixtures\App\Kernel44',
        'Symfony\Component\HttpKernel\Bundle\Bundle',
        'Symfony\Component\HttpKernel\DependencyInjection\ControllerArgumentValueResolverPass',
        'Symfony\Component\PropertyInfo\DependencyInjection\PropertyInfoConstructorPass',
        'Symfony\Component\PropertyInfo\DependencyInjection\PropertyInfoPass',
        'Symfony\Component\Routing\DependencyInjection\RoutingResolverPass',
        'Symfony\Component\Serializer\DependencyInjection\SerializerPass',
        'TheIconic\NameParser\Part\NormalisationTest'
    ];
}

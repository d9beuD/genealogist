<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Source;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class SourceFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Source::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'person' => PersonFactory::new(),
            'type' => self::faker()->randomElement([
                Source::CERT_BIRTH,
                Source::CERT_BAPTISM,
                Source::CERT_MARRIAGE,
                Source::CERT_DEATH,
                Source::CERT_MILITARY,
                Source::CERT_OTHER,
            ]),
            'url' => self::faker()->url(),
            'comment' => self::faker()->optional()->sentence(),
            'directProof' => self::faker()->boolean(70),
        ];
    }
}

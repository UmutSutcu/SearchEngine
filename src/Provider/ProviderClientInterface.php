<?php
namespace App\Provider;

interface ProviderClientInterface
{
    /** Normalize edilmiş itemlar döner */
    public function fetch(): iterable;

    /** 'json' | 'xml' gibi benzersiz ad */
    public function name(): string;
}

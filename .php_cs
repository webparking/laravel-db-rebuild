<?php

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PSR2' => true,
        '@PHP71Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'compact_nullable_typehint' => true,
        'concat_space' => ['spacing' => 'one'],
        'phpdoc_separation' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()->in(__DIR__)
    );

<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

$config = (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'native_function_invocation' => ['include' => ['@all']],
        'native_constant_invocation' => true,
        'ordered_imports' => true,
        'declare_strict_types' => true,
        'linebreak_after_opening_tag' => true,
        'single_import_per_statement' => true,
        'blank_line_after_opening_tag' => true,
        'concat_space' => ['spacing' => 'one'],
        'phpdoc_align' => ['align' => 'left'],
    ]);

return $config
    ->setRiskyAllowed(true)
    ->setFinder($finder);

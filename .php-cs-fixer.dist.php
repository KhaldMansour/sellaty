<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__) 
    ->notPath('vendor')
    ->notPath('bootstrap/cache')
    ->name('*.php');

return (new Config())
    ->setRules([
        '@PSR12' => true, 
        'new_with_braces' => false,
        'no_unused_imports' => true,
        'no_trailing_whitespace' => true,
        'blank_line_after_namespace' => true,
        'align_multiline_comment' => true,
        'braces' => true,
        'indentation_type' => true,
        'binary_operator_spaces' => ['default' => 'single_space'],
        'blank_line_before_statement' => ['statements' => ['return']],
        'array_syntax' => ['syntax' => 'short'],
        'no_closing_tag' => true
    ])
    ->setFinder($finder);

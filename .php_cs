<?php

$finder = Symfony\Component\Finder\Finder::create()
    ->files()
    ->in(__DIR__.'/config')
    ->in(__DIR__.'/lang')
    ->in(__DIR__.'/src')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers([
        '-psr0',
        'extra_empty_lines',
        'multiline_array_trailing_comma',
        'no_blank_lines_after_class_opening',
        'no_blank_lines_before_namespace',
        'no_empty_lines_after_phpdocs',
        'remove_leading_slash_use',
        'remove_lines_between_uses',
        'short_array_syntax',
    ])
    ->finder($finder);

<?php

namespace Tests\Feature;

use Tests\TestCase;

class ViewCompiledPathTest extends TestCase
{
    public function test_compiled_blade_path_is_the_writable_framework_views_directory(): void
    {
        $compiled = config('view.compiled');

        $this->assertIsString($compiled);
        $this->assertNotSame('', $compiled);
        $this->assertDirectoryExists($compiled);
        $this->assertTrue(is_writable($compiled));
        $this->assertSame(
            realpath(storage_path('framework/views')),
            realpath($compiled)
        );
        $this->assertStringStartsWith(storage_path('framework'), $compiled);
        $this->assertFalse(str_starts_with($compiled, public_path()));
    }

    public function test_required_framework_runtime_directories_exist_and_are_not_public(): void
    {
        foreach ([
            storage_path('framework/views'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/testing'),
            storage_path('logs'),
            storage_path('app/private'),
        ] as $directory) {
            $this->assertDirectoryExists($directory);
            $this->assertFalse(str_starts_with($directory, public_path()));
        }

        $this->assertFileExists(base_path('bootstrap/cache/.gitignore'));
        $this->assertFileExists(storage_path('framework/views/.gitignore'));
        $this->assertFileExists(storage_path('app/private/.gitignore'));
    }
}

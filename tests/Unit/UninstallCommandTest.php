<?php

namespace Mrgiant\FormBuilder\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Mrgiant\FormBuilder\Tests\TestCase;

class UninstallCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_uninstall_drops_all_gl_tables(): void
    {
        // Sanity: migrations created the tables.
        $this->assertTrue(Schema::hasTable('gl_forms'));
        $this->assertTrue(Schema::hasTable('gl_answers'));

        $this->artisan('form-builder:uninstall', ['--force' => true])->assertSuccessful();

        foreach (['gl_forms', 'gl_questions', 'gl_answers', 'gl_form_responses', 'gl_form_workflow_nodes'] as $table) {
            $this->assertFalse(Schema::hasTable($table), "$table should have been dropped");
        }
    }

    public function test_keep_data_preserves_tables(): void
    {
        $this->artisan('form-builder:uninstall', ['--force' => true, '--keep-data' => true])->assertSuccessful();

        $this->assertTrue(Schema::hasTable('gl_forms'), 'gl_forms should be kept with --keep-data');
    }
}

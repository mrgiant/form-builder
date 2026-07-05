<?php

namespace Mrgiant\FormBuilder\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UninstallCommand extends Command
{
    protected $signature = 'form-builder:uninstall
        {--force : Skip the confirmation prompt}
        {--keep-data : Leave the gl_* database tables in place}';

    protected $description = 'Uninstall Form Builder: drop its tables, remove published assets, and unwire resources/js/app.js.';

    /**
     * Child-before-parent order so foreign keys never block a drop, even on
     * engines where constraints can't be toggled off.
     */
    private const TABLES = [
        'gl_answers',
        'gl_form_response_workflow_actions',
        'gl_form_response_workflows',
        'gl_form_workflow_edges',
        'gl_form_workflow_node_users',
        'gl_form_workflow_nodes',
        'gl_questions',
        'gl_form_responses',
        'gl_forms',
    ];

    public function handle(): int
    {
        $this->components->warn('This removes Form Builder from your application.');

        if (! $this->option('force')
            && ! $this->confirm('Drop the gl_* tables and delete published Form Builder files? This cannot be undone.')) {
            $this->components->info('Aborted — nothing changed.');

            return self::SUCCESS;
        }

        if ($this->option('keep-data')) {
            $this->components->task('Kept gl_* tables (--keep-data)', fn () => true);
        } else {
            $this->dropTables();
        }

        $this->removePublishedFiles();
        $this->unwireAppJs();

        $this->newLine();
        $this->components->info('Form Builder uninstalled. Final step:');
        $this->components->bulletList([
            'composer remove mrgiant/form-builder',
            'npm run build',
        ]);

        return self::SUCCESS;
    }

    private function dropTables(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();

        // Forget the ledger rows so a later re-install migrates cleanly.
        $migrations = config('database.migrations');
        $migrationsTable = is_array($migrations) ? ($migrations['table'] ?? 'migrations') : ($migrations ?: 'migrations');

        if (Schema::hasTable($migrationsTable)) {
            DB::table($migrationsTable)->where('migration', 'like', '%_create_gl_%')->delete();
        }

        $this->components->task('Dropped gl_* tables', fn () => true);
    }

    private function removePublishedFiles(): void
    {
        $fs = new Filesystem;

        foreach ([resource_path('js/vendor/form-builder'), resource_path('views/vendor/form-builder')] as $dir) {
            if ($fs->isDirectory($dir)) {
                $fs->deleteDirectory($dir);
            }
        }

        if ($fs->exists($config = config_path('form-builder.php'))) {
            $fs->delete($config);
        }

        $this->components->task('Removed published assets and config', fn () => true);
    }

    private function unwireAppJs(): void
    {
        $appJs = resource_path('js/app.js');

        if (! is_file($appJs)) {
            return;
        }

        $contents = file_get_contents($appJs);

        if (! str_contains($contents, 'registerFormBuilder')) {
            return;
        }

        // Drop every line that mentions registerFormBuilder (the import and the call).
        $kept = array_filter(
            preg_split('/\R/', $contents),
            fn ($line) => ! str_contains($line, 'registerFormBuilder')
        );

        file_put_contents($appJs, implode("\n", $kept));

        $this->components->task('Removed registerFormBuilder from resources/js/app.js', fn () => true);
    }
}

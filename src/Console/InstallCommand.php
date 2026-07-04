<?php

namespace Mrgiant\FormBuilder\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'form-builder:install
        {--force : Overwrite any existing published files}';

    protected $description = 'Install the Form Builder package: publish config, publish Vue components, and wire them into resources/js/app.js.';

    public function handle(): int
    {
        $this->components->info('Installing Form Builder…');

        $this->publishTag('form-builder-config', 'config → config/form-builder.php');
        $this->publishTag('form-builder-assets', 'Vue components → resources/js/vendor/form-builder');

        $this->registerVueComponents();

        $this->newLine();
        $this->components->info('Form Builder installed. Remaining steps:');
        $this->components->bulletList([
            'php artisan migrate            (creates the gl_* tables — run your auth/users migration first)',
            'npm install vuedraggable golden-logic-ui   (required peer dependencies)',
            'npm run build                  (or: npm run dev)',
        ]);

        return self::SUCCESS;
    }

    private function publishTag(string $tag, string $label): void
    {
        $this->callSilently('vendor:publish', [
            '--tag' => $tag,
            '--force' => (bool) $this->option('force'),
        ]);

        $this->components->task($label, fn () => true);
    }

    /**
     * Idempotently add the registerFormBuilder import + call to resources/js/app.js.
     * Best-effort: if the file or a mount() call can't be found, it prints the
     * snippet to paste instead of guessing.
     */
    private function registerVueComponents(): void
    {
        $appJs = resource_path('js/app.js');

        if (! is_file($appJs)) {
            $this->components->warn('resources/js/app.js not found — add these two lines to your Vue entrypoint manually:');
            $this->printSnippet();

            return;
        }

        $contents = file_get_contents($appJs);

        if (str_contains($contents, 'registerFormBuilder')) {
            $this->components->task('Vue components already wired in resources/js/app.js', fn () => true);

            return;
        }

        $import = "import { registerFormBuilder } from './vendor/form-builder';\n";

        // Insert the registration just before the app is mounted, if we can find it.
        if (preg_match('/(\b[\w$]+)\.mount\s*\(/', $contents, $m)) {
            $appVar = $m[1];
            $contents = $import.$contents;
            $contents = preg_replace(
                '/(\b'.preg_quote($appVar, '/').'\.mount\s*\()/',
                "registerFormBuilder({$appVar});\n\n$1",
                $contents,
                1
            );

            file_put_contents($appJs, $contents);
            $this->components->task('Wired components into resources/js/app.js', fn () => true);

            return;
        }

        $this->components->warn("Couldn't locate a `.mount(` call in resources/js/app.js — add these lines manually:");
        $this->printSnippet();
    }

    private function printSnippet(): void
    {
        $this->newLine();
        $this->line("  <fg=gray>import { registerFormBuilder } from './vendor/form-builder';</>");
        $this->line('  <fg=gray>registerFormBuilder(app);   // before app.mount(...)</>');
        $this->newLine();
    }
}

<?php

namespace Mrgiant\FormBuilder\Tests\Unit;

use Illuminate\Support\Facades\View;
use Mrgiant\FormBuilder\Tests\TestCase;

class ViewResolutionTest extends TestCase
{
    public function test_bundled_views_resolve_by_bare_name(): void
    {
        $this->assertTrue(View::exists('admin.forms.index'), 'admin.forms.index should resolve to the bundled view');
        $this->assertTrue(View::exists('admin.responses.index'));
        $this->assertTrue(View::exists('forms.custom_render'));
        $this->assertTrue(View::exists('admin.approvals.inbox'));
    }

    public function test_bundled_views_also_resolve_under_namespace(): void
    {
        $this->assertTrue(View::exists('form-builder::admin.forms.index'));
    }
}

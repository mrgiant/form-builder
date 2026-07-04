<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the earlier approval-only tables (local dev, no data to preserve).
        Schema::dropIfExists('form_response_approval_actions');
        Schema::dropIfExists('form_response_approvals');
        Schema::dropIfExists('form_approval_step_users');
        Schema::dropIfExists('form_approval_steps');

        // Typed workflow nodes (approval | notification | condition).
        Schema::create('form_workflow_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('type');            // approval | notification | condition
            $table->string('name');
            $table->string('mode')->nullable(); // approval: any | all
            $table->json('config')->nullable(); // condition/notification settings
            $table->integer('pos_x')->nullable();
            $table->integer('pos_y')->nullable();
            $table->timestamps();
        });

        // Users tied to a node (approvers for approval nodes, recipients for notification nodes).
        Schema::create('form_workflow_node_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('form_workflow_nodes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['node_id', 'user_id']);
        });

        // Directed edges. from_node_id null = from Start; to_node_id null = to End (Approved).
        Schema::create('form_workflow_edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->foreignId('from_node_id')->nullable()->constrained('form_workflow_nodes')->cascadeOnDelete();
            $table->foreignId('to_node_id')->nullable()->constrained('form_workflow_nodes')->cascadeOnDelete();
            $table->string('branch')->default('next'); // next | true | false
            $table->timestamps();
        });

        // Per-response workflow run (tracker head).
        Schema::create('form_response_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_response_id')->constrained('form_responses')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending | approved | rejected
            $table->foreignId('current_node_id')->nullable()->constrained('form_workflow_nodes')->nullOnDelete();
            $table->timestamps();
        });

        // Timeline of what happened (approvals, condition results, notifications).
        Schema::create('form_response_workflow_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_response_id')->constrained('form_responses')->cascadeOnDelete();
            $table->foreignId('node_id')->nullable()->constrained('form_workflow_nodes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // approved | rejected | condition_true | condition_false | notified
            $table->text('comment')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_response_workflow_actions');
        Schema::dropIfExists('form_response_workflows');
        Schema::dropIfExists('form_workflow_edges');
        Schema::dropIfExists('form_workflow_node_users');
        Schema::dropIfExists('form_workflow_nodes');
    }
};

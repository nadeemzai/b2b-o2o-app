<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $t  = config('permission.table_names');
        $c  = config('permission.column_names');

        Schema::create($t['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($t['roles'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($t['model_has_permissions'], function (Blueprint $table) use ($t, $c) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger($c['model_morph_key']);
            $table->index([$c['model_morph_key'], 'model_type'], 'mhp_model_index');
            $table->foreign('permission_id')->references('id')->on($t['permissions'])->onDelete('cascade');
            $table->primary(['permission_id', $c['model_morph_key'], 'model_type'], 'mhp_primary');
        });

        Schema::create($t['model_has_roles'], function (Blueprint $table) use ($t, $c) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger($c['model_morph_key']);
            $table->index([$c['model_morph_key'], 'model_type'], 'mhr_model_index');
            $table->foreign('role_id')->references('id')->on($t['roles'])->onDelete('cascade');
            $table->primary(['role_id', $c['model_morph_key'], 'model_type'], 'mhr_primary');
        });

        Schema::create($t['role_has_permissions'], function (Blueprint $table) use ($t) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->foreign('permission_id')->references('id')->on($t['permissions'])->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on($t['roles'])->onDelete('cascade');
            $table->primary(['permission_id', 'role_id']);
        });

        app()['cache']->store(config('permission.cache.store') != 'default'
            ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $t = config('permission.table_names');
        Schema::drop($t['role_has_permissions']);
        Schema::drop($t['model_has_roles']);
        Schema::drop($t['model_has_permissions']);
        Schema::drop($t['roles']);
        Schema::drop($t['permissions']);
    }
};

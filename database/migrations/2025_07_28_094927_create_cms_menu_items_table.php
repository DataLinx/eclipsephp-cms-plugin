<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->foreignId('menu_id')
                ->constrained('cms_menus', 'id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('type');
            $table->string('linkable_class');
            $table->string('linkable_id');
            $table->boolean('new_tab')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_menu_items');
    }
};

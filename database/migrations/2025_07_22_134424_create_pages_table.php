<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('section_id')
                ->constrained('cms_sections', 'id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->text('short_text')->nullable();
            $table->mediumText('long_text')->nullable();
            $table->string('sef_key');
            $table->string('code')->nullable();
            $table->string('status');
            $table->string('type');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_pages');
    }
};

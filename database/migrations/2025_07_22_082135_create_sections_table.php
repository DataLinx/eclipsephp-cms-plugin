<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cms_sections', function (Blueprint $table) {
            $table->id();
            $table->string('type');

            if (config('eclipse-cms.tenancy.enabled')) {
                $tenantClass = config('eclipse-cms.tenancy.model');
                /** @var \Illuminate\Database\Eloquent\Model $tenant */
                $tenant = new $tenantClass;
                $table->foreignId(config('eclipse-cms.tenancy.foreign_key'))
                        ->constrained($tenant->getTable(), $tenant->getKeyName())
                        ->cascadeOnUpdate()
                        ->cascadeOnDelete();
            }

            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_sections');
    }
};

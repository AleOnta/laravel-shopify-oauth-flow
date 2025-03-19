<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('shopify_stores', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name');
            $table->string('shop_url')->nullable();
            $table->string('shop_domain')->unique();
            $table->string('shop_currency')->nullable();
            $table->string('shop_country')->nullable();
            $table->string('shop_owner_fullname')->nullable();
            $table->string('shop_owner_email');
            $table->string('shop_contact_email')->nullable();
            $table->string('access_token');
            $table->boolean('is_active');
            $table->timestamp('installed_at');
            $table->timestamp('uninstalled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify_stores');
    }
};

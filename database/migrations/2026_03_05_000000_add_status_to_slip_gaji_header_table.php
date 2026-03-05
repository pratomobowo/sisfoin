<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slip_gaji_header', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published'])->default('draft')->after('mode');
            $table->timestamp('published_at')->nullable()->after('uploaded_at');
            $table->unsignedBigInteger('published_by')->nullable()->after('published_at');

            $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::table('slip_gaji_header', function (Blueprint $table) {
            $table->dropForeign(['published_by']);
            $table->dropIndex(['status', 'periode']);
            $table->dropColumn(['status', 'published_at', 'published_by']);
        });
    }
};

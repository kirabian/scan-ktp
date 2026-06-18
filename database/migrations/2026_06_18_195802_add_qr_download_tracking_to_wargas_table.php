<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wargas', function (Blueprint $table) {
            $table->integer('qr_download_count')->default(0)->after('pekerjaan');
            $table->timestamp('last_qr_download_at')->nullable()->after('qr_download_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wargas', function (Blueprint $table) {
            $table->dropColumn(['qr_download_count', 'last_qr_download_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::table('employees', function (Blueprint $table): void {
         $table->string('rfid_code', 150)->nullable()->unique()->after('pin');
         $table->string('qr_code_data', 500)->nullable()->unique()->after('rfid_code');
         $table->string('face_recognition_hash', 255)->nullable()->unique()->after('qr_code_data');
      });
   }

   public function down(): void
   {
      Schema::table('employees', function (Blueprint $table): void {
         $table->dropUnique(['rfid_code']);
         $table->dropUnique(['qr_code_data']);
         $table->dropUnique(['face_recognition_hash']);
         $table->dropColumn(['rfid_code', 'qr_code_data', 'face_recognition_hash']);
      });
   }
};
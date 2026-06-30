<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('claim_documents', function (Blueprint $table) {
            $table->uuid('document_id')->primary();
            $table->foreignUuid('claim_id')->constrained('claims', 'claim_id')->onDelete('cascade');
            $table->foreignUuid('document_type_id')->constrained('document_types', 'document_types_id');
            $table->string('file_name', 255)->nullable();
            $table->string('original_filename', 255)->nullable();
            $table->string('stored_filename', 255)->nullable();
            $table->string('file_path', 255)->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->foreignUuid('uploaded_by')->nullable()->constrained('users', 'user_id');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();

            $table->index(['claim_id']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_documents');
    }
};

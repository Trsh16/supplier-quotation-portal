
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('normalized_company_name')->index();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('measurement_units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('dimension', 30)->nullable();
            $table->foreignId('base_unit_id')
                ->nullable()
                ->constrained('measurement_units')
                ->nullOnDelete();
            $table->decimal('conversion_factor', 15, 6)
                ->default(1);
            $table->timestamps();
        });

        Schema::create('standard_goods', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->nullable()->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('base_unit_id')
                ->constrained('measurement_units')
                ->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('supplier_sales_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_token')->unique();

            $table->foreignId('supplier_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('sales_contact_id')
                ->nullable()
                ->constrained('supplier_sales_contacts')
                ->nullOnDelete();

            // Snapshot kontak sales pada saat penawaran dikirim.
            // Perubahan kontak di masa depan tidak mengubah riwayat.
            $table->string('sales_name');
            $table->string('sales_email')->nullable();
            $table->string('sales_phone', 50)->nullable();

            $table->string('currency', 3)->default('IDR');

            $table->enum('vat_type', ['included', 'excluded']);
            $table->decimal('vat_rate', 5, 2)->default(0);

            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'accepted',
                'rejected',
                'expired',
            ])->default('draft');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'submitted_at']);
            $table->index(['status', 'valid_until']);
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quotation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('standard_good_id')
                ->nullable()
                ->constrained('standard_goods')
                ->nullOnDelete();

            // Nama dan satuan sebagaimana ditulis supplier.
            $table->string('supplier_item_name');
            $table->string('supplier_item_code')->nullable();
            $table->string('supplier_unit_text', 100);

            // Satuan standar yang dipilih untuk perbandingan.
            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('measurement_units')
                ->nullOnDelete();

            // Jumlah satuan dasar dalam satu unit penawaran.
            // Contoh: 1 dus berisi 12 buah = 12.
            $table->decimal('quantity_in_base_unit', 15, 6)
                ->default(1);

            $table->decimal('quoted_price', 15, 2);
            $table->decimal('minimum_order_quantity', 15, 4)
                ->default(1);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['standard_good_id', 'quoted_price']);
        });

        Schema::create('quotation_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quotation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_documents');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('supplier_sales_contacts');
        Schema::dropIfExists('standard_goods');
        Schema::dropIfExists('measurement_units');
        Schema::dropIfExists('suppliers');
    }
};

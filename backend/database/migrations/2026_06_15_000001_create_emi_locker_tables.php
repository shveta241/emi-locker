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
        // Add role to users table (Admin or Retailer)
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('retailer')->after('email'); // admin, retailer
        });

        // Customers table
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('aadhaar_number')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Retailer who created them
            $table->timestamps();
        });

        // Devices table
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('imei_1')->unique();
            $table->string('imei_2')->nullable()->unique();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('fcm_token')->nullable();
            $table->enum('status', ['LOCKED', 'UNLOCKED', 'REMINDED'])->default('UNLOCKED');
            $table->string('bypass_code')->default('998877'); // Local master offline bypass code
            $table->string('upi_id')->nullable(); // Retailer custom UPI ID for receiving payments
            $table->timestamps();
        });

        // EMI Installments table
        Schema::create('emi_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->enum('status', ['PENDING', 'PAID', 'OVERDUE'])->default('PENDING');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable(); // UPI, Cash, etc.
            $table->string('transaction_reference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emi_installments');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('customers');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};

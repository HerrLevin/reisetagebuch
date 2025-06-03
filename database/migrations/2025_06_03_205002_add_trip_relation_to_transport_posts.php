<?php

use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_posts', function (Blueprint $table) {
            $table->foreignIdFor(TransportTrip::class)
                ->nullable()
                ->constrained();
            $table->foreignIdFor(TransportTripStop::class, 'origin_stop_id')
                ->nullable()
                ->constrained();
            $table->foreignIdFor(TransportTripStop::class, 'destination_stop_id')
                ->nullable()
                ->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('transport_posts', function (Blueprint $table) {
            $table->dropForeignIdFor(TransportTrip::class);
            $table->dropForeignIdFor(TransportTripStop::class, 'origin_stop_id');
            $table->dropForeignIdFor(TransportTripStop::class, 'destination_stop_id');
        });
    }
};

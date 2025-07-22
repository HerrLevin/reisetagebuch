<?php

use App\Models\Location;
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
            $table->dropForeign(['origin_id']);
            $table->dropForeign(['destination_id']);
            $table->dropColumn(['departure', 'arrival', 'departure_delay', 'arrival_delay', 'mode', 'line']);

            $table->dropForeignIdFor(Location::class, 'destination_id');
            $table->dropForeignIdFor(Location::class, 'origin_id');

            // change nullable foreign keys to not nullable
            $table->foreignIdFor(TransportTripStop::class, 'destination_stop_id')->nullable(false)->change();
            $table->foreignIdFor(TransportTripStop::class, 'origin_stop_id')->nullable(false)->change();
            $table->foreignIdFor(TransportTrip::class)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('transport_posts', function (Blueprint $table) {
            $table->foreignIdFor(Location::class, 'origin_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Location::class, 'destination_id')->nullable()->constrained()->cascadeOnDelete();
            $table->dateTimeTz('departure');
            $table->dateTimeTz('arrival');
            $table->integer('departure_delay')->nullable();
            $table->integer('arrival_delay')->nullable();
            $table->string('mode');
            $table->string('line')->nullable();
        });
    }
};

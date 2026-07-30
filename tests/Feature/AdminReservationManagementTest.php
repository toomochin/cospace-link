<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminReservationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_shop_totals_and_export_csv(): void
    {
        $admin = User::factory()->create(['role' => 'system_admin']);
        $shop = Shop::factory()->create(['name' => '対象店舗']);
        $otherShop = Shop::factory()->create(['name' => '対象外店舗']);
        $facility = Facility::factory()->for($shop)->create(['name' => '対象施設']);
        $otherFacility = Facility::factory()->for($otherShop)->create(['name' => '対象外施設']);
        Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'price' => 1500,
            'status' => 'confirmed',
        ]);
        $refunded = Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'price' => 500,
            'status' => 'cancelled',
        ]);
        Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $otherFacility->id,
            'price' => 9999,
            'status' => 'confirmed',
        ]);
        DB::table('payments')->insert([
            'reservation_id' => $refunded->id,
            'stripe_payment_intent_id' => 'pi_admin_refunded',
            'stripe_refund_id' => 're_admin_refunded',
            'amount' => 500,
            'status' => 'refunded',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reservations.index', [
            'shop_id' => $shop->id,
        ]));
        $response->assertOk()->assertSee('対象店舗');
        $this->assertSame(1500, (int) $response->viewData('confirmedSales'));
        $this->assertSame(500, (int) $response->viewData('refundedAmount'));
        $this->assertCount(2, $response->viewData('reservations'));

        $export = $this->actingAs($admin)->get(route('admin.reservations.export', [
            'shop_id' => $shop->id,
        ]));
        $export->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $csv = $export->streamedContent();
        $this->assertStringContainsString('対象店舗', $csv);
        $this->assertStringContainsString('対象施設', $csv);
        $this->assertStringNotContainsString('対象外店舗', $csv);
        $this->assertStringNotContainsString('9999', $csv);
    }
}

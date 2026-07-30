<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OwnerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_contains_only_own_shop_daily_metrics(): void
    {
        $shop = Shop::factory()->create();
        $owner = User::factory()->create(['role' => 'shop_owner', 'shop_id' => $shop->id]);
        $facility = Facility::factory()->for($shop)->create(['is_active' => true]);
        Facility::factory()->for($shop)->create(['is_active' => true]);
        Facility::factory()->for($shop)->create(['is_active' => false]);
        $otherFacility = Facility::factory()->create(['is_active' => true]);
        Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'start_time' => today()->setTime(10, 0),
            'end_time' => today()->setTime(11, 0),
            'price' => 1200,
            'status' => 'confirmed',
        ]);
        Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'start_time' => today()->addDay()->setTime(10, 0),
            'end_time' => today()->addDay()->setTime(11, 0),
            'price' => 1800,
            'status' => 'confirmed',
        ]);
        Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $otherFacility->id,
            'start_time' => today()->setTime(10, 0),
            'end_time' => today()->setTime(11, 0),
            'price' => 9999,
            'status' => 'confirmed',
        ]);
        $cancelled = Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'status' => 'cancelled',
        ]);
        DB::table('payments')->insert([
            'reservation_id' => $cancelled->id,
            'stripe_payment_intent_id' => 'pi_owner_dashboard',
            'stripe_refund_id' => 're_owner_dashboard',
            'amount' => 500,
            'status' => 'refunded',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($owner)->get(route('owner.dashboard'));

        $response->assertOk()
            ->assertSee('本日の状況')
            ->assertSee('本日の確定売上')
            ->assertSee('本日の返金額');
        $this->assertSame(1, $response->viewData('todayReservationsCount'));
        $this->assertSame(1200, (int) $response->viewData('todaySales'));
        $this->assertSame(500, (int) $response->viewData('todayRefundedAmount'));
        $this->assertSame(2, $response->viewData('activeFacilitiesCount'));
        $this->assertSame(1, $response->viewData('todayOperatingFacilitiesCount'));
        $this->assertSame(50.0, $response->viewData('todayFacilityUtilizationRate'));
        $this->assertSame(2, $response->viewData('reservationCount'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_portal_totals_and_today_utilization(): void
    {
        $admin = User::factory()->create(['role' => 'system_admin']);
        $activeShop = Shop::factory()->create([
            'name' => 'ダッシュボード店舗',
            'area_name' => '渋谷',
            'is_active' => true,
        ]);
        $inactiveShop = Shop::factory()->create(['is_active' => false]);
        $facility = Facility::factory()->for($activeShop)->create(['is_active' => true]);
        Facility::factory()->for($inactiveShop)->create(['is_active' => true]);
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
        $cancelled = Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'start_time' => today()->setTime(12, 0),
            'end_time' => today()->setTime(13, 0),
            'price' => 500,
            'status' => 'cancelled',
        ]);
        DB::table('payments')->insert([
            'reservation_id' => $cancelled->id,
            'stripe_payment_intent_id' => 'pi_dashboard_refunded',
            'stripe_refund_id' => 're_dashboard_refunded',
            'amount' => 500,
            'status' => 'refunded',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('ポータル全体の状況')
            ->assertSee('累計確定売上')
            ->assertSee('累計返金額')
            ->assertSee('ダッシュボード店舗')
            ->assertSee('渋谷');
        $this->assertSame(2, $response->viewData('totalShopsCount'));
        $this->assertSame(1, $response->viewData('activeShopsCount'));
        $this->assertSame(1, $response->viewData('activeFacilitiesCount'));
        $this->assertSame(2, $response->viewData('totalConfirmedReservationsCount'));
        $this->assertSame(3000, (int) $response->viewData('totalConfirmedSales'));
        $this->assertSame(500, (int) $response->viewData('totalRefundedAmount'));
        $this->assertSame(1, $response->viewData('todayOperatingFacilitiesCount'));
        $this->assertSame(100.0, $response->viewData('todayFacilityUtilizationRate'));
    }
}

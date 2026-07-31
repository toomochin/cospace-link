<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalFacilitySearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_facilities_by_area_amenities_and_keyword(): void
    {
        $shibuya = Shop::factory()->create([
            'name' => '渋谷ワーク',
            'area_name' => '渋谷',
            'amenities' => ['Wi-Fi', '電源'],
        ]);
        $umeda = Shop::factory()->create(['area_name' => '梅田', 'amenities' => ['Wi-Fi']]);

        Facility::factory()->for($shibuya)->create([
            'name' => '集中会議室',
            'amenities' => ['モニター'],
        ]);
        Facility::factory()->for($umeda)->create(['name' => '集中会議室']);

        $response = $this->get(route('home', [
            'area' => '渋谷',
            'amenities' => ['wifi', 'コンセント', 'モニター'],
            'keyword' => '集中',
        ]));

        $response->assertOk()->assertSee('渋谷ワーク');
        $this->assertCount(1, $response->viewData('facilities'));
    }

    public function test_facility_tag_does_not_apply_to_other_facilities_in_same_shop(): void
    {
        $shop = Shop::factory()->create(['amenities' => ['Wi-Fi']]);
        $withMonitor = Facility::factory()->for($shop)->create(['amenities' => ['モニター']]);
        Facility::factory()->for($shop)->create(['amenities' => []]);

        $response = $this->get(route('home', [
            'amenities' => ['Wi-Fi', 'モニター'],
        ]));

        $this->assertCount(1, $response->viewData('facilities'));
        $this->assertTrue($response->viewData('facilities')->first()->is($withMonitor));
    }

    public function test_search_filters_are_carried_to_detail_and_back_link(): void
    {
        $shop = Shop::factory()->create([
            'area_name' => '渋谷',
            'amenities' => ['Wi-Fi'],
        ]);
        $facility = Facility::factory()->for($shop)->create([
            'name' => '検索条件保持ルーム',
            'amenities' => ['モニター'],
        ]);
        $filters = [
            'area' => '渋谷',
            'keyword' => '条件保持',
            'amenities' => ['Wi-Fi', 'モニター'],
        ];
        $detailUrl = route('facilities.show', ['facility' => $facility->id] + $filters);

        $this->get(route('home', $filters))
            ->assertOk()
            ->assertSee($detailUrl);

        $this->get($detailUrl)
            ->assertOk()
            ->assertSee(route('home', $filters));
    }

    public function test_booked_meeting_room_is_excluded_for_requested_time(): void
    {
        $facility = Facility::factory()->create(['type' => 'meeting_room']);
        $start = Carbon::tomorrow()->setTime(10, 0);

        Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'start_time' => $start,
            'end_time' => $start->copy()->addHour(),
            'status' => 'confirmed',
        ]);

        $response = $this->get(route('home', [
            'date' => $start->format('Y-m-d'),
            'start_time' => '10:30',
            'end_time' => '11:00',
        ]));

        $response->assertSessionHasNoErrors();
        $this->assertCount(0, $response->viewData('facilities'));
    }

    public function test_facility_is_excluded_outside_shop_opening_hours(): void
    {
        $facility = Facility::factory()->create();
        $date = Carbon::tomorrow();

        $response = $this->get(route('home', [
            'date' => $date->format('Y-m-d'),
            'start_time' => '21:00',
            'end_time' => '21:30',
        ]));

        $response->assertSessionHasNoErrors();
        $this->assertNotNull($facility);
        $this->assertCount(0, $response->viewData('facilities'));
    }
}

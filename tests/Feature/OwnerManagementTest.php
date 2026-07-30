<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_own_shop_information_and_image(): void
    {
        Storage::fake('public');
        $shop = Shop::factory()->create();
        $owner = User::factory()->create(['role' => 'shop_owner', 'shop_id' => $shop->id]);

        $this->actingAs($owner)->put(route('owner.shop.update'), [
            'name' => 'CoSpace 渋谷駅前',
            'area_name' => '渋谷',
            'address' => '東京都渋谷区1-2-3',
            'access' => '渋谷駅から徒歩3分',
            'opening_hours' => '09:00-21:00',
            'description' => 'Web会議に適した店舗です。',
            'amenities' => ['Wi-Fi', '電源', 'Wi-Fi'],
            'image' => UploadedFile::fake()->image('shop.webp'),
        ])->assertRedirect(route('owner.shop.edit'));

        $shop->refresh();
        $this->assertSame('CoSpace 渋谷駅前', $shop->name);
        $this->assertSame('渋谷駅から徒歩3分', $shop->access);
        $this->assertSame(['Wi-Fi', '電源'], $shop->amenities);
        Storage::disk('public')->assertExists($shop->image_path);
    }

    public function test_owner_can_create_a_facility_only_for_own_shop(): void
    {
        $shop = Shop::factory()->create();
        $owner = User::factory()->create(['role' => 'shop_owner', 'shop_id' => $shop->id]);

        $this->actingAs($owner)->post(route('owner.facilities.store'), [
            'name' => '新規個室',
            'type' => 'meeting_room',
            'price_per_30min' => 800,
            'capacity' => 4,
            'description' => '店舗管理者が登録',
            'is_active' => true,
        ])->assertRedirect(route('owner.facilities.index'));

        $this->assertDatabaseHas('facilities', [
            'shop_id' => $shop->id,
            'name' => '新規個室',
        ]);
    }

    public function test_owner_can_update_facility_image_and_publication_status(): void
    {
        Storage::fake('public');
        $shop = Shop::factory()->create();
        $owner = User::factory()->create(['role' => 'shop_owner', 'shop_id' => $shop->id]);
        Storage::disk('public')->put('facilities/old.png', 'old-image');
        $facility = Facility::factory()->for($shop)->create(['image_path' => 'facilities/old.png']);

        $this->actingAs($owner)->put(route('owner.facilities.update', $facility), [
            'name' => '更新後の会議室',
            'type' => 'meeting_room',
            'price_per_30min' => 1200,
            'capacity' => 6,
            'equipment' => 'Wi-Fi、電源、モニター',
            'description' => '画像を更新',
            'image' => UploadedFile::fake()->image('replacement.png'),
            'is_active' => false,
        ])->assertRedirect(route('owner.facilities.index'));

        $facility->refresh();
        $this->assertSame(1200, $facility->price_per_30min);
        $this->assertSame('Wi-Fi、電源、モニター', $facility->equipment);
        $this->assertFalse($facility->is_active);
        Storage::disk('public')->assertExists($facility->image_path);
        Storage::disk('public')->assertMissing('facilities/old.png');
    }

    public function test_owner_cannot_edit_or_update_another_shops_facility(): void
    {
        $shop = Shop::factory()->create();
        $owner = User::factory()->create(['role' => 'shop_owner', 'shop_id' => $shop->id]);
        $otherFacility = Facility::factory()->create();

        $this->actingAs($owner)
            ->get(route('owner.facilities.edit', $otherFacility))
            ->assertNotFound();

        $this->actingAs($owner)->put(route('owner.facilities.update', $otherFacility), [
            'name' => '変更不可',
            'type' => 'area',
            'price_per_30min' => 500,
            'capacity' => 2,
            'is_active' => true,
        ])->assertNotFound();
    }

    public function test_owner_can_filter_reservations_and_view_refunded_amount(): void
    {
        $shop = Shop::factory()->create();
        $owner = User::factory()->create(['role' => 'shop_owner', 'shop_id' => $shop->id]);
        $facility = Facility::factory()->for($shop)->create();
        Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
            'price' => 1200,
            'status' => 'confirmed',
        ]);
        $refunded = Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'start_time' => now()->addDays(2),
            'end_time' => now()->addDays(2)->addHour(),
            'price' => 800,
            'status' => 'cancelled',
        ]);
        \DB::table('payments')->insert([
            'reservation_id' => $refunded->id,
            'stripe_payment_intent_id' => 'pi_owner_refunded',
            'stripe_refund_id' => 're_owner_refunded',
            'amount' => 800,
            'status' => 'refunded',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $date = now()->addDays(2)->format('Y-m-d');

        $response = $this->actingAs($owner)->get(route('owner.reservations.index', [
            'date_from' => $date,
            'date_to' => $date,
            'status' => 'cancelled',
        ]));

        $response->assertOk()->assertSee('キャンセル済み');
        $this->assertSame(0, $response->viewData('confirmedSales'));
        $this->assertSame(800, (int) $response->viewData('refundedAmount'));
        $this->assertCount(1, $response->viewData('reservations'));
    }

    public function test_owner_can_export_only_own_shop_reservations_as_csv(): void
    {
        $shop = Shop::factory()->create();
        $owner = User::factory()->create(['role' => 'shop_owner', 'shop_id' => $shop->id]);
        $ownedFacility = Facility::factory()->for($shop)->create(['name' => '自店舗会議室']);
        $otherFacility = Facility::factory()->create(['name' => '他店舗会議室']);
        Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $ownedFacility->id,
            'status' => 'confirmed',
            'price' => 1500,
        ]);
        Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $otherFacility->id,
            'status' => 'confirmed',
            'price' => 9999,
        ]);

        $response = $this->actingAs($owner)->get(route('owner.reservations.export', [
            'status' => 'confirmed',
        ]));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringStartsWith(pack('C*', 0xEF, 0xBB, 0xBF), $csv);
        $this->assertStringContainsString('予約ID', $csv);
        $this->assertStringContainsString('自店舗会議室', $csv);
        $this->assertStringNotContainsString('他店舗会議室', $csv);
        $this->assertStringNotContainsString('9999', $csv);
    }

    public function test_owner_reservation_page_contains_only_own_shop_reservations(): void
    {
        $shop = Shop::factory()->create();
        $owner = User::factory()->create(['role' => 'shop_owner', 'shop_id' => $shop->id]);
        $ownedFacility = Facility::factory()->for($shop)->create();
        $otherFacility = Facility::factory()->create();

        Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $ownedFacility->id,
            'price' => 1200,
            'status' => 'confirmed',
        ]);
        Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $otherFacility->id,
            'price' => 9999,
            'status' => 'confirmed',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.reservations.index'))
            ->assertOk()
            ->assertSee('1,200')
            ->assertDontSee('9,999');
    }
}

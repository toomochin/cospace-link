<?php

namespace Tests\Unit;

use App\Http\Requests\Admin\ShopRequest as AdminShopRequest;
use App\Http\Requests\FacilitySearchRequest;
use App\Http\Requests\Owner\FacilityRequest;
use App\Http\Requests\Owner\ShopRequest as OwnerShopRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FormRequestRulesTest extends TestCase
{
    public function test_valid_facility_search_passes(): void
    {
        $request = new FacilitySearchRequest;
        $validator = Validator::make([
            'area' => '渋谷',
            'date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:30',
            'amenities' => ['Wi-Fi', '電源'],
            'keyword' => '会議室',
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_time_range_is_rejected(): void
    {
        $request = new FacilitySearchRequest;
        $validator = Validator::make([
            'start_time' => '11:00',
            'end_time' => '10:00',
        ], $request->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_date_start_and_end_time_must_be_entered_together(): void
    {
        $request = new FacilitySearchRequest;

        $timeOnly = Validator::make([
            'start_time' => '09:00',
            'end_time' => '10:00',
        ], $request->rules());
        $dateOnly = Validator::make([
            'date' => now()->addDay()->format('Y-m-d'),
        ], $request->rules());

        $this->assertTrue($timeOnly->fails());
        $this->assertTrue($dateOnly->fails());
    }

    public function test_owner_shop_rules_accept_valid_data(): void
    {
        $request = new OwnerShopRequest;
        $validator = Validator::make([
            'name' => 'CoSpace 新宿',
            'area_name' => '新宿',
            'address' => '東京都新宿区1-1-1',
            'opening_hours' => '09:00-21:00',
            'amenities' => ['Wi-Fi'],
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_owner_facility_rules_require_supported_type_and_price(): void
    {
        $request = new FacilityRequest;
        $validator = Validator::make([
            'name' => '個室A',
            'type' => 'unsupported',
            'price_per_30min' => -1,
            'capacity' => 0,
        ], $request->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_admin_shop_rules_accept_valid_data(): void
    {
        $request = new AdminShopRequest;
        $validator = Validator::make([
            'name' => 'CoSpace 新宿',
            'area_name' => '新宿',
            'owner_email' => 'owner@example.com',
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }
}

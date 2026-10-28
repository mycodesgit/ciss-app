<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;

use App\Models\SettingDB\Province;
use App\Models\SettingDB\City;
use App\Models\SettingDB\Barangay;

class PortalLocationController extends Controller
{
    public function provinces($regionId)
    {
        $provinces = Province::where('region_id', $regionId)->orderBy('name')->get(['province_id', 'name']);

        return response()->json($provinces);
    }

    public function cities($provinceId)
    {
        $cities = City::where('province_id', $provinceId)->orderBy('name')->get(['city_id', 'name', 'zip_code']);

        return response()->json($cities);
    }

    public function barangays($cityId)
    {
        $barangays = Barangay::where('city_id', $cityId)->orderBy('name')->get(['id', 'name']);

        return response()->json($barangays);
    }
}

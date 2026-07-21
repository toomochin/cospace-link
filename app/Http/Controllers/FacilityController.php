<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /**
     * 施設一覧（トップ画面）表示
     */
    public function index(Request $request)
    {
        $query = Facility::where('is_active', true);

        // 種別絞り込み（meeting_room / area）
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // 施設名・説明文・設備備品のあいまい検索
        if ($request->filled('keyword')) {
            $keyword = '%' . $request->keyword . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', $keyword)
                    ->orWhere('description', 'like', $keyword)
                    ->orWhere('equipment', 'like', $keyword);
            });
        }

        $facilities = $query->get();

        return view('welcome', compact('facilities'));
    }
    /**
     * 施設詳細画面表示
     */
    public function show($id)
    {
        $facility = Facility::where('is_active', true)->findOrFail($id);

        return view('facilities.show', compact('facility'));
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FacilityStoreRequest;
use App\Models\Facility;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::orderBy('id', 'asc')->get();
        return view('admin.facilities.index', compact('facilities'));
    }

    public function create()
    {
        return view('admin.facilities.create');
    }

    public function store(FacilityStoreRequest $request)
    {
        // バリデーション済み＆is_activeが整形された安全なデータを取得
        Facility::create($request->validated());

        return redirect()->route('admin.facilities.index')->with('status', '施設を追加しました。');
    }

    public function edit($id)
    {
        $facility = Facility::findOrFail($id);
        return view('admin.facilities.edit', compact('facility'));
    }

    public function update(FacilityStoreRequest $request, $id)
    {
        $facility = Facility::findOrFail($id);

        // バリデーション済み＆is_activeが整形された安全なデータで更新
        $facility->update($request->validated());

        return redirect()->route('admin.facilities.index')->with('status', '施設情報を更新しました。');
    }
}
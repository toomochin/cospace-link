<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\FacilityRequest;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $facilities = $request->user()->shop->facilities()->orderBy('id')->get();

        return view('owner.facilities.index', compact('facilities'));
    }

    public function create()
    {
        return view('owner.facilities.create');
    }

    public function store(FacilityRequest $request)
    {
        $request->user()->shop->facilities()->create($this->data($request));

        return redirect()->route('owner.facilities.index')->with('status', '施設を追加しました。');
    }

    public function edit(Request $request, Facility $facility)
    {
        $facility = $request->user()->shop->facilities()->findOrFail($facility->id);

        return view('owner.facilities.edit', compact('facility'));
    }

    public function update(FacilityRequest $request, Facility $facility)
    {
        $facility = $request->user()->shop->facilities()->findOrFail($facility->id);
        $oldImagePath = $facility->image_path;
        $data = $this->data($request);
        $facility->update($data);

        if (isset($data['image_path']) && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()->route('owner.facilities.index')->with('status', '施設を更新しました。');
    }

    public function destroy(Request $request, Facility $facility)
    {
        $facility = $request->user()->shop->facilities()->findOrFail($facility->id);
        $facility->delete();

        return redirect()->route('owner.facilities.index')->with('status', '施設を削除しました。');
    }

    private function data(FacilityRequest $request): array
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('facilities', 'public');
        }

        unset($data['image']);

        return $data;
    }
}

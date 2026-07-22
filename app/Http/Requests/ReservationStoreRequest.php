<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ReservationStoreRequest extends FormRequest
{
    /**
     * リクエストの実行権限（ログイン済みユーザーのみ許可）
     */
    public function authorize(): bool
    {
        return true; // ルーティングの auth ミドルウェアで保護されているため true
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ];
    }

    /**
     * カスタムエラーメッセージ
     */
    public function messages(): array
    {
        return [
            'start_time.required' => '開始日時を選択してください。',
            'start_time.after' => '過去の日時は予約できません。',
            'end_time.required' => '終了日時を選択してください。',
            'end_time.after' => '終了日時は開始日時より後の時間を指定してください。',
        ];
    }

    /**
     * 基本バリデーション通過後に重複チェックを実施
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 基本ルール（date等）でエラーがある場合は重複チェックを行わない
            if ($validator->errors()->any()) {
                return;
            }

            // ルートパラメータから施設IDを取得（facility_id または id）
            $facilityId = $this->route('facility_id') ?? $this->route('id');
            $startTime = Carbon::parse($this->start_time);
            $endTime = Carbon::parse($this->end_time);

            // 重複チェック（キャンセル済みの予約は除外）
            $exists = Reservation::where('reservable_id', $facilityId)
                ->where('reservable_type', \App\Models\Facility::class)
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                })
                ->exists();

            if ($exists) {
                $validator->errors()->add('start_time', '指定された時間帯にはすでに別の予約が入っています。');
            }
        });
    }
}
<?php

namespace App\Http\Resources;

use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class OrderResource extends JsonResource
{
    public function isDelivered($endDay) {
        return Carbon::now()->gt($endDay);
    }
    public function quantity ($arr) {
        $total = 0;
        foreach($arr as $item) {
            $total += $item->count;
        }
        return $total;
    }

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'unique_id' => $this->unique_id,
            'token' => $this->token,
            'user_id' => $this->user_id,
            'product_list' => json_decode($this->product_list),
            'quantity' => $this->quantity(json_decode($this->product_list)),
            'from_date' => Carbon::parse($this->from_date)->format('d/m/Y'),
            'to_date' => Carbon::parse($this->to_date)->format('d/m/Y'),
            'purchase_method' => $this->purchase_method,
            'total_amount' => $this->total_amount,
            'name' => $this->name,
            'address' => $this->address,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'is_delivered' => $this->isDelivered($this->to_date),
            'created_at' => $this->created_at,
        ];
    }
}

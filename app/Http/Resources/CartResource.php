<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductPhotoResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'count' => $this->count,
            'product_id' => $this->product_id,
            'created_at' => $this->created_at,
            'product' => new ProductResource(Product::find($this->product_id)),
            'product_photo' => ProductPhotoResource::collection(Product::find($this->product_id)->photos)->first(),
            'date' => $this->created_at->format('d/m/Y')
        ];
    }
}

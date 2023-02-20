<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function showStatus ($stock) {
        if($stock < 1) {
            return 'Not Available';
        } else if($stock > 0) {
            return 'Available';
        }
    }

    public function returnRating ($rate) {
        $input = [];
        for ($i=0; $i<$rate; $i++) {
            $input[] = '★';
        }
        return $input;
    }


    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'unique_id' => $this->unique_id,
            'slug' => Str::slug($this->name),
            'price' => $this->price,
            'stock' => $this->stock,
            'stock_status' => $this->showStatus($this->stock),
            'description' => $this->description,
            'seller' => User::find($this->user_id)->name,
            'rating' => $this->returnRating($this->rating),
            'category' => Category::find($this->category_id)->title,
            'category_id' => $this->category_id,
            'date' => $this->created_at->format('d/m/Y'),
            'time' => $this->created_at->format('g:i a'),
            'photos' => ProductPhotoResource::collection($this->photos)
        ];
    }
}

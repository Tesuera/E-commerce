<?php

namespace App\Http\Controllers;

use App\Models\ProductPhoto;
use App\Http\Requests\StoreProductPhotoRequest;
use App\Http\Requests\UpdateProductPhotoRequest;

class ProductPhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreProductPhotoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductPhotoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductPhoto  $productPhoto
     * @return \Illuminate\Http\Response
     */
    public function show(ProductPhoto $productPhoto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductPhoto  $productPhoto
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductPhoto $productPhoto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProductPhotoRequest  $request
     * @param  \App\Models\ProductPhoto  $productPhoto
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductPhotoRequest $request, ProductPhoto $productPhoto)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductPhoto  $productPhoto
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductPhoto $productPhoto)
    {
        //
    }
}

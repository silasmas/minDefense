<?php

namespace App\Http\Controllers;

use App\Models\StateAsset;
use App\Http\Requests\StoreStateAssetRequest;
use App\Http\Requests\UpdateStateAssetRequest;

class StateAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       return view('site.pages.home');
    }
    public function about()
    {
       return view('site.pages.about');
    }

 
    public function ministre()
    {
       return view('site.pages.minstre');
    }
    public function gouvernance()
    {
       return view('site.pages.gouv');
    }
    public function contact()
    {
       return view('site.pages.contact');
    }
    public function actualites()
    {
       return view('site.pages.actualites');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStateAssetRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(StateAsset $stateAsset)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StateAsset $stateAsset)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStateAssetRequest $request, StateAsset $stateAsset)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StateAsset $stateAsset)
    {
        //
    }
}

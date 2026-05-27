<?php
use Illuminate\Http\Request;
Route::middleware("auth:api")->get("/productvault", function (Request $request) { return $request->user(); });

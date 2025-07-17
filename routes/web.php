<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

// Route::post('/test-upload', function (\Illuminate\Http\Request $request) {
//     return response()->json([
//         'csrf' => $request->header('X-CSRF-TOKEN'),
//         'session' => session()->all(),
//         'user' => auth()->user(),
//     ]);
// });
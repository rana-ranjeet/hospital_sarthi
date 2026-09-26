<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\Request;

class HospitalApiController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = trim((string) $request->query('q', ''));

        $hospitals = Hospital::query()
            ->where('is_active', true)
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($search) use ($query) {
                    $search->where('name', 'like', "%{$query}%")
                        ->orWhere('city', 'like', "%{$query}%")
                        ->orWhere('address', 'like', "%{$query}%");
                });
            })
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'city', 'address']);

        return response()->json($hospitals);
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolRequest;
use App\Models\City;
use App\Models\Country;
use App\Models\School;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
    }

    public function createCity(Request $request) 
    {
        $attr = $request->validate([
            'name' => 'required',
            'zip_code' => 'required|digits_between:3,10'
        ]);
        $city = City::create($attr);

        return response()->json($city);
    }

    public function updateCity(Request $request, $id)
    {
        $city = City::find($id);
        if(!$city) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $city->update($request->only(['name', 'zip_code']));

        return response()->json($city);
    }

    public function createCountry(Request $request)
    {
        $attr = $request->validate([
            'name' => 'required|string|min:2',
            'icon' => 'nullable',
            'icon_invert' => 'nullable'
        ]);
        $country = Country::create($attr);

        return response()->json($country);
    }

    public function createSchool(Request $request)
    {
        $attr = $request->validate([
            'name' => 'required|min:2',
            'high_school' => 'nullable|numeric',
            'color_1' => 'nullable|string|max:255',
            'color_2' => 'nullable|string|max:255',
            'logo_1' => 'nullable|string|max:255',
            'logo_2' => 'nullable|string|max:255',
            'slogan' => 'nullable|string|max:255',
            'acronym' => 'nullable|string|max:255',
            'banner' => 'nullable|string|max:255'
        ]);
        $school = School::create($attr);

        return response()->json($school);
    }
}

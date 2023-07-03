<?php

namespace App\Http\Controllers;

use App\Models\Association;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Country;
use App\Models\Hobby;
use App\Models\Industry;
use App\Models\Feedback;
use App\Models\MilitaryBranch;
use App\Models\City;
use App\Models\Degree;
use App\Models\GradLevel;
use App\Models\IbcCompany;
use App\Models\JobTitle;
use App\Models\School;
use App\Models\Skill;
use App\Models\StudentOrg;

use Exception;

class CommonController extends Controller
{
    public function getConfigurations(Request $request)
    {
        try {
            $associations = Association::all("id", 'name')->toJson();
            $countries = Country::all('id', 'name')->toJson();
            $hobbies = Hobby::all('id', 'name')->toJson();
            $industries = Industry::all('id', 'name')->toJson();
            $schools = DB::table('schools')->select('id', 'name')->offset(0)->limit(100)->orderBy('id')->get()->toJson();
            $military_branches = MilitaryBranch::all('id', 'name')->toJson();

            return response()->json([
                'message' => 'Configurations retrieved succesfully!',
                'data' => [
                    'associations' => json_decode($associations),
                    'countries' => json_decode($countries),
                    'hobbies' => json_decode($hobbies),
                    'industries' => json_decode($industries),
                    'schools' => json_decode($schools),
                    'military_branches' => json_decode($military_branches),
                    "showLoginForm" => false
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong, Please try again.', 'error' => $e]);
        }
    }

    public function getSchools(Request $request)
    {
        $result = School::all()->map->only(['id', 'name', 'high_school', 'color_1', 'color_2', 'logo_1', 'logo_2', 'slogan', 'acronym', 'banner']);

        return response()->json($result);
    }

    public function getMilitaryCodes(Request $request)
    {
        try {
            $page = $request->page;
            $search_keyword = $request->search;
            $military_branch = $request->military_branch;
            $military_codes_query = DB::table('military_codes')->select('id', 'name', 'military_branch_id', 'description');
            if ($search_keyword) {
                $military_codes_query = $military_codes_query->where('name', 'LIKE', "%{$search_keyword}%");
            }
            if ($military_branch) {
                $military_codes_query = $military_codes_query->where('military_branch_id', '=', $military_branch);
            }
            $military_codes = $military_codes_query->offset(($page - 1) * 100)->limit(100)->orderBy('id')->get()->toJson();

            return response()->json([
                'message' => 'Military Codes retrieved succesfully!',
                'data' => json_decode($military_codes)
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong, Please try again.']);
        }
    }

    public function saveFeedback(Request $request)
    {
        try {
            $feedback = Feedback::create([
                'positive_comment' => $request->positive_comment,
                'improvement_comment' => $request->improvement_comment,
                'general_comment' => $request->general_comment,
            ]);
            $feedback->save();
            // $title = 'New feedback from Linked Leaders';
            // $feedback_details = [ 
            //     'positive_comment' => $request->positive_comment,
            //     'improvement_comment' => $request->improvement_comment,
            //     'general_comment' => $request->general_comment
            // ];
            // $sendmail = Mail::to("yourmail@gmail.com")->send(new SendMail($title, $feedback_details));

            return response()->json([
                'message' => 'Feedback submitted succesfully!',
                'status' => "success"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong, Please try again.',
                'status' => "error"
            ]);
        }
    }

    public function getCities(Request $request)
    {
        $cities = City::all()->map->only(['id', 'name']);

        return response()->json($cities);
    }

    public function getCountries(Request $request)
    {
        $countries = Country::all()->map->only(['id', 'name', 'icon', 'icon_invert']);

        return response()->json($countries);
    }

    public function getDegrees(Request $request)
    {
        $degrees = Degree::all()->map->only(['id', 'name']);

        return response()->json($degrees);
    }

    public function getGradLevels(Request $request)
    {
        $result = GradLevel::all()->map->only(['id', 'name']);

        return response()->json($result);
    }

    public function getIbcCompanies(Request $request)
    {
        $result = IbcCompany::all()->map->only(['id', 'name', 'description']);

        return response()->json($result);
    }

    public function getStudentOrgs(Request $request)
    {
        $result = StudentOrg::all()->map->only(['id', 'name']);

        return response()->json($result);
    }

    public function getAssociations(Request $request)
    {
        $result = Association::all()->map->only(['id', 'name']);

        return response()->json($result);
    }

    public function getIndustries(Request $request)
    {
        $result = Industry::all()->map->only(['id', 'name', 'icon', 'icon_invert']);

        return response()->json($result);
    }

    public function getHobbies(Request $request)
    {
        $result = Hobby::all()->map->only(['id', 'name', 'icon', 'icon_invert']);

        return response()->json($result);
    }

    public function getJobTitles(Request $request)
    {
        $result = JobTitle::all()->map->only(['id', 'name']);

        return response()->json($result);
    }

    public function getMilitaryBranches(Request $request)
    {
        $result = MilitaryBranch::all()->map->only(['id', 'name']);

        return response()->json($result);
    }

    public function getSkills(Request $request)
    {
        $result = Skill::all()->map->only(['id', 'name']);

        return response()->json($result);
    }
}

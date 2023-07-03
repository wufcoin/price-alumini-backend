<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Models\AssociationUser;
use App\Models\MilitaryBranchUser;
use App\Models\MilitaryCodeUser;
use App\Models\TraitsSharedCount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Country;
use App\Models\SchoolUser;
use App\Models\HobbyUser;
use App\Models\IndustryUser;
use App\Models\MilitaryBranch;
use App\Models\MilitaryCode;

use Exception;

class UserController extends Controller
{
    public function users()
    {
        try {
            $users = User::query()->orderBy('connections_size', 'desc')->with(['associations', 'schools', 'hobbies', 'industries', 'organizations'])->get()->toArray();

            foreach ($users as $key => $user) {
                if ($user['country_id'] != null) {
                    $users[$key]['country'] = Country::find($user['country_id'])->toArray();
                }
                if ($user['military_code_id'] != null) {
                    $users[$key]['military_code'] = MilitaryCode::find($user['military_code_id'])->toArray();
                    $users[$key]['military_branch'] = MilitaryBranch::find($users[$key]['military_code']['military_branch_id'])->toArray();
                }
            }
            return response()->json([
                'message' => 'Users retrieved succesfully!',
                'data' => ["users" => $users, 'count' => count($users)]
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong, Please try again.']);
        }
    }

    public function getUsersByTraitsCounts(Request $request)
    {
        $userID = $request->id;

        try {
            $usersSortByTraitsCount = TraitsSharedCount::where('user_id', $userID)->select("user_id_other_user", "total_traits_shared_count")->orderBy('total_traits_shared_count', 'desc')->orderBy('connection_size', 'desc')->get();
            $users = new Collection();
            $userTraitCountList = array();
            foreach ($usersSortByTraitsCount as &$userItem) {
                $otherUser = User::query()->where('id', $userItem["user_id_other_user"])->with(['associations', 'schools', 'hobbies', 'industries', 'organizations'])->get();
                $users = $users->merge($otherUser);
                $userTraitCountList[] = $userItem["total_traits_shared_count"];
            }
            foreach ($users as $key => $user) {
                if ($user['country_id'] != null) {
                    $users[$key]['country'] = Country::find($user['country_id'])->toArray();
                }
                if ($user['military_code_id'] != null) {
                    $users[$key]['military_code'] = MilitaryCode::find($user['military_code_id'])->toArray();
                    $users[$key]['military_branch'] = MilitaryBranch::find($users[$key]['military_code']['military_branch_id'])->toArray();
                }
            }
            return response()->json([
                'message' => 'Users retrieved successfully!',
                'data' => ["users" => $users, "traits_count" => $userTraitCountList, 'count' => count($users)]
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong, Please try again.']);
        }
    }

    public function userDetails(Request $request)
    {
        try {
            $user = User::find($request->id);
            $user['country'] = Country::find($user->country_id);
            $user['associations'] = $user->associations;
            $user['schools'] = $user->schools;
            $user['hobbies'] = $user->hobbies;
            $user['industries'] = $user->industries;
            $user['organizations'] = $user->organizations;
            if (isset($user->military_code_id)) {
                $user['military_code'] = MilitaryCode::find($user->military_code_id);
                $user['military_branch'] = MilitaryBranch::find($user['military_code']['military_branch_id']);
            }
            if ($user) {
                return response()->json([
                    'message' => 'User details retrieved succesfully!',
                    'data' => $user
                ]);
            } else {
                return response()->json(['message' => 'User details not found.']);
            }
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong, Please try again.']);
        }
    }

    public function update(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'country_id' => 'required',
                    'associations_ids' => 'required',
                    'schools_ids' => 'required',
                    'hobbies_ids' => 'required',
                    'industries_ids' => 'required'
                ],
                [
                    'country_id.required' => 'Please select country.',
                    'associations_ids.required' => 'Please select at least one association.',
                    'schools_ids.required' => 'Please select at least one school.',
                    'hobbies_ids.required' => 'Please select hobbies.',
                    'industries_ids.required' => 'Please select industries.'
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ]);
            }
            $user = User::find($request->id);
            if ($user) {
                $user->country_id = $request->country_id;
                $user->military_code_id = isset($request->military_code_id) ? $request->military_code_id : null;
                $MilitaryCode = null;

                if (!isset($request->military_code_id)) {
                    MilitaryBranchUser::where('user_id', $user->id)->delete();
                    MilitaryCodeUser::where('user_id', $user->id)->delete();
                } else {
                    $MilitaryCode = MilitaryCode::where('id', $request->military_code_id)->first();
                    if (MilitaryCodeUser::where('user_id', $user->id)->first() == null) {
                        MilitaryCodeUser::updateOrCreate([
                            "user_id" => $user->id,
                            "military_code_id" => $request->military_code_id,
                        ]);
                        MilitaryBranchUser::updateOrCreate([
                            "user_id" => $user->id,
                            "military_branch_id" => $MilitaryCode->military_branch_id,
                        ]);
                    } else {
                        MilitaryCodeUser::updateOrCreate(
                            ["user_id" => $user->id],
                            ["military_code_id" => $request->military_code_id]
                        );
                        MilitaryBranchUser::updateOrCreate(
                            ["user_id" => $user->id],
                            ["military_branch_id" => $MilitaryCode->military_branch_id]
                        );
                    }
                }
                $user->youtube_video_url = $request->youtube_video_url;
                $user->how_help_others = $request->how_help_others;
                $user->how_help_looking_for = $request->how_help_looking_for;
                $user->associations()->sync(gettype($request->associations_ids) == "string" ? json_decode($request->associations_ids) : $request->associations_ids);
                $user->schools()->sync(gettype($request->schools_ids) == "string" ? json_decode($request->schools_ids) : $request->schools_ids);
                $user->hobbies()->sync(gettype($request->hobbies_ids) == "string" ? json_decode($request->hobbies_ids) : $request->hobbies_ids);
                $user->industries()->sync(gettype($request->industries_ids) == "string" ? json_decode($request->industries_ids) : $request->industries_ids);
                $user->save();

                $user['country'] = Country::find($user->country_id);
                $user['associations'] = $user->associations;
                $user['schools'] = $user->schools;
                $user['hobbies'] = $user->hobbies;
                $user['industries'] = $user->industries;
                $user['organizations'] = $user->organizations;
                if (isset($user->military_code_id)) {
                    $user['military_code'] = MilitaryCode::find($user->military_code_id);
                    $user['military_branch'] = MilitaryBranch::find($user['military_code']['military_branch_id']);
                }

                $UserList = User::where("id", "!=", $user->id)->get()->toArray();
                $userAssociationSQLList = AssociationUser::select('association_id')->where("user_id", $user->id)->get()->toArray();
                $UserSchoolSQLList = SchoolUser::select('school_id')->where("user_id", $user->id)->get()->toArray();
                $UserHobbySQLList = HobbyUser::select('hobby_id')->where("user_id", $user->id)->get()->toArray();
                $UserIndustrySQLList = IndustryUser::select('industry_id')->where("user_id", $user->id)->get()->toArray();
                $UserMilitaryCodeID = $MilitaryCode != null ? $MilitaryCode->id : null;
                $UserMilitaryBranchID = $MilitaryCode != null ? $MilitaryCode->military_branch_id : null;
                $userAssociationList = array();
                $userSchoolList = array();
                $userHobbyList = array();
                $userIndustryList = array();

                foreach ($userAssociationSQLList as &$associationItem) {
                    $userAssociationList[] = $associationItem['association_id'];
                }
                foreach ($UserSchoolSQLList as &$userItem) {
                    $userSchoolList[] = $userItem['school_id'];
                }
                foreach ($UserHobbySQLList as &$hobbyItem) {
                    $userHobbyList[] = $hobbyItem['hobby_id'];
                }
                foreach ($UserIndustrySQLList as &$industryItem) {
                    $userIndustryList[] = $industryItem['industry_id'];
                }
                for ($i = 0; $i < count($UserList); $i++) {
                    $otherUser = $UserList[$i];
                    $otherUserAssociationList = array();
                    $otherUserSchoolList = array();
                    $otherUserHobbyList = array();
                    $otherUserIndustryList = array();
                    $countryTraitCount = ($otherUser["country_id"] == $user->country_id) ? 1 : 0;
                    $otherUserAssociationSQLList = AssociationUser::select('association_id')->where("user_id", $otherUser["id"])->get()->toArray();
                    $OtherUserSchoolSQLList = SchoolUser::select('school_id')->where("user_id", $otherUser["id"])->get()->toArray();
                    $OtherUserHobbySQLList = HobbyUser::select('hobby_id')->where("user_id", $otherUser["id"])->get()->toArray();
                    $OtherUserIndustrySQLList = IndustryUser::select('industry_id')->where("user_id", $otherUser["id"])->get()->toArray();

                    foreach ($otherUserAssociationSQLList as &$otherAssociationItem) {
                        $otherUserAssociationList[] = $otherAssociationItem['association_id'];
                    }
                    foreach ($OtherUserSchoolSQLList as &$otherUserSchoolItem) {
                        $otherUserSchoolList[] = $otherUserSchoolItem['school_id'];
                    }
                    foreach ($OtherUserHobbySQLList as &$otherHobbyItem) {
                        $otherUserHobbyList[] = $otherHobbyItem['hobby_id'];
                    }
                    foreach ($OtherUserIndustrySQLList as &$otherIndustryItem) {
                        $otherUserIndustryList[] = $otherIndustryItem['industry_id'];
                    }
                    $OtherUserMilitaryCodeID = null;
                    $OtherUserMilitaryBranchID = null;
                    if (isset($otherUser["military_code_id"])) {
                        $OtherUSerMilitaryCode = MilitaryCode::where('id', $otherUser["military_code_id"])->first();
                        $OtherUserMilitaryCodeID = $OtherUSerMilitaryCode->id;
                        $OtherUserMilitaryBranchID =  $OtherUSerMilitaryCode->military_branch_id;
                    }
                    $associationTraitCount = count(array_intersect($userAssociationList, $otherUserAssociationList));
                    $schoolTraitCount = count(array_intersect($userSchoolList, $otherUserSchoolList));
                    $militaryCodeTraitCount = ($OtherUserMilitaryCodeID == $UserMilitaryCodeID) ? 1 : 0;
                    $militaryBranchesTraitCount = ($OtherUserMilitaryBranchID == $UserMilitaryBranchID) ? 1 : 0;
                    $hobbyTraitCount = count(array_intersect($userHobbyList, $otherUserHobbyList));
                    $industryTraitCount = count(array_intersect($userIndustryList, $otherUserIndustryList));
                    $total_shared_count = $countryTraitCount + $associationTraitCount + $schoolTraitCount
                        + $militaryCodeTraitCount +  $militaryBranchesTraitCount + $hobbyTraitCount + $industryTraitCount;
                    $traitsCountExisting = TraitsSharedCount::where('user_id', $user["id"])->where('user_id_other_user', $otherUser["id"])->get();

                    if ($traitsCountExisting) {
                        TraitsSharedCount::updateOrCreate([
                            "user_id" => $user["id"],
                            "user_id_other_user" => $otherUser["id"],
                            "connection_size" => $otherUser["connections_size"],
                            "countries_count" => $countryTraitCount,
                            "association_count" => $associationTraitCount,
                            "schools_count" => $schoolTraitCount,
                            "military_branches_count" => $militaryBranchesTraitCount,
                            "military_codes_count" => $militaryCodeTraitCount,
                            "associations_count" => 0,
                            "hobbies_count" => $hobbyTraitCount,
                            "industries_count" => $industryTraitCount,
                            "total_traits_shared_count" => $total_shared_count,
                        ]);
                    } else {
                        TraitsSharedCount::updateOrCreate(
                            [
                                "user_id" => $user["id"],
                                "user_id_other_user" => $otherUser["id"]
                            ],
                            [
                                "connection_size" => $otherUser["connections_size"],
                                "countries_count" => $countryTraitCount,
                                "association_count" => $associationTraitCount,
                                "schools_count" => $schoolTraitCount,
                                "military_branches_count" => $militaryBranchesTraitCount,
                                "military_codes_count" => $militaryCodeTraitCount,
                                "associations_count" => 0,
                                "hobbies_count" => $hobbyTraitCount,
                                "industries_count" => $industryTraitCount,
                                "total_traits_shared_count" => $total_shared_count,
                            ]
                        );
                    }
                }
                return response()->json([
                    'message' => 'User details updated succesfully!',
                    'data' => $user
                ]);
            } else {
                return response()->json(['message' => 'User not found.']);
            }
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function getMatch(Request $request)
    {
        try {
            $match = [];
            $user = User::query()->where('id', '=', $request->id)->with(['associations', 'schools', 'hobbies', 'industries', 'organizations'])->get()->toArray();
            $user_details = $user[0];
            $graduation_match_userInfos = [];
            $match[] = ["category_name" => "graduation", "value" =>  $user_details['graduation'], 'users' => $graduation_match_userInfos];
            if ($user_details['country_id']) {
                $user_details['country'] = Country::find($user_details['country_id'])->toArray();
                $users_with_specific_country = User::query()->where('country_id', '=', $user_details['country']['id'])->with(['associations', 'schools', 'hobbies', 'industries', 'organizations'])->orderBy('connections_size', 'desc')->get()->toArray();
                foreach ($users_with_specific_country as $key => $user) {
                    $users_with_specific_country[$key]['country'] = Country::find($user['country_id'])->toArray();
                    if ($users_with_specific_country[$key]['military_code_id']) {
                        $users_with_specific_country[$key]['military_code'] = MilitaryCode::find($users_with_specific_country[$key]['military_code_id'])->toArray();
                        $users_with_specific_country[$key]['military_branch'] = MilitaryBranch::find($users_with_specific_country[$key]['military_code']['military_branch_id']);
                    }
                }
                $match[] = ["category_name" => $user_details['country']['name'], "users" => $users_with_specific_country, "count" => count($users_with_specific_country)];
            }
            if ($user_details['schools'] && !empty($user_details['schools'])) {
                foreach ($user_details['schools'] as $key => $school) {
                    $users_ids_with_specific_school = SchoolUser::query()->select('user_id')
                        ->where("school_id", '=', $school['id'])
                        ->get()->toArray();
                    $users_ids_array = array_map(function (array $value): int {
                        return $value['user_id'];
                    }, $users_ids_with_specific_school);
                    $users_with_specific_school = User::query()->whereIn('id', $users_ids_array)->with(['associations', 'schools', 'hobbies', 'industries', 'organizations'])->orderBy('connections_size', 'desc')->get()->toArray();
                    foreach ($users_with_specific_school as $key => $user) {
                        $users_with_specific_school[$key]['country'] = Country::find($user['country_id'])->toArray();
                        if ($users_with_specific_school[$key]['military_code_id']) {
                            $users_with_specific_school[$key]['military_code'] = MilitaryCode::find($users_with_specific_school[$key]['military_code_id'])->toArray();
                            $users_with_specific_school[$key]['military_branch'] = MilitaryBranch::find($users_with_specific_school[$key]['military_code']['military_branch_id']);
                        }
                    }
                    $match[] = ["category_name" => $school['name'], "users" => $users_with_specific_school, "count" => count($users_with_specific_school)];
                }
            }
            if ($user_details['hobbies'] && !empty($user_details['hobbies'])) {
                foreach ($user_details['hobbies'] as $key => $hobby) {
                    $users_ids_with_specific_hobby = HobbyUser::query()->select('user_id')
                        ->where("hobby_id", '=', $hobby['id'])
                        ->get()->toArray();

                    $users_ids_array = array_map(function (array $value): int {
                        return $value['user_id'];
                    }, $users_ids_with_specific_hobby);

                    $users_with_specific_hobby = User::query()->whereIn('id', $users_ids_array)->with(['associations', 'schools', 'hobbies', 'industries', 'organizations'])->orderBy('connections_size', 'desc')->get()->toArray();

                    foreach ($users_with_specific_hobby as $key => $user) {
                        $users_with_specific_hobby[$key]['country'] = Country::find($user['country_id'])->toArray();
                        if ($users_with_specific_hobby[$key]['military_code_id']) {
                            $users_with_specific_hobby[$key]['military_code'] = MilitaryCode::find($users_with_specific_hobby[$key]['military_code_id'])->toArray();
                            $users_with_specific_hobby[$key]['military_branch'] = MilitaryBranch::find($users_with_specific_hobby[$key]['military_code']['military_branch_id']);
                        }
                    }
                    $match[] = ["category_name" => $hobby['name'], "users" => $users_with_specific_hobby, "count" => count($users_with_specific_hobby)];
                }
            }
            if ($user_details['industries'] && !empty($user_details['industries'])) {
                foreach ($user_details['industries'] as $key => $industry) {
                    $users_ids_with_specific_industry = IndustryUser::query()->select('user_id')
                        ->where("industry_id", '=', $industry['id'])
                        ->get()->toArray();

                    $users_ids_array = array_map(function (array $value): int {
                        return $value['user_id'];
                    }, $users_ids_with_specific_industry);

                    $users_with_specific_industry = User::query()->whereIn('id', $users_ids_array)->with(['associations', 'schools', 'hobbies', 'industries', 'organizations'])->orderBy('connections_size', 'desc')->get()->toArray();

                    foreach ($users_with_specific_industry as $key => $user) {
                        $users_with_specific_industry[$key]['country'] = Country::find($user['country_id'])->toArray();
                        if ($users_with_specific_industry[$key]['military_code_id']) {
                            $users_with_specific_industry[$key]['military_code'] = MilitaryCode::find($users_with_specific_industry[$key]['military_code_id'])->toArray();
                            $users_with_specific_industry[$key]['military_branch'] = MilitaryBranch::find($users_with_specific_industry[$key]['military_code']['military_branch_id']);
                        }
                    }
                    $match[] = ["category_name" => $industry['name'], "users" => $users_with_specific_industry, "count" => count($users_with_specific_industry)];
                }
            }
            if ($user) {
                return response()->json([
                    'message' => 'User details retrieved succesfully!',
                    'data' => $match
                ]);
            } else {
                return response()->json(['message' => 'User details not found.']);
            }
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong, Please try again.']);
        }
    }

    public function getRanks(Request $request)
    {
        try {
            $match = [];
            $user = User::query()->where('id', '=', $request->id)->with(['associations', 'schools', 'hobbies', 'industries', 'organizations'])->get()->toArray();
            $user_details = $user[0];

            if ($user_details['country_id']) {
                $user_details['country'] = Country::find($user_details['country_id'])->toArray();

                $users_with_specific_country = User::query()->where('country_id', '=', $user_details['country']['id'])->orderBy('connections_size', 'desc')->get()->toArray();

                foreach ($users_with_specific_country as $key => $user) {
                    if ($user['id'] == $request->id) {
                        $users_with_specific_country[$key]['country'] = Country::find($user['country_id'])->toArray();
                        $users_with_specific_country[$key]['country']['rank'] = ($key + 1);
                        $match["country"] = $users_with_specific_country[$key]['country'];
                    }
                }
            }
            if ($user_details['schools'] && !empty($user_details['schools'])) {
                foreach ($user_details['schools'] as $key => $school) {
                    $users_ids_with_specific_school = SchoolUser::query()->select('user_id')
                        ->where("school_id", '=', $school['id'])
                        ->get()->toArray();
                    $users_ids_array = array_map(function (array $value): int {
                        return $value['user_id'];
                    }, $users_ids_with_specific_school);

                    $users_with_specific_school = User::query()->whereIn('id', $users_ids_array)->orderBy('connections_size', 'desc')->get()->toArray();

                    foreach ($users_with_specific_school as $key => $user) {
                        if ($user['id'] == $request->id) {
                            $school['rank'] = ($key + 1);
                            $match['schools'][] = $school;
                        }
                    }
                }
            }
            if ($user_details['hobbies'] && !empty($user_details['hobbies'])) {
                foreach ($user_details['hobbies'] as $key => $hobby) {
                    $users_ids_with_specific_hobby = HobbyUser::query()->select('user_id')
                        ->where("hobby_id", '=', $hobby['id'])
                        ->get()->toArray();

                    $users_ids_array = array_map(function (array $value): int {
                        return $value['user_id'];
                    }, $users_ids_with_specific_hobby);

                    $users_with_specific_hobby = User::query()->whereIn('id', $users_ids_array)->orderBy('connections_size', 'desc')->get()->toArray();

                    foreach ($users_with_specific_hobby as $key => $user) {
                        if ($user['id'] == $request->id) {
                            $hobby['rank'] = ($key + 1);
                            $match['hobbies'][] = $hobby;
                        }
                    }
                }
            }
            if ($user_details['industries'] && !empty($user_details['industries'])) {
                foreach ($user_details['industries'] as $key => $industry) {
                    $users_ids_with_specific_industry = IndustryUser::query()->select('user_id')
                        ->where("industry_id", '=', $industry['id'])
                        ->get()->toArray();

                    $users_ids_array = array_map(function (array $value): int {
                        return $value['user_id'];
                    }, $users_ids_with_specific_industry);

                    $users_with_specific_industry = User::query()->whereIn('id', $users_ids_array)->with(['associations', 'schools', 'hobbies', 'industries', 'organizations'])->orderBy('connections_size', 'desc')->get()->toArray();

                    foreach ($users_with_specific_industry as $key => $user) {
                        if ($user['id'] == $request->id) {
                            $industry['rank'] = ($key + 1);
                            $match['industries'][] = $industry;
                        }
                    }
                }
            }
            if ($user) {
                return response()->json([
                    'message' => 'User ranks retrieved succesfully!',
                    'data' => $match
                ]);
            } else {
                return response()->json(['message' => 'User details not found.']);
            }
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong, Please try again.']);
        }
    }

    public function update_devicetoken(Request $request)
    {
        $my_user = User::where('id', $request->user_id)->first();

        if ($my_user) {
            $my_user->device_token = $request->device_token;
            $success =  $my_user->save();
            return response()->json(['status' => '1', 'data' => $my_user]);
        } else {
            return response()->json(['status' => '0', 'message' => 'There is no user.']);
        }
    }

    public function getUsers(Request $request)
    {
        $users = User::where('id', '<>', auth()->id())->get();

        return response()->json(ProfileResource::collection($users));
    }
}

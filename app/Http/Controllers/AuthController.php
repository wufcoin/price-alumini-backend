<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Jobs\NewUserRegistered;
use App\Models\AssociationUser;
use App\Models\HobbyUser;
use App\Models\IndustryUser;
use App\Models\MilitaryBranchUser;
use App\Models\MilitaryCodeUser;
use App\Models\SchoolUser;
use App\Models\TraitsSharedCount;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Organization;
use App\Models\MilitaryBranch;
use App\Models\MilitaryCode;
use App\Traits\UserTrait;
use Exception;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    private function responseWithToken(User $user)
    {
        $token = $user->createToken(config('app.name'));

        return [
            'access_token' => $token->plainTextToken,
            'data' => new ProfileResource($user)
        ];
    }

    public function signin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }
        $user = Auth::attempt(["email" => $request->email, "password" => $request->password]);
        if ($user) {
            $user = User::where('email', $request->email)->first();

            return response()->json($this->responseWithToken($user));
        } else {
            return response()->json(['message' => 'Email or password is incorrect!'], 400);
        }
    }

    public function linkedinsignin(Request $request)
    {
        try {
            $existUser = User::where('email', $request->email)->first();

            $validator = Validator::make(
                $request->all(),
                [
                    'first_name' => 'required|string',
                    'last_name' => 'required|string',
                    'email' => $existUser ? 'required|string' : 'required|string|unique:users',
                    'country_id' => 'required',
                    'schools_ids' => 'required',
                    'hobbies_ids' => 'required',
                    'industries_ids' => 'required',
                ],
                [
                    'country_id.required' => 'Please select country.',
                    'schools_ids.required' => 'Please select atleast one schools.',
                    'hobbies_ids.required' => 'Please select hobbies.',
                    'industries_ids.required' => 'Please select industries.'
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ]);
            }
            if (!$existUser) {
                $existUser = User::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'password' => Hash::make("LL@1234"),
                    'linkedin_id' => $request->linkedin_id,
                    'connections_size' => $request->connections_size,
                    'headline' => $request->headline,
                    'profile_url' => $request->profile_url,
                    'profile_pic' => $request->profile_pic,
                    'youtube_video_url' => $request->youtube_video_url,
                    'how_help_others' => $request->how_help_others,
                    'how_help_looking_for' => $request->how_help_looking_for
                ]);
                $user = Auth::loginUsingId($existUser->id);
                $user->linkedin_auth_token = $request->linkedin_auth_token;
                $user->linkedin_auth_token_expire_at = $request->linkedin_auth_token_expire_at;
                $user->linkedin_auth_refresh_token = $request->linkedin_auth_refresh_token;
                $user->linkedin_auth_refresh_token_expire_at = $request->linkedin_auth_refresh_token_expire_at;
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

                $organization_ids = [];
                if ($request->organizations && !empty($request->organizations)) {
                    $organizations_array = gettype($request->organizations) == "string" ? json_decode($request->organizations, true) : $request->organizations;
                    foreach ($organizations_array as $organization) {

                        $organization = Organization::updateOrCreate([
                            'name' => $organization['organization~']['localizedName'],
                            'role' => $organization['role'],
                            'role_assignee_urn' => $organization['roleAssignee'],
                            'state' => $organization['state'],
                            'organization_urn' => $organization['organization']
                        ], [
                            'name' => $organization['organization~']['localizedName'],
                            'role' => $organization['role'],
                            'role_assignee_urn' => $organization['roleAssignee'],
                            'state' => $organization['state'],
                            'organization_urn' => $organization['organization']
                        ]);
                        array_push($organization_ids, $organization->id);
                    }
                    $user->organizations()->sync($organization_ids);
                } else {
                    $user->organizations()->detach();
                }

                $user->associations()->sync(gettype($request->associations_ids) == "string" ? json_decode($request->associations_ids) : $request->associations_ids);
                $user->schools()->sync(gettype($request->schools_ids) == "string" ? json_decode($request->schools_ids) : $request->schools_ids);
                $user->hobbies()->sync(gettype($request->hobbies_ids) == "string" ? json_decode($request->hobbies_ids) : $request->hobbies_ids);
                $user->industries()->sync(gettype($request->industries_ids) == "string" ? json_decode($request->industries_ids) : $request->industries_ids);
                $user->save();

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
                    $total_shared_count = $associationTraitCount + $schoolTraitCount + $militaryCodeTraitCount +  $militaryBranchesTraitCount + $hobbyTraitCount + $industryTraitCount;

                    TraitsSharedCount::updateOrCreate([
                        "user_id" => $user["id"],
                        "user_id_other_user" => $otherUser["id"],
                        "connection_size" => $otherUser["connections_size"],
                        "association_count" => $associationTraitCount,
                        "schools_count" => $schoolTraitCount,
                        "military_branches_count" => $militaryBranchesTraitCount,
                        "military_codes_count" => $militaryCodeTraitCount,
                        "associations_count" => 0,
                        "hobbies_count" => $hobbyTraitCount,
                        "industries_count" => $industryTraitCount,
                        "total_traits_shared_count" => $total_shared_count,
                    ]);
                }
            } else {
                $user = Auth::loginUsingId($existUser->id);

                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->linkedin_id = $request->linkedin_id;
                $user->connections_size = $request->connections_size;
                $user->headline = $request->headline;
                $user->profile_url = $request->profile_url;
                $user->profile_pic = $request->profile_pic;

                $user->youtube_video_url = $request->youtube_video_url;
                $user->how_help_others = $request->how_help_others;
                $user->how_help_looking_for = $request->how_help_looking_for;

                $user->linkedin_auth_token = $request->linkedin_auth_token;
                $user->linkedin_auth_token_expire_at = $request->linkedin_auth_token_expire_at;

                $user->linkedin_auth_refresh_token = $request->linkedin_auth_refresh_token;
                $user->linkedin_auth_refresh_token_expire_at = $request->linkedin_auth_refresh_token_expire_at;

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
                $organization_ids = [];
                if ($request->organizations && !empty($request->organizations)) {
                    $organizations_array = gettype($request->organizations) == "string" ? json_decode($request->organizations, true) : $request->organizations;
                    foreach ($organizations_array as $organization) {

                        $organization = Organization::updateOrCreate([
                            'name' => $organization['organization~']['localizedName'],
                            'role' => $organization['role'],
                            'role_assignee_urn' => $organization['roleAssignee'],
                            'state' => $organization['state'],
                            'organization_urn' => $organization['organization']
                        ], [
                            'name' => $organization['organization~']['localizedName'],
                            'role' => $organization['role'],
                            'role_assignee_urn' => $organization['roleAssignee'],
                            'state' => $organization['state'],
                            'organization_urn' => $organization['organization']
                        ]);
                        array_push($organization_ids, $organization->id);
                    }
                    $user->organizations()->sync($organization_ids);
                }
                $user->associations()->sync(gettype($request->associations_ids) == "string" ? json_decode($request->associations_ids) : $request->associations_ids);
                $user->schools()->sync(gettype($request->schools_ids) == "string" ? json_decode($request->schools_ids) : $request->schools_ids);
                $user->hobbies()->sync(gettype($request->hobbies_ids) == "string" ? json_decode($request->hobbies_ids) : $request->hobbies_ids);
                $user->industries()->sync(gettype($request->industries_ids) == "string" ? json_decode($request->industries_ids) : $request->industries_ids);
                $user->save();

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
            }
            $tokenResult = $existUser->createToken('LinkedinLogin');

            $user = User::find(Auth::user()->id);
            if ($user) {
                $user['associations'] = $user->associations;
                $user['schools'] = $user->schools;
                $user['hobbies'] = $user->hobbies;
                $user['industries'] = $user->industries;
                $user['organizations'] = $user->organizations;
                if (isset($user->military_code_id)) {
                    $user['military_code'] = MilitaryCode::find($user->military_code_id);
                    $user['military_branch'] = MilitaryBranch::find($user['military_code']['military_branch_id']);
                }
            } else {
                return response()->json(['message' => 'User not found.']);
            }

            return response()->json([
                'message' => 'You have been loggedin successfully!',
                'data' => $user,
                'access_token' => $tokenResult->plainTextToken
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function getuserbyemail(Request $request)
    {
        try {
            $existUser = User::where('email', $request->email)->first();
            if ($existUser != null) {
                $tokenResult = $existUser->createToken('LinkedinLogin');
                $existUser['associations'] = $existUser->associations;
                $existUser['schools'] = $existUser->schools;
                $existUser['hobbies'] = $existUser->hobbies;
                $existUser['industries'] = $existUser->industries;
                $existUser['organizations'] = $existUser->organizations;
                if (isset($existUser->military_code_id)) {
                    $existUser['military_code'] = MilitaryCode::find($existUser->military_code_id);
                    $existUser['military_branch'] = MilitaryBranch::find($existUser['military_code']['military_branch_id']);
                }
                return response()->json([
                    'message' => 'You have been loggedin successfully!',
                    'data' => $existUser,
                    'access_token' => $tokenResult->plainTextToken
                ]);
            } else {
                return response()->json([
                    'message' => 'You have been loggedin successfully!',
                    'data' => $existUser,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'data' => null,
            ]);
        }
    }

    public function logout()
    {
        try {
            if (Auth::check()) {
                Auth::user()->tokens->each(function ($token, $key) {
                    $token->delete();
                });
                return response()->json(['message' => 'you have been successfully logged out!', 'status' => "success"], 200);
            } else {
                return response()->json(['message' => 'Something went wrong, Please try again.', 'status' => "error"], 500);
            }
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong, Please try again.', 'status' => "error"], 500);
        }
    }

    public function register(Request $request)
    {
        $attr = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'grad_level_id' => 'nullable',
            'degree_id' => 'nullable',
            'jc_penny' => 'required',
            'ibc_company_id' => 'nullable',
            'student_org_id' => 'nullable',
            'association_id' => 'nullable',
            'industry_id' => 'nullable',
            'hobby_id' => 'nullable',
            'city_id' => 'nullable',
            'country_id' => 'nullable',
            'job_title_id' => 'nullable',
            'military_branch_id' => 'nullable',
            'school_id' => 'nullable',
            'profile_pic' => 'required|string',
            'profile_url' => 'nullable|string',
            'grad_year' => 'nullable|string',
            'headline' => 'nullable|string',
            'youtube_video_url' => 'nullable',
            'skills_id' => 'nullable',
            'type' => 'in:OWN,JOIN'
        ]);
        $attr['connections_size'] = 6;
        if (!empty($attr['profile_pic'])) {
            $extension = explode('image/', $attr['profile_pic'])[1];
            $extension = explode(';', $extension)[0];
            $image = explode(',', $attr['profile_pic'])[1];
            $image = base64_decode($image);

            $path = sprintf("avatars/%s.%s", time(), $extension);
            Storage::disk('public')->put($path, $image);
            $attr['profile_pic'] = $path;
        } else {
            $attr['profile_pic'] = null;
        }
        if (!empty($attr['profile_url'])) {
            $extension = explode('image/', $attr['profile_url'])[1];
            $extension = explode(';', $extension)[0];
            $image = explode(',', $attr['profile_url'])[1];
            $image = base64_decode($image);

            $path = sprintf('profile/%s.%s', time(), $extension);
            Storage::disk('public')->put($path, $image);
            $attr['profile_url'] = $path;
        } else {
            $attr['profile_url'] = null;
        }
        $attr['password'] = Hash::make($attr['password']);

        $user = User::create($attr);
        if (!$user) {
            return response()->json(['message' => 'Registration failed'], 500);
        }
        if (!empty($attr['grad_level_id'])) {
            $user->saveGradLevels($attr['grad_level_id']);
        }
        if (!empty($attr['degree_id'])) {
            $user->saveDegrees($attr['degree_id']);
        }
        if (!empty($attr['student_org_id'])) {
            $user->saveStudentOrgs($attr['student_org_id']);
        }
        if (!empty($attr['association_id'])) {
            $user->saveAssociations($attr['association_id']);
        }
        if (!empty($attr['industry_id'])) {
            $user->saveIndustries($attr['industry_id']);
        }
        if (!empty($attr['hobby_id'])) {
            $user->saveHobbies($attr['hobby_id']);
        }
        if (!empty($attr['city_id'])) {
            $user->saveCities($attr['city_id']);
        }
        if (!empty($attr['job_title_id'])) {
            $user->saveJobTitles($attr['job_title_id']);
        }
        if (!empty($attr['military_branch_id'])) {
            $user->saveMilitaryBranch($attr['military_branch_id']);
        }
        if (!empty($attr['school_id'])) {
            $user->saveSchools($attr['school_id']);
        }
        if (!empty($attr['country_id'])) {
            $user->saveCountries($attr['country_id']);
        }
        if (!empty($attr['skills_id'])) {
            $user->saveSkills($attr['skills_id']);
        }

        dispatch(new NewUserRegistered($user))->delay(now()->addHours(13));

        return response()->json($this->responseWithToken($user));
    }

    public function updateProfile(Request $request)
    {
        $attr = $request->validate([
            'first_name' => 'string',
            'last_name' => 'string',
            'grad_level_id' => 'nullable',
            'degree_id' => 'nullable',
            'jc_penny' => 'boolean',
            'ibc_company_id' => 'nullable',
            'student_org_id' => 'nullable',
            'association_id' => 'nullable',
            'industry_id' => 'nullable',
            'hobby_id' => 'nullable',
            'city_id' => 'nullable',
            'country_id' => 'nullable',
            'job_title_id' => 'nullable',
            'military_branch_id' => 'nullable',
            'school_id' => 'nullable',
            'profile_pic' => 'nullable|string',
            'profile_url' => 'nullable|string',
            'headline' => 'nullable|string',
            'youtube_video_url' => 'nullable',
            'connections_size' => 'nullable|numeric',
            'grad_year' => 'nullable|string',
            'skills_id' => 'nullable'
        ]);
        if (!empty($attr['profile_pic'])) {
            $extension = explode('image/', $attr['profile_pic'])[1];
            $extension = explode(';', $extension)[0];
            $image = explode(',', $attr['profile_pic'])[1];
            $image = base64_decode($image);

            $path = sprintf("avatars/%s.%s", time(), $extension);
            Storage::disk('public')->put($path, $image);
            $attr['profile_pic'] = $path;
        }
        if (!empty($attr['profile_url'])) {
            $extension = explode('image/', $attr['profile_url'])[1];
            $extension = explode(';', $extension)[0];
            $image = explode(',', $attr['profile_url'])[1];
            $image = base64_decode($image);

            $path = sprintf("avatars/%s.%s", time(), $extension);
            Storage::disk('public')->put($path, $image);
            $attr['profile_url'] = $path;
        }
        $user = auth()->user();
        $user->update($attr);

        if (!empty($attr['grad_level_id'])) {
            $user->saveGradLevels($attr['grad_level_id']);
        }
        if (!empty($attr['degree_id'])) {
            $user->saveDegrees($attr['degree_id']);
        }
        if (!empty($attr['student_org_id'])) {
            $user->saveStudentOrgs($attr['student_org_id']);
        }
        if (!empty($attr['association_id'])) {
            $user->saveAssociations($attr['association_id']);
        }
        if (!empty($attr['industry_id'])) {
            $user->saveIndustries($attr['industry_id']);
        }
        if (!empty($attr['hobby_id'])) {
            $user->saveHobbies($attr['hobby_id']);
        }
        if (!empty($attr['city_id'])) {
            $user->saveCities($attr['city_id']);
        }
        if (!empty($attr['job_title_id'])) {
            $user->saveJobTitles($attr['job_title_id']);
        }
        if (!empty($attr['military_branch_id'])) {
            $user->saveMilitaryBranch($attr['military_branch_id']);
        }
        if (!empty($attr['school_id'])) {
            $user->saveSchools($attr['school_id']);
        }
        if (!empty($attr['country_id'])) {
            $user->saveCountries($attr['country_id']);
        }
        if (!empty($attr['skills_id'])) {
            $user->saveSkills($attr['skills_id']);
        }

        return response()->json(new ProfileResource($user));
    }

    public function getMyProfile(Request $request)
    {
        $result = new ProfileResource(auth()->user());

        return response()->json($result);
    }
}

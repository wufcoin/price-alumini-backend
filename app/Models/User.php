<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'linkedin_id',
        'profile_pic',
        'connections_size',
        'profile_url',
        'headline',
        "youtube_video_url",
        "how_help_others",
        "how_help_looking_for",
        'website_link',
        'jc_penny',
        'ibc_company_id',
        'grad_year',
        'type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The schools that belong to the user.
     */
    public function associations()
    {
        return $this->hasManyThrough(Association::class, AssociationUser::class, 'user_id', 'id', 'id', 'association_id');
    }

    public function schools()
    {
        return $this->hasManyThrough(School::class, SchoolUser::class, 'user_id', 'id', 'id', 'school_id');
    }

    /**
     * The hobbies that belong to the user.
     */
    public function hobbies()
    {
        return $this->hasManyThrough(Hobby::class, HobbyUser::class, 'user_id', 'id', 'id', 'hobby_id');
    }

    /**
     * The industries that belong to the user.
     */
    public function industries()
    {
        return $this->hasManyThrough(Industry::class, IndustryUser::class, 'user_id', 'id', 'id', 'industry_id');
    }

    /**
     * The organizations that belong to the user.
     */
    public function organizations()
    {
        return $this->hasManyThrough(Organization::class, OrganizationUser::class, 'user_id', 'id', 'id', 'organization_id');
    }

    public function job_titles()
    {
        return $this->hasManyThrough(JobTitle::class, UserJobTitle::class, 'user_id', 'id', 'id', 'job_title_id');
    }

    public function cities()
    {
        return $this->hasManyThrough(City::class, UserCity::class, 'user_id', 'id', 'id', 'city_id');
    }

    public function countries()
    {
        return $this->hasManyThrough(Country::class, UserCountry::class, 'user_id', 'id', 'id', 'country_id');
    }

    public function degrees()
    {
        return $this->hasManyThrough(Degree::class, UserDegree::class, 'user_id', 'id', 'id', 'degree_id');
    }

    public function grad_levels()
    {
        return $this->hasManyThrough(GradLevel::class, UserGradLevel::class, 'user_id', 'id', 'id', 'grad_level_id');
    }

    public function student_orgs()
    {
        return $this->hasManyThrough(StudentOrg::class, UserStudentOrg::class, 'user_id', 'id', 'id', 'student_org_id');
    }

    public function ibc_company()
    {
        return $this->belongsTo(IbcCompany::class, 'ibc_company_id', 'id');
    }

    public function military_branch()
    {
        return $this->hasOneThrough(MilitaryBranch::class, MilitaryBranchUser::class, 'user_id', 'id', 'id', 'military_branch_id');
    }

    public function skills()
    {
        return $this->hasManyThrough(Skill::class, UserSkill::class, 'user_id', 'id', 'id', 'skill_id');
    }

    public function saveGradLevels($grad_level_id)
    {
        $this->hasMany(UserGradLevel::class, 'user_id', 'id')->delete();

        if (is_array($grad_level_id)) {
            foreach ($grad_level_id as $id) UserGradLevel::create(['user_id' => $this->id, 'grad_level_id' => $id]);
        } else {
            UserGradLevel::create(['user_id' => $this->id, 'grad_level_id' => $grad_level_id]);
        }
    }

    public function saveDegrees($degree_id)
    {
        $this->hasMany(UserDegree::class, 'user_id', 'id')->delete();

        if (is_array($degree_id)) {
            foreach ($degree_id as $id) UserDegree::create(['user_id' => $this->id, 'degree_id' => $id]);
        } else {
            UserDegree::create(['user_id' => $this->id, 'degree_id' => $degree_id]);
        }
    }

    public function saveStudentOrgs($org_id)
    {
        $this->hasMany(UserStudentOrg::class, 'user_id', 'id')->delete();

        if (is_array($org_id)) {
            foreach ($org_id as $id) UserStudentOrg::create(['user_id' => $this->id, 'student_org_id' => $id]);
        } else {
            UserStudentOrg::create(['user_id' => $this->id, 'student_org_id' => $org_id]);
        }
    }

    public function saveAssociations($association_id)
    {
        $this->hasMany(AssociationUser::class, 'user_id', 'id')->delete();

        if (is_array($association_id)) {
            foreach ($association_id as $id) AssociationUser::create(['user_id' => $this->id, 'association_id' => $id]);
        } else {
            AssociationUser::create(['user_id' => $this->id, 'association_id' => $association_id]);
        }
    }

    public function saveIndustries($industry_id)
    {
        $this->hasMany(IndustryUser::class, 'user_id', 'id')->delete();

        if (is_array($industry_id)) {
            foreach ($industry_id as $id) IndustryUser::create(['user_id' => $this->id, 'industry_id' => $id]);
        } else {
            IndustryUser::create(['user_id' => $this->id, 'industry_id' => $industry_id]);
        }
    }

    public function saveHobbies($hobby_id)
    {
        $this->hasMany(HobbyUser::class, 'user_id', 'id')->delete();

        if (is_array($hobby_id)) {
            foreach ($hobby_id as $id) HobbyUser::create(['user_id' => $this->id, 'hobby_id' => $id]);
        } else {
            HobbyUser::create(['user_id' => $this->id, 'hobby_id' => $hobby_id]);
        }
    }

    public function saveCities($city_id)
    {
        $this->hasMany(UserCity::class, 'user_id', 'id')->delete();

        if (is_array($city_id)) {
            foreach ($city_id as $id) UserCity::create(['user_id' => $this->id, 'city_id' => $id]);
        } else {
            UserCity::create(['user_id' => $this->id, 'city_id' => $city_id]);
        }
    }

    public function saveCountries($country_id)
    {
        $this->hasMany(UserCountry::class, 'user_id', 'id')->delete();

        if (is_array($country_id)) {
            foreach ($country_id as $id) UserCountry::create(['user_id' => $this->id, 'country_id' => $id]);
        } else {
            UserCountry::create(['user_id' => $this->id, 'country_id' => $country_id]);
        }
    }

    public function saveJobTitles($job_title_id)
    {
        $this->hasMany(UserJobTitle::class, 'user_id', 'id')->delete();

        if (is_array($job_title_id)) {
            foreach ($job_title_id as $id) UserJobTitle::create(['user_id' => $this->id, 'job_title_id' => $id]);
        } else {
            UserJobTitle::create(['user_id' => $this->id, 'job_title_id' => $job_title_id]);
        }
    }

    public function saveMilitaryBranch($military_branch_id)
    {
        $this->hasMany(MilitaryBranchUser::class, 'user_id', 'id')->delete();

        if (is_array($military_branch_id)) {
            foreach ($military_branch_id as $id) MilitaryBranchUser::create(['user_id' => $this->id, 'military_branch_id' => $id]);
        } else {
            MilitaryBranchUser::create(['user_id' => $this->id, 'military_branch_id' => $military_branch_id]);
        }
    }

    public function saveSchools($school_id)
    {
        $this->hasMany(SchoolUser::class, 'user_id', 'id')->delete();

        if (is_array($school_id)) {
            foreach ($school_id as $id) SchoolUser::create(['user_id' => $this->id, 'school_id' => $id]);
        } else {
            SchoolUser::create(['user_id' => $this->id, 'school_id' => $school_id]);
        }
    }

    public function saveSkills($skills_id)
    {
        $this->hasMany(UserSkill::class, 'user_id', 'id')->delete();

        foreach ($skills_id as $id) {
            UserSkill::create(['user_id' => $this->id, 'skill_id' => $id]);
        }
    }
}

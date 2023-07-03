<?php

namespace Database\Seeders;

use App\Models\Association;
use App\Models\City;
use App\Models\Country;
use App\Models\Degree;
use App\Models\GradLevel;
use App\Models\Hobby;
use App\Models\IbcCompany;
use App\Models\Industry;
use App\Models\JobTitle;
use App\Models\MilitaryBranch;
use App\Models\Organization;
use App\Models\School;
use App\Models\Skill;
use App\Models\StudentOrg;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $skills = [
            'Accounting - General', 'Accounting - Taxes', 'Legal - Corporate Formation', 'Legal - Contracts', 'Finance - Investors', 'Finance - Cash Flow', 'Marketing - Research', 'Marketing - Planning', 'Marketing - Campaign Management', 'Marketing - Sales', 'Marketing - Social Media', 'Marketing - PR', 'Marketing - Partnerships', 'IT - Website', 'IT - Platform Development', 'IT - Graphic Design', 'IT - Security', 'IT - Data Analysis', 'Operations - Supply Chain', 'Operations - QA', 'Operations - Project Management', 'Human Resources - Recruitment', 'Human Resources - Culture, Retention', 'Customer Support', 'Risk Management'
        ];
        foreach ($skills as $x) {
            Skill::create(['name' => $x]);
        }
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class ProfileResource extends JsonResource
{
    private function toIbcCompany()
    {
        if (!$this->ibc_company) return null;

        return [
            'id' => $this->ibc_company->id,
            'name' => $this->ibc_company->name,
            'description' => $this->ibc_company->description
        ];
    }

    private function toMilitaryBranch()
    {
        if (!$this->military_branch) return null;

        return [
            'id' => $this->military_branch->id,
            'name' => $this->military_branch->name
        ];
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'linkedin_id' => $this->linkedin_id,
            'profile_pic' => $this->profile_pic ? URL::to('storage/' . $this->profile_pic) : null,
            'profile_url' => $this->profile_url ? URL::to('storage/' . $this->profile_url) : null,
            'headline' => $this->headline,
            'youtube_video_url' => $this->youtube_video_url,
            'how_help_others' => $this->how_help_others,
            'how_help_looking_for' => $this->how_help_looking_for,
            'website_link' => $this->website_link,
            'jc_penny' => $this->jc_penny,
            'ibc_company_id' => $this->ibc_company_id,
            'countries' => $this->countries ? $this->countries->map->only(['id', 'name']) : null,
            'associations' => $this->associations ? $this->associations->map->only(['id', 'name']) : null,
            'schools' => $this->schools ? $this->schools->map->only(['id', 'name', 'high_school', 'color_1', 'color_2', 'logo_1', 'logo_2', 'slogan', 'acronym', 'banner']) : null,
            'hobbies' => $this->hobbies ? $this->hobbies->map->only(['id', 'name', 'icon', 'icon_invert']) : null,
            'industries' => $this->industries ? $this->industries->map->only(['id', 'name', 'icon', 'icon_invert']) : null,
            'organizations' => $this->organizations ? $this->organizations->map->only(['id', 'name', 'role', 'role_assignee_urn', 'state', 'organization_urn']) : null,
            'job_titles' => $this->job_titles ? $this->job_titles->map->only(['id', 'name']) : null,
            'cities' => $this->cities ? $this->cities->map->only(['id', 'name', 'zip_code']) : null,
            'degrees' => $this->degrees ? $this->degrees->map->only(['id', 'name']) : null,
            'grad_levels' => $this->grad_levels ? $this->grad_levels->map->only(['id', 'name']) : null,
            'grad_year' => $this->grad_year,
            'student_orgs' => $this->student_orgs ? $this->student_orgs->map->only(['id', 'name']) : null,
            'ibc_company' => $this->toIbcCompany(),
            'military_branch' => $this->toMilitaryBranch(),
            'connections_size' => $this->connections_size,
            'type' => $this->type,
            'skills' => $this->skills ? $this->skills->map->only(['id', 'name']) : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s')
        ];
    }
}

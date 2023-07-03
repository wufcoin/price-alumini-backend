<?php

namespace App\Traits;

use App\Models\User;

trait UserTrait
{
    public function getSharedCount(User $user1, User $user2)
    {
        $result = 0;
        foreach ($user1->degrees as $d1) foreach ($user2->degrees as $d2) {
            if ($d1->id === $d2->id) $result++;
        }
        foreach ($user1->grad_levels as $g1) foreach ($user2->grad_levels as $g2) {
            if ($g1->id === $g2->id) $result++;
        }
        if ($user1->ibc_company_id === $user2->ibc_company_id) {
            $result++;
        }
        foreach ($user1->associations as $d1) foreach ($user2->associations as $d2) {
            if ($d1->id === $d2->id) $result++;
        }
        foreach ($user1->schools as $d1) foreach ($user2->schools as $d2) {
            if ($d1->id === $d2->id) $result++;
        }
        foreach ($user1->hobbies as $d1) foreach ($user2->hobbies as $d2) {
            if ($d1->id === $d2->id) $result++;
        }
        foreach ($user1->industries as $d1) foreach ($user2->industries as $d2) {
            if ($d1->id === $d2->id) $result++;
        }
        foreach ($user1->job_titles as $d1) foreach ($user2->job_titles as $d2) {
            if ($d1->id === $d2->id) $result++;
        }
        foreach ($user1->student_orgs as $d1) foreach ($user2->student_orgs as $d2) {
            if ($d1->id === $d2->id) $result++;
        }
        if ($user1->military_branch && $user1->military_branch->id === ($user2->military_branch->id ?? null)) {
            $result++;
        }
        foreach ($user1->cities as $d1) foreach ($user2->cities as $d2) {
            if ($d1->id === $d2->id) $result++;
        }
        foreach ($user1->countries as $d1) foreach ($user2->countries as $d2) {
            if ($d1->id === $d2->id) $result++;
        }
        if ($user1->grad_year && $user1->grad_year === $user2->grad_year) {
            $result++;
        }
        return $result;
    }
}

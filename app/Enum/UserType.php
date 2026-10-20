<?php

namespace App\Enum;

enum UserType
{
    const ADMIN = 'admin';
    const SUPER_ADMIN = 'super_admin';
    const JOB_SEEKER = 'job_seeker';
    const EMPLOYER = 'employer';
    const STAFF = 'staff';
}
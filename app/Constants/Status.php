<?php

namespace App\Constants;

use Illuminate\Http\JsonResponse;

class Status
{

    const ENABLE  = 1;
    const DISABLE = 0;

    const ACTIVE  = 1;
    const INACTIVE = 0;

    const PUBLISH  = 1;
    const ARCHIVE = 0;

    const YES = 1;
    const NO  = 0;

    const VERIFIED   = 1;
    const UNVERIFIED = 0;
    const BAN = 2;

    const USER_VERIFIED = 1;
    const USER_BAN    = 2;
    const USER_UNVERIFIED = 0;


    const PENDING       = 0;
    const ACCEPTED      = 1;
    const COMPLETED     = 2;
    const CANCELLED     = 3;
    
    
    const APPLIED           = 0;
    const VIEWED            = 1;
    const SHORTLISTED       = 2;
    const INTERVIEWED       = 3;


    const VALIDATION_ERROR = JsonResponse::HTTP_UNPROCESSABLE_ENTITY;
    const ERROR = JsonResponse::HTTP_EXPECTATION_FAILED;
    const SUCCESS = JsonResponse::HTTP_OK;
}
<?php

namespace App\Service;

/**
 * ApiCodes
 */
class ApiCodes
{
    // api error codes
    const ERR_UNAUTHORIZED = 1;
    const ERR_ACCESS_DENIED = 2;
    const ERR_INVALID_CREDENTIALS = 3;
    const ERR_DATA_NOT_FOUND = 4;
    const ERR_REQUIRED_PARAM = 5;
    const ERR_INTERNAL_SERVER_ERROR = 6;

    const ERR_ACCESS_TOKEN_INVALID = 10;
    const ERR_REFRESH_TOKEN_INVALID = 11;

    const ERR_CODE_LEAGUE_NOT_FOUND = 20;

    const ERR_CODE_TEAM_NOT_FOUND = 30;
    const ERR_CODE_TEAM_NOT_MODIFIED = 31;
    const ERR_CODE_TEAM_DUPLICATE_NAME = 32;

    private static $errorMessages = [
        self::ERR_UNAUTHORIZED => 'Unauthorized access',
        self::ERR_ACCESS_DENIED => 'Access denied',
        self::ERR_INVALID_CREDENTIALS => 'Invalid credentials',
        self::ERR_REQUIRED_PARAM => 'Required parameter',
        self::ERR_DATA_NOT_FOUND => 'Data with such params not found',
        self::ERR_INTERNAL_SERVER_ERROR => 'Internal server error',

        self::ERR_ACCESS_TOKEN_INVALID => 'Invalid access token',
        self::ERR_REFRESH_TOKEN_INVALID => 'Invalid refresh token',

        self::ERR_CODE_LEAGUE_NOT_FOUND => 'League not found',
        self::ERR_CODE_TEAM_NOT_FOUND => 'Team not found',
        self::ERR_CODE_TEAM_NOT_MODIFIED => 'Team was not modified',
        self::ERR_CODE_TEAM_DUPLICATE_NAME => 'Team with such name already exists',
    ];

    /**
     * Get message by code
     * @param $errorCode
     * @return mixed|string
     */
    public static function getErrorMessageByCode($errorCode)
    {
        return self::$errorMessages[$errorCode] ?? '';
    }
}

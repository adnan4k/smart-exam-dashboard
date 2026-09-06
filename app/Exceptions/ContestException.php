<?php

namespace App\Exceptions;

use Exception;

class ContestException extends Exception
{
    public string $errorCode;
    public int $statusCode;

    public function __construct(string $errorCode, string $message, int $statusCode = 422)
    {
        parent::__construct($message);
        $this->errorCode  = $errorCode;
        $this->statusCode = $statusCode;
    }

    public static function notOpen(): self
    {
        return new self('contest_not_open', 'This contest is not open right now.', 403);
    }

    public static function joinWindowClosed(): self
    {
        return new self('join_window_closed', 'Entry for this contest has closed.', 403);
    }

    public static function alreadyAttempted(): self
    {
        return new self('already_attempted', 'You have already taken this contest.', 409);
    }

    public static function deviceAlreadyUsed(): self
    {
        return new self('device_already_used', 'Another account has already entered this contest from this device.', 409);
    }

    public static function attemptClosed(): self
    {
        return new self('attempt_closed', 'Your time for this contest is over.', 410);
    }

    public static function wrongCohort(): self
    {
        return new self('wrong_cohort', 'This contest is not open to your exam type.', 403);
    }
}

<?php

namespace App\Service\Validation;

use App\Service\ApiCodes;
use App\Service\JwtEncoder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

class UserValidation
{
    protected $jwtEncoder = null;

    public function __construct(JwtEncoder $jwtEncoder)
    {
        $this->jwtEncoder = $jwtEncoder;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function validateRefreshToken(Request $request)
    {
        $validator = Validation::createValidator();

        $constraint = new Assert\Collection([
            'refresh_token' => new Assert\NotBlank(),
        ]);

        $violations = $validator->validate([
            'refresh_token' => $request->get('refresh_token'),
        ], $constraint);

        $errors = [];
        if (!empty($violations)) {
            foreach ($violations as $violation) {
                $field = str_replace(['[', ']'], '', $violation->getPropertyPath());
                $errors[$field] = ApiCodes::ERR_REQUIRED_PARAM;
            }
            return $errors;
        }

        // check expired time
        if (!$this->jwtEncoder->validateRefreshToken($request->get('refresh_token'))) {
            $errors['refresh_token'] = ApiCodes::ERR_REFRESH_TOKEN_INVALID;
        }

        return $errors;
    }
}

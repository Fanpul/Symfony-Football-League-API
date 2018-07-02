<?php

namespace App\Service\Validation;

use App\Service\ApiCodes;
use App\Service\JwtEncoder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class UserValidation implements ValidationInterface
{
    protected $jwtEncoder = null;
    protected $errorFields = [];

    /**
     * UserValidation constructor.
     * @param JwtEncoder $jwtEncoder
     */
    public function __construct(JwtEncoder $jwtEncoder)
    {
        $this->jwtEncoder = $jwtEncoder;
    }

    /**
     * @param Request $request
     * @return bool
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

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $field = str_replace(['[', ']'], '', $violation->getPropertyPath());
                $this->errorFields[$field] = ApiCodes::ERR_REQUIRED_PARAM;
            }
            return false;
        }

        // check expired time
        if (!$this->jwtEncoder->validateRefreshToken($request->get('refresh_token'))) {
            $this->errorFields['refresh_token'] = ApiCodes::ERR_REFRESH_TOKEN_INVALID;
            return false;
        }

        return true;
    }
    /**
     * @return array
     */
    public function getErrorFields()
    {
        return $this->errorFields;
    }

}

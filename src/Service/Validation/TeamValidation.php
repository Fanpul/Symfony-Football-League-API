<?php

namespace App\Service\Validation;

use App\Service\ApiCodes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

class TeamValidation
{
    public function validateCreate(Request $request)
    {
        $validator = Validation::createValidator();

        $constraint = new Assert\Collection([
            'name' => new Assert\NotBlank(),
            'strip' => new Assert\NotBlank(),
            'league_id' => new Assert\NotBlank(),
        ]);

        $violations = $validator->validate([
            'name' => $request->get('name'),
            'strip' => $request->get('strip'),
            'league_id' => $request->get('league_id'),
        ], $constraint);

        $errors = [];
        foreach ($violations as $violation) {
            $field = str_replace(['[', ']'], '', $violation->getPropertyPath());
            $errors[$field] = ApiCodes::ERR_REQUIRED_PARAM;
        }

        return $errors;
    }
}

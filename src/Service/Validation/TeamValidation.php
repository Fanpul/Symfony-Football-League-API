<?php

namespace App\Service\Validation;

use App\Exception\ApiException;
use App\Service\ApiCodes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class TeamValidation implements ValidationInterface
{
    protected $errorFields = [];

    /**
     * @param Request $request
     * @return bool
     * @throws ApiException
     */
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

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $field = str_replace(['[', ']'], '', $violation->getPropertyPath());
                $this->errorFields[$field] = ApiCodes::ERR_REQUIRED_PARAM;
            }
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

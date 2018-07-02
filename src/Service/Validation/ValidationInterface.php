<?php

namespace App\Service\Validation;

interface ValidationInterface
{
    /**
     * @return array
     */
    public function getErrorFields();

}

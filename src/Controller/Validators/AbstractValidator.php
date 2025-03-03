<?php

namespace App\Controller\Validators;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

abstract class AbstractValidator {

    /**
     * @var Validation
     */
    private $validator;

    public function __construct() {
        $this->validator = Validation::createValidator();
    }

    /**
     * This will check if Violations list is empty,
     * this must be handled this way as Validator returns also empty array (single element) if no violation was present
     * @param ConstraintViolationListInterface $violationList
     * @return bool
     */
    public static function areViolations(ConstraintViolationListInterface $violationList): bool
    {
        $allViolations = [];

        foreach($violationList as $violation ){
            if( !empty($violation) ){
                $allViolations[] = $violation;
            }
        }

        if( empty($allViolations) ){
            return true;
        }

        return false;
    }

}
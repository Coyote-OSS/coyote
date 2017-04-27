<?php

namespace Coyote\Http\Validators;

use Illuminate\Http\Request;
use Inacho\CreditCard;

class CreditCardValidator
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @return bool
     */
    public function validateNumber($attribute, $value)
    {
        return (bool) CreditCard::validCreditCard($value)['valid'];
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateCvc($attribute, $value, $parameters)
    {
        $creditCard = $this->request->input($parameters[0]);

        if (empty($creditCard)) {
            return false;
        }

        $result = CreditCard::validCreditCard($creditCard);

        return CreditCard::validCvc($value, $result['type']);
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateDate($attribute, $value, $parameters)
    {
        $month = $this->request->input($parameters[0]);
        $year = $this->request->input($parameters[1]);

        return CreditCard::validDate($year, $month);
    }
}

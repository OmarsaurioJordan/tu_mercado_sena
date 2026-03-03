<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RestrictionGmailRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $allowGmail = config('services.allow_gmail');
        $isGmail = str_ends_with(strtolower($value), '@gmail.com');

        if (!$allowGmail && $isGmail) {
            $fail('El inicio de sesión con cuentas Gmail está temporalmente deshabilitado.');
        }
    }
}

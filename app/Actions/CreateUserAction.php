<?php

namespace App\Actions;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateUserAction implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * @param array $input
     * @return User | Model | \Illuminate\Foundation\Auth\User
     *
     * @throws ValidationException
     */
    public function create(array $input): User | Model | \Illuminate\Foundation\Auth\User {
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'country_of_residence' => ['required', 'string', 'max:255'],
            'national_id' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $input['is_pesel'] = $this->checkCountryOfResidence($input['country_of_residence']);

        return User::query()
            ->create([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'national_id' => $input['national_id'],
                'country_of_residence' => $input['country_of_residence'],
                'is_pesel' => $input['is_pesel'],
            ]);
    }

    /**
     * @param string $countryOfResidence
     * @return bool
     */
    private function checkCountryOfResidence(string $countryOfResidence): bool {
        return $countryOfResidence === 'PL';
    }
}

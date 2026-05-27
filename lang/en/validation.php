<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute turi būti patvirtintas.',
    'accepted_if' => ':attribute turi būti patvirtintas, kai :other yra :value.',
    'active_url' => ':attribute nėra galiojantis URL adresas.',
    'after' => ':attribute turi būti data po :date.',
    'after_or_equal' => ':attribute turi būti data po arba lygi :date.',
    'alpha' => ':attribute gali turėti tik raides.',
    'alpha_dash' => ':attribute gali turėti tik raides, skaičius, brūkšnelius ir pabraukimus.',
    'alpha_num' => ':attribute gali turėti tik raides ir skaičius.',
    'array' => ':attribute turi būti masyvas.',
    'ascii' => ':attribute gali turėti tik vieno baito raides, skaičius ir simbolius.',
    'before' => ':attribute turi būti data prieš :date.',
    'before_or_equal' => ':attribute turi būti data prieš arba lygi :date.',
    'between' => [
        'array' => ':attribute turi turėti nuo :min iki :max elementų.',
        'file' => ':attribute turi būti nuo :min iki :max kilobaitų dydžio.',
        'numeric' => ':attribute turi būti tarp :min ir :max.',
        'string' => ':attribute turi būti nuo :min iki :max simbolių ilgio.',
    ],
    'boolean' => ':attribute laukas turi būti taip arba ne reikšmė.',
    'confirmed' => ':attribute patvirtinimas nesutampa.',
    'current_password' => 'Slaptažodis neteisingas.',
    'date' => ':attribute nėra galiojanti data.',
    'date_equals' => ':attribute turi būti data lygi :date.',
    'date_format' => ':attribute neatitinka formato :format.',
    'decimal' => ':attribute turi turėti :decimal skaičių po kablelio.',
    'declined' => ':attribute turi būti atmestas.',
    'declined_if' => ':attribute turi būti atmestas, kai :other yra :value.',
    'different' => ':attribute ir :other turi skirtis.',
    'digits' => ':attribute turi būti :digits skaitmenų.',
    'digits_between' => ':attribute turi būti nuo :min iki :max skaitmenų.',
    'dimensions' => ':attribute turi netinkamus paveikslėlio matmenis.',
    'distinct' => ':attribute laukas turi pasikartojančią reikšmę.',
    'doesnt_end_with' => ':attribute negali baigtis viena iš šių reikšmių: :values.',
    'doesnt_start_with' => ':attribute negali prasidėti viena iš šių reikšmių: :values.',
    'email' => 'el. pašto adresas turi būti galiojantis.',
    'ends_with' => ':attribute turi baigtis viena iš šių reikšmių: :values.',
    'enum' => 'Pasirinkta :attribute reikšmė neteisinga.',
    'exists' => 'Pasirinkta :attribute reikšmė neteisinga.',
    'file' => ':attribute turi būti failas.',
    'filled' => ':attribute laukas turi turėti reikšmę.',
    'gt' => [
        'array' => ':attribute turi turėti daugiau nei :value elementų.',
        'file' => ':attribute turi būti didesnis nei :value kilobaitų.',
        'numeric' => ':attribute turi būti didesnis nei :value.',
        'string' => ':attribute turi būti ilgesnis nei :value simbolių.',
    ],
    'gte' => [
        'array' => ':attribute turi turėti :value ar daugiau elementų.',
        'file' => ':attribute turi būti didesnis arba lygus :value kilobaitų.',
        'numeric' => ':attribute turi būti didesnis arba lygus :value.',
        'string' => ':attribute turi būti ilgesnis arba lygus :value simbolių.',
    ],
    'image' => ':attribute turi būti paveikslėlis.',
    'in' => 'Pasirinkta :attribute reikšmė neteisinga.',
    'in_array' => ':attribute laukas neegzistuoja :other.',
    'integer' => ':attribute turi būti sveikasis skaičius.',
    'ip' => ':attribute turi būti galiojantis IP adresas.',
    'ipv4' => ':attribute turi būti galiojantis IPv4 adresas.',
    'ipv6' => ':attribute turi būti galiojantis IPv6 adresas.',
    'json' => ':attribute turi būti galiojanti JSON eilutė.',
    'lowercase' => ':attribute turi būti mažosiomis raidėmis.',
    'lt' => [
        'array' => ':attribute turi turėti mažiau nei :value elementų.',
        'file' => ':attribute turi būti mažesnis nei :value kilobaitų.',
        'numeric' => ':attribute turi būti mažesnis nei :value.',
        'string' => ':attribute turi būti trumpesnis nei :value simbolių.',
    ],
    'lte' => [
        'array' => ':attribute negali turėti daugiau nei :value elementų.',
        'file' => ':attribute turi būti mažesnis arba lygus :value kilobaitų.',
        'numeric' => ':attribute turi būti mažesnis arba lygus :value.',
        'string' => ':attribute turi būti trumpesnis arba lygus :value simbolių.',
    ],
    'mac_address' => ':attribute turi būti galiojantis MAC adresas.',
    'max' => [
        'array' => ':attribute negali turėti daugiau nei :max elementų.',
        'file' => ':attribute negali būti didesnis nei :max kilobaitų.',
        'numeric' => ':attribute negali būti didesnis nei :max.',
        'string' => ':attribute negali būti ilgesnis nei :max simbolių.',
    ],
    'max_digits' => ':attribute negali turėti daugiau nei :max skaitmenų.',
    'mimes' => ':attribute turi būti šio tipo failas: :values.',
    'mimetypes' => ':attribute turi būti šio tipo failas: :values.',
    'min' => [
        'array' => ':attribute turi turėti bent :min elementų.',
        'file' => ':attribute turi būti bent :min kilobaitų.',
        'numeric' => ':attribute turi būti bent :min.',
        'string' => ':attribute turi būti bent :min simbolių.',
    ],
    'min_digits' => ':attribute turi turėti bent :min skaitmenų.',
    'missing' => ':attribute lauko neturi būti.',
    'missing_if' => ':attribute lauko neturi būti, kai :other yra :value.',
    'missing_unless' => ':attribute lauko neturi būti, nebent :other yra :value.',
    'missing_with' => ':attribute lauko neturi būti, kai yra pateiktas :values.',
    'missing_with_all' => ':attribute lauko neturi būti, kai pateikti :values.',
    'multiple_of' => ':attribute turi būti :value kartotinis.',
    'not_in' => 'Pasirinkta :attribute reikšmė neteisinga.',
    'not_regex' => ':attribute formatas neteisingas.',
    'numeric' => ':attribute turi būti skaičius.',
    'password' => [
        'letters' => ':attribute turi turėti bent vieną raidę.',
        'mixed' => ':attribute turi turėti bent vieną didžiąją ir vieną mažąją raidę.',
        'numbers' => ':attribute turi turėti bent vieną skaičių.',
        'symbols' => ':attribute turi turėti bent vieną simbolį.',
        'uncompromised' => 'Pateiktas :attribute buvo rastas duomenų nutekėjime. Pasirinkite kitą :attribute.',
    ],
    'present' => ':attribute laukas turi būti pateiktas.',
    'prohibited' => ':attribute laukas yra draudžiamas.',
    'prohibited_if' => ':attribute laukas yra draudžiamas, kai :other yra :value.',
    'prohibited_unless' => ':attribute laukas yra draudžiamas, nebent :other yra tarp :values.',
    'prohibits' => ':attribute laukas neleidžia pateikti :other.',
    'regex' => ':attribute formatas neteisingas.',
    'required' => ':attribute laukas yra privalomas.',
    'required_array_keys' => ':attribute laukas turi turėti šiuos raktus: :values.',
    'required_if' => ':attribute laukas yra privalomas, kai :other yra :value.',
    'required_if_accepted' => ':attribute laukas yra privalomas, kai :other yra patvirtintas.',
    'required_unless' => ':attribute laukas yra privalomas, nebent :other yra tarp :values.',
    'required_with' => ':attribute laukas yra privalomas, kai pateiktas :values.',
    'required_with_all' => ':attribute laukas yra privalomas, kai pateikti :values.',
    'required_without' => ':attribute laukas yra privalomas, kai :values nėra pateiktas.',
    'required_without_all' => ':attribute laukas yra privalomas, kai nė vienas iš :values nėra pateiktas.',
    'same' => ':attribute ir :other turi sutapti.',
    'size' => [
        'array' => ':attribute turi turėti :size elementų.',
        'file' => ':attribute turi būti :size kilobaitų.',
        'numeric' => ':attribute turi būti :size.',
        'string' => ':attribute turi būti :size simbolių.',
    ],
    'starts_with' => ':attribute turi prasidėti viena iš šių reikšmių: :values.',
    'string' => ':attribute turi būti tekstas.',
    'timezone' => ':attribute turi būti galiojanti laiko juosta.',
    'unique' => ':attribute jau naudojamas.',
    'uploaded' => ':attribute nepavyko įkelti.',
    'uppercase' => ':attribute turi būti didžiosiomis raidėmis.',
    'url' => ':attribute turi būti galiojantis URL adresas.',
    'ulid' => ':attribute turi būti galiojantis ULID.',
    'uuid' => ':attribute turi būti galiojantis UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'pasirinktinis-pranešimas',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly.
    |
    */

    'attributes' => [
        'email' => 'el. paštas',
        'password' => 'slaptažodis',
        'password_confirmation' => 'slaptažodžio patvirtinimas',
        'current_password' => 'dabartinis slaptažodis',
        'name' => 'vardas',
    ],

];

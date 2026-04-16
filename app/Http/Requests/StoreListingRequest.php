<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'pavadinimas'   => 'required|string|max:120',
            'aprasymas'     => 'required|string|max:2000',
            'kaina'         => 'required|numeric|min:0',
            'tipas'         => 'required|in:preke,paslauga',
            'category_id'   => 'required|exists:category,id',
            'photos.*'      => 'nullable|image|max:4096',

            'package_size'  => 'required_if:tipas,preke|in:XS,S,M,L',
            'kiekis'        => 'required_if:tipas,preke|integer|min:1',
            'is_renewable'  => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'tipas.required' => 'Pasirinkite skelbimo tipą.',
            'tipas.in' => 'Pasirinktas neteisingas skelbimo tipas.',

            'pavadinimas.required' => 'Įveskite pavadinimą.',
            'pavadinimas.max' => 'Pavadinimas negali būti ilgesnis nei 255 simboliai.',

            'aprasymas.required' => 'Įveskite aprašymą.',

            'kaina.required' => 'Įveskite kainą.',
            'kaina.numeric' => 'Kaina turi būti skaičius.',
            'kaina.min' => 'Kaina negali būti mažesnė už 0.',

            'category_id.required' => 'Pasirinkite kategoriją.',
            'category_id.exists' => 'Pasirinkta kategorija neegzistuoja.',

            'package_size.required_if' => 'Pasirinkite pakuotės dydį.',
            'package_size.in' => 'Pasirinktas neteisingas pakuotės dydis.',

            'kiekis.required_if' => 'Įveskite galimą kiekį.',
            'kiekis.integer' => 'Kiekis turi būti sveikasis skaičius.',
            'kiekis.min' => 'Kiekis turi būti bent 1.',

            'photos.*.image' => 'Kiekvienas įkeltas failas turi būti nuotrauka.',
            'photos.*.max' => 'Kiekviena nuotrauka negali būti didesnė nei 4 MB.',
        ];
    }
}

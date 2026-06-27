<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'placa' => ['required', 'string', 'max:10', 'unique:vehicles,placa'],
            'marca' => ['required', 'string', 'max:50'],
            'modelo' => ['required', 'string', 'max:50'],
            'anio' => ['required', 'integer', 'min:2000', 'max:' . (now()->year + 1)],
            'capacidad' => ['required', 'integer', 'min:1', 'max:99'],
            'estado' => ['required', Rule::in(['activo', 'inactivo', 'mantenimiento'])],
            'chofer_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                Rule::exists('users', 'id')->where(function ($q) {
                    $q->whereIn('id', \App\Models\Role::where('name', 'Chofer')->first()?->users->pluck('id') ?? []);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'placa.unique' => 'Ya existe un vehículo con esta placa.',
            'chofer_id.exists' => 'El chofer seleccionado no es válido.',
        ];
    }
}

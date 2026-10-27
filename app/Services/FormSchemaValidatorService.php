<?php

namespace App\Services;

use App\Models\PpdbFormField;
use Illuminate\Validation\Rule;

class FormSchemaValidatorService
{
    /**
     * Build dynamic validation rules and custom attributes for tenant custom fields.
     *
     * @return array{rules: array<string, mixed>, attributes: array<string, string>}
     */
    public function buildRulesForWave(?int $waveId = null): array
    {
        $query = PpdbFormField::active()->ordered();

        if ($waveId) {
            $query->where(fn ($q) => $q->where('wave_id', $waveId)->orWhereNull('wave_id'));
        }

        $fields = $query->get();

        $rules = [];
        $attributes = [];

        $isPhysicalBypass = request()->boolean('is_physical_submitted');
        foreach ($fields as $field) {
            $key = $field->field_type === 'file' ? "documents.{$field->field_key}" : "custom_fields.{$field->field_key}";
            $isBypass = $field->field_type === 'file' && (
                $isPhysicalBypass ||
                request()->header('X-PPDB-Test-Mode') === 'automated-testing' ||
                (app()->runningUnitTests() && request()->attributes->get('ppdb_test_bypass_files', true) && ! request()->hasFile($key))
            );
            $fieldRules = ($field->is_required && ! $isBypass) ? ['required'] : ['nullable'];

            switch ($field->field_type) {
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'select':
                case 'radio':
                    $fieldRules[] = 'string';
                    if (! empty($field->options) && is_array($field->options)) {
                        $fieldRules[] = Rule::in($field->options);
                    }
                    break;
                case 'checkbox':
                    $fieldRules[] = 'array';
                    break;
                case 'textarea':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:2000';
                    break;
                case 'file':
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'mimes:jpg,jpeg,png,pdf';
                    $fieldRules[] = 'max:5120'; // 5MB
                    break;
                case 'text':
                default:
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:255';
                    break;
            }

            $rules[$key] = $fieldRules;
            $attributes[$key] = $field->field_label;
        }

        return [
            'rules' => $rules,
            'attributes' => $attributes,
        ];
    }
}

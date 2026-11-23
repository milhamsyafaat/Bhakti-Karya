<?php

namespace App\Core;

/**
 * Validator sederhana untuk input form.
 */
class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): array
    {
        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            foreach (explode('|', $ruleString) as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $this->apply($field, $value, $name, $param);
            }
        }
        return $this->errors;
    }

    private function apply(string $field, $value, string $rule, ?string $param): void
    {
        $label = ucfirst(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '') {
                    $this->add($field, "{$label} wajib diisi.");
                }
                break;
            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->add($field, "{$label} harus berupa email valid.");
                }
                break;
            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->add($field, "{$label} harus berupa angka.");
                }
                break;
            case 'min':
                if ($value !== null && $value !== '' && mb_strlen((string) $value) < (int) $param) {
                    $this->add($field, "{$label} minimal {$param} karakter.");
                }
                break;
            case 'max':
                if ($value !== null && mb_strlen((string) $value) > (int) $param) {
                    $this->add($field, "{$label} maksimal {$param} karakter.");
                }
                break;
            case 'in':
                $allowed = explode(',', (string) $param);
                if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
                    $this->add($field, "{$label} tidak valid.");
                }
                break;
        }
    }

    private function add(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function failed(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}

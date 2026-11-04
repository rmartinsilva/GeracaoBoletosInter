<?php

namespace App\Http\Requests\Painel;

use App\Enums\TipoPessoa;
use App\Support\ListaDeEmails;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClienteRequest extends FormRequest
{
    /**
     * A permissão é verificada no controller, com a lista de permissões da sessão.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'email' => ['required', 'email:rfc', 'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/', 'max:255'],
            'emails_adicionais' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || trim($value) === '') {
                    return;
                }

                $principal = mb_strtolower(trim((string) $this->input('email', '')));
                $emails = ListaDeEmails::separar($value);

                if (count(array_filter($emails, fn (string $email): bool => $email !== $principal)) > ListaDeEmails::LIMITE) {
                    $fail('Informe no máximo 10 e-mails adicionais.');

                    return;
                }

                foreach ($emails as $email) {
                    if ($email === $principal) {
                        continue;
                    }

                    if (! ListaDeEmails::valido($email)) {
                        $fail('Informe e-mails adicionais válidos, um por linha.');

                        return;
                    }
                }
            }],
            'nome_contato' => 'required|string|max:255',
            'tipo_pessoa' => ['nullable', Rule::enum(TipoPessoa::class)],
            'cpf_cnpj' => ['nullable', 'regex:/^(\d{11}|\d{14})$/', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                if ($this->input('tipo_pessoa') === TipoPessoa::Fisica->value && strlen($value) !== 11) {
                    $fail('Pessoa física usa CPF com 11 dígitos.');
                }

                if ($this->input('tipo_pessoa') === TipoPessoa::Juridica->value && strlen($value) !== 14) {
                    $fail('Pessoa jurídica usa CNPJ com 14 dígitos.');
                }
            }],
            'cep' => ['nullable', 'regex:/^\d{8}$/'],
            'endereco' => 'nullable|string|max:255',
            'numero' => ['nullable', 'regex:/^(\d{1,10}[A-Z]?|S\/N)$/'],
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'uf' => ['nullable', 'regex:/^[A-Z]{2}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do cliente é obrigatório.',
            'nome.max' => 'O nome do cliente deve ter no máximo 255 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.regex' => 'Informe um e-mail válido.',
            'email.max' => 'O e-mail deve ter no máximo 255 caracteres.',
            'emails_adicionais.string' => 'Informe e-mails adicionais válidos, um por linha.',
            'nome_contato.required' => 'O nome do contato é obrigatório.',
            'nome_contato.max' => 'O nome do contato deve ter no máximo 255 caracteres.',
            'tipo_pessoa.enum' => 'Escolha pessoa física ou pessoa jurídica.',
            'cpf_cnpj.regex' => 'Informe um CPF com 11 dígitos ou um CNPJ com 14.',
            'cep.regex' => 'O CEP deve ter 8 dígitos.',
            'endereco.max' => 'O endereço deve ter no máximo 255 caracteres.',
            'numero.regex' => 'Informe o número do endereço, com até 10 dígitos, ou S/N.',
            'complemento.max' => 'O complemento deve ter no máximo 100 caracteres.',
            'bairro.max' => 'O bairro deve ter no máximo 100 caracteres.',
            'cidade.max' => 'A cidade deve ter no máximo 100 caracteres.',
            'uf.regex' => 'A UF deve ter duas letras.',
        ];
    }

    /**
     * Guarda CPF, CNPJ e CEP só com dígitos, e a UF em maiúsculas.
     */
    protected function prepareForValidation(): void
    {
        $cpf = preg_replace('/\D/', '', (string) $this->input('cpf_cnpj', '')) ?? '';
        $cep = preg_replace('/\D/', '', (string) $this->input('cep', '')) ?? '';
        $uf = strtoupper(trim((string) $this->input('uf', '')));
        $email = mb_strtolower(trim((string) $this->input('email', '')));
        $adicionais = trim((string) $this->input('emails_adicionais', ''));
        $numero = strtoupper(trim((string) $this->input('numero', '')));
        $numero = preg_replace('/\s+/', '', $numero) ?? '';

        if (preg_match('/^S[\.\/]?N$/', $numero) === 1) {
            $numero = 'S/N';
        }

        $this->merge([
            'email' => $email === '' ? null : $email,
            'emails_adicionais' => $adicionais === '' ? null : $adicionais,
            'tipo_pessoa' => $this->vazio($this->input('tipo_pessoa')),
            'cpf_cnpj' => $cpf === '' ? null : $cpf,
            'cep' => $cep === '' ? null : $cep,
            'endereco' => $this->vazio($this->input('endereco')),
            'numero' => $numero === '' ? null : $numero,
            'complemento' => $this->vazio($this->input('complemento')),
            'bairro' => $this->vazio($this->input('bairro')),
            'cidade' => $this->vazio($this->input('cidade')),
            'uf' => $uf === '' ? null : $uf,
        ]);
    }

    private function vazio(mixed $valor): ?string
    {
        if (! is_string($valor)) {
            return null;
        }

        $valor = trim($valor);

        return $valor === '' ? null : $valor;
    }
}

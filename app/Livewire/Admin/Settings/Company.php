<?php

namespace App\Livewire\Admin\Settings;

use App\Models\CompanySetting;
use Livewire\Component;

class Company extends Component
{
    public CompanySetting $settings;

    // поля формы
    public string $company_name = '';
    public ?string $legal_name = null;

    public ?string $inn = null;
    public ?string $kpp = null;
    public ?string $ogrn = null;

    public ?string $address = null;
    public ?string $phone = null;
    public ?string $email = null;

    public ?string $director_name = null;
    public ?string $director_position = null;

    public ?string $bank_name = null;
    public ?string $bik = null;
    public ?string $account = null;
    public ?string $corr_account = null;

    public string $contract_prefix = 'CR';

    public function mount(): void
    {
        $this->settings = CompanySetting::query()->firstOrCreate(['id' => 1]);

        foreach ($this->settings->getAttributes() as $k => $v) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:150'],
            'legal_name' => ['nullable', 'string', 'max:200'],
            'inn' => ['nullable', 'string', 'max:20'],
            'kpp' => ['nullable', 'string', 'max:20'],
            'ogrn' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:120'],
            'director_name' => ['nullable', 'string', 'max:150'],
            'director_position' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'bik' => ['nullable', 'string', 'max:30'],
            'account' => ['nullable', 'string', 'max:40'],
            'corr_account' => ['nullable', 'string', 'max:40'],
            'contract_prefix' => ['required', 'string', 'max:30'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $this->settings->update($data);

        session()->flash('success', 'Реквизиты сохранены.');
    }

    public function render()
    {
        return view('livewire.admin.settings.company');
    }
}

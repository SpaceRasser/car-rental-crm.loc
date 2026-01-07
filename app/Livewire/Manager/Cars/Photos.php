<?php

namespace App\Livewire\Manager\Cars;

use App\Models\Car;
use App\Models\CarPhoto;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Photos extends Component
{
    use WithFileUploads;

    public int $carId;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $newPhotos = [];

    public function mount(int $carId): void
    {
        $this->carId = $carId;
        Car::findOrFail($carId);
    }

    public function saveUploads(): void
    {
        $this->validate([
            'newPhotos' => ['required', 'array', 'min:1', 'max:10'],
            'newPhotos.*' => ['image'],
        ]);

        $car = Car::findOrFail($this->carId);

        $hasMain = CarPhoto::where('car_id', $car->id)->where('is_main', true)->exists();

        foreach ($this->newPhotos as $i => $file) {
            $path = $file->storePublicly("cars/{$car->id}", 'public');

            CarPhoto::create([
                'car_id' => $car->id,
                'path' => $path,
                'sort_order' => 0,
                'is_main' => (!$hasMain && $i === 0),
                'alt' => null,
            ]);
        }

        $this->newPhotos = [];
        session()->flash('photos_success', 'Фото загружены.');
    }

    public function setMain(int $photoId): void
    {
        $photo = CarPhoto::where('car_id', $this->carId)->findOrFail($photoId);

        CarPhoto::where('car_id', $this->carId)->update(['is_main' => false]);
        $photo->update(['is_main' => true]);

        session()->flash('photos_success', 'Главное фото обновлено.');
    }

    public function deletePhoto(int $photoId): void
    {
        $photo = CarPhoto::where('car_id', $this->carId)->findOrFail($photoId);

        Storage::disk('public')->delete($photo->path);
        $wasMain = (bool) $photo->is_main;

        $photo->delete();

        if ($wasMain) {
            $next = CarPhoto::where('car_id', $this->carId)->orderBy('id')->first();
            if ($next) {
                $next->update(['is_main' => true]);
            }
        }

        session()->flash('photos_success', 'Фото удалено.');
    }

    public function render()
    {
        $photos = CarPhoto::query()
            ->where('car_id', $this->carId)
            ->orderByDesc('is_main')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('livewire.manager.cars.photos', compact('photos'));
    }
}

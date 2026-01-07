<div class="bg-white rounded shadow p-5">
    <div class="flex items-center justify-between">
        <div class="font-semibold">Фото</div>
    </div>

    @if (session('photos_success'))
    <div class="mt-3 bg-green-50 border border-green-200 text-green-800 p-3 rounded text-sm">
        {{ session('photos_success') }}
    </div>
    @endif

    {{-- Загрузка --}}
    <div class="mt-4">
        <label class="text-sm text-gray-600">Загрузить фото (до 10 шт, до 4MB каждое)</label>
        <input type="file" multiple wire:model="newPhotos" class="mt-1 block w-full text-sm" />

        @error('newPhotos') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        @error('newPhotos.*') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror

        <div class="mt-3">
            <button wire:click="saveUploads" class="px-4 py-2 rounded bg-gray-800 text-white text-sm">
                Загрузить
            </button>
        </div>
    </div>

    {{-- Галерея --}}
    <div class="mt-5">
        @if($photos->isEmpty())
        <div class="text-sm text-gray-500">Пока нет загруженных фото.</div>
        @else
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($photos as $p)
            <div wire:key="photo-{{ $p->id }}" class="border rounded overflow-hidden">
                <div class="aspect-[4/3] bg-gray-100 flex items-center justify-center overflow-hidden">
                    <img
                        src="{{ asset('storage/'.$p->path) }}"
                        alt=""
                        class="w-full h-full object-cover"
                    />
                </div>

                <div class="p-2 flex items-center justify-between gap-2">
                    @if($p->is_main)
                    <span class="text-xs px-2 py-1 rounded bg-gray-800 text-white">Главное</span>
                    @else
                    <button type="button"
                            wire:click.prevent="setMain({{ $p->id }})"
                            class="text-xs px-2 py-1 rounded border">
                        Сделать главным
                    </button>
                    @endif

                    <button type="button"
                            wire:click.prevent="deletePhoto({{ $p->id }})"
                            class="text-xs px-2 py-1 rounded border text-red-600">
                        Удалить
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

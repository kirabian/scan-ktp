<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Admin Data - Kategori Warga') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Top 5 Laki / Perempuan -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-bold mb-4">Top 5 Jenis Kelamin</h3>
                        <ul class="divide-y divide-gray-200">
                            @foreach($topGenders as $item)
                                <li class="py-3 flex justify-between items-center hover:bg-gray-50 transition">
                                    <a href="{{ route('admin.warga-list', ['filter_gender' => $item['name']]) }}" class="text-blue-600 hover:text-blue-800 font-medium w-full block">
                                        {{ $item['name'] }}
                                    </a>
                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        {{ $item['count'] }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Top 5 Umur -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-bold mb-4">Top 5 Umur</h3>
                        <ul class="divide-y divide-gray-200">
                            @foreach($topAges as $item)
                                <li class="py-3 flex justify-between items-center hover:bg-gray-50 transition">
                                    <a href="{{ route('admin.warga-list', ['filter_age' => $item['name']]) }}" class="text-blue-600 hover:text-blue-800 font-medium w-full block">
                                        {{ $item['name'] }} Tahun
                                    </a>
                                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        {{ $item['count'] }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Top 5 Kecamatan -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-bold mb-4">Top 5 Kecamatan</h3>
                        <ul class="divide-y divide-gray-200">
                            @foreach($topKecamatan as $item)
                                <li class="py-3 flex justify-between items-center hover:bg-gray-50 transition">
                                    <a href="{{ route('admin.warga-list', ['filter_district' => $item['name']]) }}" class="text-blue-600 hover:text-blue-800 font-medium w-full block">
                                        {{ $item['name'] }}
                                    </a>
                                    <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        {{ $item['count'] }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Top 5 Desa -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-bold mb-4">Top 5 Desa</h3>
                        <ul class="divide-y divide-gray-200">
                            @foreach($topDesa as $item)
                                <li class="py-3 flex justify-between items-center hover:bg-gray-50 transition">
                                    <a href="{{ route('admin.warga-list', ['filter_village' => $item['name']]) }}" class="text-blue-600 hover:text-blue-800 font-medium w-full block">
                                        {{ $item['name'] }}
                                    </a>
                                    <span class="bg-orange-100 text-orange-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        {{ $item['count'] }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

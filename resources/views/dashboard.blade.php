<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}

                    <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Panduan Import Excel</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>Pada fitur import excel ini kamu bisa melakukan kustom pesan ke setiap no tujuannya. Misal dalam file excel, kamu mempunyai tabel dibawah ini:</p>
                                    
                                    <div class="my-4 overflow-x-auto">
                                        <table class="min-w-full divide-y divide-blue-200 bg-white shadow-sm rounded-lg overflow-hidden">
                                            <thead class="bg-blue-100">
                                                <tr>
                                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-800 uppercase tracking-wider"></th>
                                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">A</th>
                                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">B</th>
                                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">C</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-blue-100">
                                                <tr>
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-blue-500 bg-blue-50">1</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700">081234567890</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700">Budi</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700">Rp 200.000</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-blue-500 bg-blue-50">2</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700">081234567891</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700">Ani</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700">Rp 100.000</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-blue-500 bg-blue-50">3</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700">081234567892</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700">Rudi</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700">Rp 300.000</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <p class="mb-2">Tambahkan tanda <strong>@{{ kolom }}</strong> pada pesan untuk menyisipkan data dari file excel kamu. Misal pada field pesan, kamu menulis pesan sebagai berikut:</p>
                                    <div class="bg-white p-3 rounded shadow-sm border border-blue-200 mb-3 italic">
                                        "Hai @{{ B }}, untuk invoice kamu sebesar @{{ C }} sudah jatuh tempo ya. Silahkan segera lakukan pembayaran."
                                    </div>
                                    <p class="mb-2">Maka pesan yang dikirim ke masing-masing no tujuan akan berbeda. Untuk pesan ke <strong>081234567890</strong> sebagai berikut:</p>
                                    <div class="bg-white p-3 rounded shadow-sm border border-blue-200 mb-3 italic">
                                        "Hai Budi, untuk invoice kamu sebesar Rp 200.000 sudah jatuh tempo ya. Silahkan segera lakukan pembayaran."
                                    </div>
                                    <p class="mt-3 font-semibold text-blue-900 text-xs">
                                        * Perlu diingat, sistem akan mengambil data hingga kolom Z saja ya. Dan untuk tulisan kolom harus huruf besar.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

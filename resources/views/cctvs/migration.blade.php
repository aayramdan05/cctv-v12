<x-app-layout>
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Migrasi CCTV (Bulk Add)</h2>
            <a href="{{ route('cctv.index') }}" class="text-blue-600 hover:underline flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Data CCTV
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if(session('feedback_url'))
            <div class="bg-blue-50 border border-blue-200 p-4 rounded mb-6 flex items-start">
                <div class="flex-shrink-0 mt-0.5">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Download Hasil Pengetesan (Feedback)</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Silakan download file Excel di bawah ini untuk melihat detail CCTV mana saja yang berhasil disimpan dan mana yang gagal beserta alasannya.</p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ session('feedback_url') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" download>
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Download Feedback.xlsx
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Upload File Excel</h3>
            
            <div class="mb-6 bg-gray-50 p-4 rounded border border-gray-200">
                <h4 class="font-bold text-gray-700 mb-2">Petunjuk:</h4>
                <ul class="list-disc pl-5 text-sm text-gray-600 space-y-1">
                    <li>Gunakan template Excel yang telah disediakan untuk memastikan format kolom benar.</li>
                    <li>Sistem akan otomatis men-generate <strong>RTSP URL</strong> berdasarkan isian kolom <strong>Merk</strong>. Merek yang didukung: <em>Hikvision, Dahua, SPC, Ezviz</em>. (Selain itu akan menggunakan format default).</li>
                    <li>Pastikan nama <strong>Fakultas</strong> dan <strong>Gedung</strong> sama persis dengan yang ada di sistem agar dapat terbaca.</li>
                    <li>Sistem akan otomatis melakukan tes koneksi (Screenshot) pada saat upload. Data yang gagal tes <strong>tidak akan disimpan</strong> ke database.</li>
                </ul>
                <div class="mt-3">
                    <a href="{{ route('cctv.migration.template') }}" class="inline-flex items-center text-sm text-green-600 hover:text-green-700 font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Download Template Excel
                    </a>
                </div>
            </div>

            <form action="{{ route('cctv.migration.import') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File (.xlsx atau .csv)</label>
                    <input type="file" name="excel_file" accept=".xlsx, .csv" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="mt-6">
                    <button type="submit" id="submitBtn" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Mulai Proses Upload & Testing
                    </button>
                    
                    <div id="loadingIndicator" class="hidden mt-3 text-sm text-gray-600 flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sistem sedang mengetes koneksi seluruh kamera. Mohon jangan tutup halaman ini...
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', function() {
            document.getElementById('submitBtn').classList.add('opacity-50', 'cursor-not-allowed');
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('loadingIndicator').classList.remove('hidden');
        });
    </script>
</x-app-layout>

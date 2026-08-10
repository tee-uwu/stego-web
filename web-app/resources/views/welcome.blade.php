<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Steganography</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-800 min-h-screen flex items-center justify-center p-4 font-sans">

    <div class="w-full max-w-md bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <!-- Top accent bar -->
        <div class="h-1.5 flex">
            <div class="w-1/2 bg-emerald-700"></div>
            <div class="w-1/2 bg-orange-700"></div>
        </div>

        <div class="p-6 space-y-5">
            <!-- Header & Tabs -->
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h1 class="font-semibold text-gray-900 text-base">Steganography</h1>
                <div class="flex gap-1 bg-gray-100 p-1 rounded-lg text-xs font-medium">
                    <button id="btn-enc" onclick="tab('enc')" class="px-3 py-1 rounded bg-white text-emerald-700 shadow-xs">Encode</button>
                    <button id="btn-dec" onclick="tab('dec')" class="px-3 py-1 rounded text-gray-500 hover:text-gray-900">Decode</button>
                </div>
            </div>

            @if(session('error'))
                <div class="p-2.5 bg-red-50 text-red-600 text-xs rounded border border-red-100">{{ session('error') }}</div>
            @endif

            <!-- ENCODE -->
            <form id="v-enc" action="{{ route('stego.encode') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cover Image</label>
                    <input type="file" name="cover_image" accept="image/*" required class="w-full text-xs text-gray-600 border border-gray-200 rounded p-1.5 focus:border-emerald-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Secret Text</label>
                    <textarea name="secret_text" rows="3" required placeholder="Enter hidden message..." class="w-full text-xs border border-gray-200 rounded p-2 focus:border-emerald-600 focus:outline-none"></textarea>
                </div>
                <button type="submit" class="w-full py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-medium rounded transition">Encode Message</button>

                @if(session('stego_image'))
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg flex flex-col gap-2.5 mt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-emerald-900">Encoded Stego Image:</span>
                            <span class="text-[10px] text-emerald-600 font-mono">{{ session('stego_filename') ?? basename(session('stego_image')) }}</span>
                        </div>
                        <div class="w-full flex justify-center bg-gray-900/5 p-2 rounded border border-emerald-100/80">
                            <img src="{{ asset(session('stego_image')) }}" alt="Stego Preview" class="max-h-48 rounded object-contain shadow-xs border border-white">
                        </div>
                        <div class="flex gap-2 w-full pt-1">
                            <a href="{{ route('stego.download', session('stego_filename') ?? basename(session('stego_image'))) }}" class="flex-1 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-medium rounded text-center transition flex items-center justify-center gap-1.5 shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Download Image
                            </a>
                            <a href="{{ asset(session('stego_image')) }}" target="_blank" class="px-3 py-1.5 border border-emerald-300 text-emerald-700 hover:bg-emerald-100/60 text-xs font-medium rounded text-center transition">
                                Open Preview
                            </a>
                        </div>
                    </div>
                @endif
            </form>

            <!-- DECODE -->
            <form id="v-dec" action="{{ route('stego.decode') }}" method="POST" enctype="multipart/form-data" class="space-y-3 hidden">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Stego Image</label>
                    <input type="file" name="stego_image" accept="image/*" required class="w-full text-xs text-gray-600 border border-gray-200 rounded p-1.5 focus:border-orange-600 focus:outline-none">
                </div>
                <button type="submit" class="w-full py-2 bg-orange-700 hover:bg-orange-800 text-white text-xs font-medium rounded transition">Extract Message</button>

                @if(session('recovered_message'))
                    <div class="p-2.5 bg-orange-50 border border-orange-100 rounded">
                        <span class="text-[10px] text-orange-800 font-semibold block uppercase">Decoded Message</span>
                        <p class="text-xs font-mono text-orange-950 break-all mt-0.5 select-all">{{ session('recovered_message') }}</p>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <script>
        function tab(t) {
            const e = t === 'enc';
            document.getElementById('v-enc').classList.toggle('hidden', !e);
            document.getElementById('v-dec').classList.toggle('hidden', e);
            document.getElementById('btn-enc').className = e ? 'px-3 py-1 rounded bg-white text-emerald-700 shadow-xs' : 'px-3 py-1 rounded text-gray-500 hover:text-gray-900';
            document.getElementById('btn-dec').className = !e ? 'px-3 py-1 rounded bg-white text-orange-700 shadow-xs' : 'px-3 py-1 rounded text-gray-500 hover:text-gray-900';
        }
        @if(session('active_tab') === 'decrypt') tab('dec'); @endif
    </script>
</body>
</html>
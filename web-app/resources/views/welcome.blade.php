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
                    <div class="p-2.5 bg-emerald-50 border border-emerald-100 rounded flex items-center justify-between">
                        <img src="{{ asset(session('stego_image')) }}" class="w-10 h-10 object-cover rounded border border-emerald-200">
                        <a href="{{ asset(session('stego_image')) }}" download class="px-2.5 py-1 bg-emerald-700 text-white text-xs rounded">Download</a>
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
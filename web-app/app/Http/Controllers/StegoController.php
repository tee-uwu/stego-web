<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Str;

class StegoController extends Controller
{
    public function encode(Request $request)
    {
        $request->validate([
            'cover_image' => 'required|image|mimes:png,jpg,jpeg',
            'secret_text' => 'required|string',
        ]);

        // Save uploaded cover image to storage
        $coverPath = $request->file('cover_image')->store('uploads', 'public');
        $fullCoverPath = storage_path('app/public/' . $coverPath);

        // Define output stego image path
        $outputFileName = 'stego_' . Str::random(10) . '.png';
        $fullOutputPath = storage_path('app/public/output/' . $outputFileName);

        if (!file_exists(storage_path('app/public/output'))) {
            mkdir(storage_path('app/public/output'), 0755, true);
        }

        // Path to Python executable & engine script
        $pythonPath = 'python'; // Or full path e.g. 'C:\Python310\python.exe'
        $scriptPath = base_path('../inference/stego_engine.py');
        $inferenceDir = base_path('../inference');

        $env = array_merge($_SERVER, $_ENV, [
            'SYSTEMROOT' => getenv('SYSTEMROOT') ?: getenv('SystemRoot') ?: 'C:\\Windows',
            'SystemRoot' => getenv('SystemRoot') ?: getenv('SYSTEMROOT') ?: 'C:\\Windows',
            'TF_ENABLE_ONEDNN_OPTS' => '0',
            'TF_CPP_MIN_LOG_LEVEL' => '3',
        ]);

        // Execute: python stego_engine.py encode <cover_path> "<secret_text>" <output_path>
        $process = new Process([
            $pythonPath,
            $scriptPath,
            'encode',
            $fullCoverPath,
            $request->input('secret_text'),
            $fullOutputPath
        ], $inferenceDir, $env);

        $process->run();

        if (!$process->isSuccessful()) {
            return back()->with('error', 'Encoding failed: ' . $process->getErrorOutput());
        }

        return back()->with([
            'stego_image' => 'storage/output/' . $outputFileName,
            'active_tab' => 'encrypt'
        ]);
    }

    public function decode(Request $request)
    {
        $request->validate([
            'stego_image' => 'required|image|mimes:png,jpg,jpeg',
        ]);

        $stegoPath = $request->file('stego_image')->store('uploads', 'public');
        $fullStegoPath = storage_path('app/public/' . $stegoPath);

        $pythonPath = 'python';
        $scriptPath = base_path('../inference/stego_engine.py');
        $inferenceDir = base_path('../inference');

        $env = array_merge($_SERVER, $_ENV, [
            'SYSTEMROOT' => getenv('SYSTEMROOT') ?: getenv('SystemRoot') ?: 'C:\\Windows',
            'SystemRoot' => getenv('SystemRoot') ?: getenv('SYSTEMROOT') ?: 'C:\\Windows',
            'TF_ENABLE_ONEDNN_OPTS' => '0',
            'TF_CPP_MIN_LOG_LEVEL' => '3',
        ]);

        // Execute: python stego_engine.py decode <stego_path>
        $process = new Process([
            $pythonPath,
            $scriptPath,
            'decode',
            $fullStegoPath
        ], $inferenceDir, $env);

        $process->run();

        if (!$process->isSuccessful()) {
            return back()->with('error', 'Decoding failed: ' . $process->getErrorOutput());
        }

        $output = trim($process->getOutput());

        return back()->with([
            'recovered_message' => $output,
            'active_tab' => 'decrypt'
        ]);
    }
}
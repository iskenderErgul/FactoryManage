<?php
//
//namespace App\Http\Controllers;
//
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Storage;
//use GuzzleHttp\Client;
//
//class AudioTranscriptionController extends Controller
//{
//    public function transcribe(Request $request)
//    {
//        $request->validate([
//            'file' => 'required|file|mimes:mp3,wav', // Dosya uzantılarını kontrol et
//        ]);
//
//        $audioFile = $request->file('file');
//        $path = $audioFile->store('audio'); // Dosyayı storage/audio dizinine kaydet
//
//        // OpenAI API ile iletişim
//        $client = new Client();
//        $response = $client->post('https://api.openai.com/v1/audio/transcriptions', [
//            'headers' => [
//                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
//                'Content-Type' => 'multipart/form-data',
//            ],
//            'multipart' => [
//                [
//                    'name' => 'file',
//                    'contents' => fopen(storage_path('app/' . $path), 'r'),
//                ],
//                [
//                    'name' => 'model',
//                    'contents' => 'whisper-1',
//                ],
//            ],
//            'verify' => false, // SSL doğrulamasını devre dışı bırak
//        ]);
//
//        $data = json_decode($response->getBody(), true);
//
//        return response()->json(['success' => true, 'data' => $data['text']]);
//    }
//}

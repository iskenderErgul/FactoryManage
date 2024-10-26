import axios from "axios";
import fs from 'fs';
import FormData from 'form-data';
import dotenv from "dotenv";

import { fileURLToPath } from "url";
import path from 'path';


dotenv.config();

async function convertAudioToText(audioFilePath) {
    console.log('Ses dosyası OpenAI API\'ye gönderiliyor:', audioFilePath);
    const audioFile = fs.createReadStream(audioFilePath);

    const formData = new FormData();
    formData.append('file', audioFile);
    formData.append('model', 'whisper-1');

    try {
        const response = await axios.post('https://api.openai.com/v1/audio/transcriptions', formData, {
            headers: {
                'Authorization': `Bearer ${process.env.OPENAI_API_KEY}`,
                ...formData.getHeaders()
            }
        });
        console.log('Transkripsiyon sonucu alındı:', response.data.text);
        return response.data.text;
    } catch (error) {
        console.error('OpenAI API ile iletişimde hata oluştu:', error.message);
        throw error; // Hata fırlat
    }
}

async function parseCommandText(commandText) {
    const response = await axios.post('https://api.openai.com/v1/chat/completions', {
        model: "gpt-4",
        messages: [
            { role: "system", content: "Komutları JSON formatında ayrıştıracak bir asistansın." },
            { role: "user", content: `Metni JSON formatında ayrıştır: "${commandText}"` }
        ]
    }, {
        headers: {
            'Authorization': `Bearer ${process.env.OPENAI_API_KEY}`,
            'Content-Type': 'application/json'
        }
    });
    console.log(response)

    return response.data.choices[0].message.content;
}

async function submitToBackend(parsedData) {
    await axios.post('/api/createUsers', parsedData, {
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer YOUR_AUTH_TOKEN`
        }
    });
}

async function processAudioCommand(audioFilePath) {
    console.log('Ses dosyası işleniyor...');
    try {
        const commandText = await convertAudioToText(audioFilePath);
        const parsedCommand = await parseCommandText(commandText);
        const resp = await submitToBackend(JSON.parse(parsedCommand));

        console.log("İşlem başarılı bir şekilde gerçekleştirildi:", resp);
        return resp; // İşlem sonucu döndür
    } catch (error) {
        console.error("Bir hata oluştu:", error);
        throw error; // Hata fırlat
    }
}


const transcription = {
    processAudioCommand,
};

export default transcription;

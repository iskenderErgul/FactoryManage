import express from 'express';
import bodyParser from 'body-parser';
import dotenv from 'dotenv';
import multer from 'multer';
import transcription from './AudioConvertText/transcription.js';
import cors from 'cors'; // CORS'u içe aktarın

dotenv.config();

const app = express();
const upload = multer({ dest: 'public/audio/' }); // Dosyaların kaydedileceği yer
app.use(cors);
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

app.post('/api/transcribe', upload.single('file'), async (req, res) => {
    console.log('api fonksiyonunda')
    const audioFilePath = req.file.path; // Yüklenen dosyanın yolu
    console.log('Ses dosyası alındı:', audioFilePath);

    try {
        const response = await transcription.processAudioCommand(audioFilePath);
        console.log('Transkripsiyon işlemi başarılı:', response);
        res.json({ success: true, data: response });
    } catch (error) {
        console.error('Transkripsiyon sırasında hata oluştu:', error.message);
        res.status(500).json({ success: false, error: error.message });
    }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});

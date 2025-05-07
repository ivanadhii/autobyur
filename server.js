const express = require('express');
const nodemailer = require('nodemailer');
const bodyParser = require('body-parser');
const cors = require('cors');

const app = express();
const port = 3000;

app.use(cors());
app.use(bodyParser.json());

// Atur transport Nodemailer (pakai SendGrid)
const transporter = nodemailer.createTransport({
    host: 'smtp.sendgrid.net',
    port: 587,
    secure: false,
    auth: {
        user: 'apikey',
        pass: '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'
    }
});

// Endpoint untuk menerima permintaan email dari client
app.post('/send-email', (req, res) => {
    const { subject, text } = req.body;

    const mailOptions = {
        from: 'atthaadvisa@apps.ipb.ac.id',
        to: 'nhexania@gmail.com',
        subject,
        text
    };

    transporter.sendMail(mailOptions, (error, info) => {
        if (error) {
            console.error("Gagal mengirim email:", error);
            res.status(500).json({ success: false, error: error.toString() });
        } else {
            console.log("Email terkirim:", info.response);
            res.status(200).json({ success: true });
        }
    });
});

app.listen(port, () => {
    console.log(`Server berjalan di http://localhost:${port}`);
});

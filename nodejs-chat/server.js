const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const bodyParser = require('body-parser');

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

app.use(bodyParser.json());

let messages = [];

// HTTP orqali xabar qabul qilish
app.post('/send-message', (req, res) => {
    const messageData = {
        name: req.body.name,
        chat_id: req.body.chat_id,  // Chat ID orqali
        sender_id: req.body.sender_id,
        text: req.body.text
    };

    messages.push(messageData);

    // Faqat chat_id ga tegishli kanaldagi foydalanuvchilarga xabar yuborish
    io.to(`chat_${messageData.chat_id}`).emit('newMessage', messageData);

    return res.status(200).send('Message sent to WebSocket.');
});

// WebSocket ulanishlarini boshqarish
io.on('connection', (socket) => {
    console.log('New user connected: ', socket.id);

    // Oldingi xabarlarni uzatish
    socket.emit('previousMessages', messages);

    // Foydalanuvchini chat kanaliga ulash
    socket.on('joinChat', (chatId) => {
        socket.join(`chat_${chatId}`);
        console.log(`User joined chat: chat_${chatId}`);
    });

    // Foydalanuvchi uzilganida
    socket.on('disconnect', () => {
        console.log('User disconnected: ', socket.id);
    });
});



// WebSocket ulanishlarini boshqarish
io.on('connection', (socket) => {
    console.log('New user connected: ', socket.id);

    // Oldingi xabarlarni uzatish
    socket.emit('previousMessages', messages);

    // Foydalanuvchi uzilganida
    socket.on('disconnect', () => {
        console.log('User disconnected: ', socket.id);
    });
});

// Serverni ishga tushirish
server.listen(3000, () => {
    console.log('Node.js server is running on port 3000...');
});

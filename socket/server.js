const express = require('express');
const { createServer } = require('http');
const { Server } = require('socket.io');
const Redis = require('ioredis');
require('dotenv').config();

const app = express();
const httpServer = createServer(app);

const io = new Server(httpServer, {
    cors: {
        origin: '*',
        methods: ['GET', 'POST'],
    },
});

const redis = new Redis({
    host: process.env.REDIS_HOST || '127.0.0.1',
    port: Number(process.env.REDIS_PORT || 6379),
    password:
        process.env.REDIS_PASSWORD &&
            process.env.REDIS_PASSWORD !== 'null'
            ? process.env.REDIS_PASSWORD
            : undefined,
});

redis.on('connect', () => {
    console.log('Redis connected successfully');
});

redis.on('ready', () => {
    console.log('Redis is ready');
});

redis.on('error', (error) => {
    console.error('Redis error:', error.message);
});

redis.on('close', () => {
    console.log('Redis connection closed');
});

redis.subscribe('chat-message-created', (error, count) => {
    if (error) {
        console.error('Redis subscription error:', error.message);
        return;
    }

    console.log(
        `Subscribed successfully to ${count} Redis channel`
    );
});

redis.on('message', (channel, message) => {
    try {
        console.log('Redis channel:', channel);
        console.log('Redis message:', message);

        if (channel !== 'chat-message-created') {
            console.log('Invalid Redis channel');
            return;
        }

        const parsedMessage = JSON.parse(message);

        io.emit('message.created', parsedMessage);

        console.log(
            'message.created emitted to all connected users'
        );
    } catch (error) {
        console.error(
            'Error parsing Redis message:',
            error.message
        );
    }
});

io.on('connection', (socket) => {
    console.log('--------------------------------');
    console.log('New user connected');
    console.log('Socket ID:', socket.id);
    console.log('Connected users:', io.engine.clientsCount);
    console.log('--------------------------------');

    socket.emit('connection.success', {
        success: true,
        message: 'Connected to chat server successfully',
        socket_id: socket.id,
    });

    socket.on('disconnect', (reason) => {
        console.log('--------------------------------');
        console.log('User disconnected');
        console.log('Socket ID:', socket.id);
        console.log('Reason:', reason);
        console.log('Connected users:', io.engine.clientsCount);
        console.log('--------------------------------');
    });

    socket.on('error', (error) => {
        console.error(
            `Socket ${socket.id} error:`,
            error.message
        );
    });
});

app.get('/', (request, response) => {
    response.json({
        success: true,
        message: 'Global chat Socket.IO server is running',
        connected_users: io.engine.clientsCount,
    });
});

const port = Number(process.env.PORT || 3000);
const host = process.env.HOST || '0.0.0.0';

httpServer.listen(port, host, () => {
    console.log(
        `Socket.IO server running on http://${host}:${port}`
    );
});
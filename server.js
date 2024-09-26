const express = require('express');
const corsAnywhere = require('cors-anywhere');

const app = express();
const port = 9090; // You can use any port

corsAnywhere.createServer({
    originWhitelist: [], // Allow all origins
}).listen(port, () => {
    console.log(`CORS proxy running on http://localhost:${port}`);
});
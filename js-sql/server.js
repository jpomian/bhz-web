const express = require('express');
const mysql = require('mysql2/promise');
const cors = require('cors');
require('dotenv').config()

const app = express();
app.use(cors());

const dbConfig = {
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PW,
  database: process.env.DB_DATABASE,
  port: process.env.PORT
};

app.get('/api/players', async (req, res) => {
  try {
    const connection = await mysql.createConnection(dbConfig);
    const [rows] = await connection.query('SELECT * FROM players_stats');
    connection.end();
    res.json(rows);
  } catch (error) {
    console.error('Błąd bazy danych', error);
    res.status(500).json({ code: 'Nie zwrócono wyników' });
  }
});

app.use(express.static('public'));

const PORT = 3000;

app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});
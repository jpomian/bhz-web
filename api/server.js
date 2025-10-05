const express = require('express');
const mysql = require('mysql2/promise');
const cors = require('cors');
require('dotenv').config()

const app = express();
app.use(cors());

const sql = `SELECT
    sql_mainstats.Nick,
    sql_mainstats.Infections,
    sql_mainstats.Kills,
    sql_mainstats.Auth,
    sql_times.\`Time Played\` AS \`Time\`,
    sql_times.\`First Seen\` AS \`Last\`
FROM
    sql_mainstats
INNER JOIN
    sql_times
ON
    sql_mainstats.Auth = sql_times.Auth
  ORDER BY
    sql_mainstats.Kills DESC`;

const dbConfig = {
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PW,
  database: process.env.DB_DATABASE,
  port: process.env.PORT,
  charset: 'utf8mb4'
};

app.get('/api/players', async (_req, res) => {
  try {
    const connection = await mysql.createConnection(dbConfig);
    const [rows] = await connection.query(sql);
    connection.end();
    res.json(rows);
  } catch (error) {
    console.error('Błąd bazy danych', error);
    res.status(500).json({ code: 'Nie zwrócono wyników' });
  }
});

app.get('/gracz/:authId', async (req, res) => {
  try {
    const authId = req.params.authId;
    if (!authId) {
      return res.status(400).json({ error: 'Nie ma takiego gracza' });
    }

    const connection = await mysql.createConnection(dbConfig);
    
    const query = `SELECT
      sql_mainstats.Nick,
      sql_mainstats.Infections,
      sql_mainstats.Kills,
      sql_mainstats.Auth,
      sql_times.\`Time Played\` AS \`Time\`,
      sql_times.\`First Seen\` AS \`Last\`
    FROM
      sql_mainstats
    INNER JOIN
      sql_times
    ON
      sql_mainstats.Auth = sql_times.Auth
    WHERE sql_mainstats.Auth = ?`;
    
    const [data] = await connection.query(query, [authId]);
    await connection.end();

    if (!data || data.length === 0) {
      return res.status(404).json({ error: 'Nie ma takiego gracza' });
    }

    res.json(data[0]);
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
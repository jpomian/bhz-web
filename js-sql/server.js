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
    sql_times.\`Time Played\` AS \`Time\`,
    sql_times.\`Last Seen\` AS \`Last\`
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
  port: process.env.PORT
};

app.get('/api/players', async (req, res) => {
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

app.use(express.static('public'));

const PORT = 3000;

app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});
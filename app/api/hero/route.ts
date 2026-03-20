import { NextResponse } from 'next/server';
import mysql from 'mysql2/promise';

export async function GET() {
  const conn = await mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'grayy',
  });
  const [rows] = await conn.execute('SELECT * FROM hero_section WHERE id = 1');
  await conn.end();
  return NextResponse.json((rows as any[])[0]);
}
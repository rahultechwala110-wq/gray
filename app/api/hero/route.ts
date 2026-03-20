import { NextResponse } from 'next/server';
import mysql from 'mysql2/promise';

export async function GET() {
  try {
    const conn = await mysql.createConnection({
      host:     process.env.DB_HOST     || 'localhost',
      user:     process.env.DB_USER     || 'root',
      password: process.env.DB_PASSWORD || '',
      database: process.env.DB_NAME     || 'grayy',
    });
    const [rows] = await conn.execute('SELECT * FROM hero_section WHERE id=1 LIMIT 1');
    await conn.end();

    const data = (rows as any[])[0] ?? null;
    if (!data) return NextResponse.json(null, { status: 404 });

    data.video_file = data.video_file
      ? `http://localhost/gray/admin/uploads/hero-section/${data.video_file}`
      : '';

    return NextResponse.json(data, { headers: { 'Cache-Control': 'no-store' } });
  } catch (err) {
    console.error('Hero API error:', err);
    return NextResponse.json(null, { status: 500 });
  }
}
// app/api/about-home/route.ts
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
    const [rows] = await conn.execute('SELECT * FROM about_home WHERE id = 1 LIMIT 1');
    await conn.end();
    const data = (rows as any[])[0];
    if (!data) return NextResponse.json({ error: 'Not found' }, { status: 404 });

    const base = 'http://localhost/gray/admin/uploads/about/';
    data.small_image = data.small_image ? `${base}${data.small_image}` : '';
    data.large_image = data.large_image ? `${base}${data.large_image}` : '';

    return NextResponse.json(data);
  } catch {
    return NextResponse.json({ error: 'DB error' }, { status: 500 });
  }
}
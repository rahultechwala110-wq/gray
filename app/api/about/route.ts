// app/api/about/route.ts
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

    const [rows] = await conn.execute(
      'SELECT * FROM about_section WHERE id=1 LIMIT 1'
    );

    await conn.end();

    const data = (rows as any[])[0] ?? null;
    if (data) {
      const base = 'http://localhost/gray/admin/uploads/about/';
      data.image1 = data.image1 ? `${base}${data.image1}` : '';
      data.image2 = data.image2 ? `${base}${data.image2}` : '';
    }

    return NextResponse.json(data, {
      headers: { 'Cache-Control': 'no-store' },
    });
  } catch {
    return NextResponse.json(null, { status: 500 });
  }
}
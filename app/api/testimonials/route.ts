// app/api/testimonials/route.ts
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
      'SELECT * FROM testimonials WHERE is_active=1 ORDER BY sort_order, id DESC'
    );
    await conn.end();
    return NextResponse.json(rows, { headers: { 'Cache-Control': 'no-store' } });
  } catch {
    return NextResponse.json([], { status: 500 });
  }
}
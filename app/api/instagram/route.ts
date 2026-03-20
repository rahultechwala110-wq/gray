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

    const [settings] = await conn.execute(
      'SELECT * FROM instagram_settings WHERE id=1 LIMIT 1'
    );

    const [images] = await conn.execute(
      'SELECT * FROM instagram_images WHERE is_active=1 ORDER BY sort_order ASC, id ASC'
    );

    await conn.end();

    const base = 'http://localhost/gray/admin/uploads/instagram/';
    const data = (images as any[]).map((r) => ({
      id:    r.id,
      image: `${base}${r.image}`,
    }));

    return NextResponse.json(
      { success: true, data, settings: (settings as any[])[0] ?? null },
      { headers: { 'Cache-Control': 'no-store' } }
    );
  } catch {
    return NextResponse.json({ success: false, data: [], settings: null }, { status: 500 });
  }
}
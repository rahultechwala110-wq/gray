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

    const [reels] = await conn.execute(
      'SELECT id, video FROM reels WHERE is_active=1 ORDER BY sort_order ASC, id ASC'
    );

    const [settingsRows] = await conn.execute(
      'SELECT * FROM reels_settings WHERE id=1 LIMIT 1'
    );

    await conn.end();

    const base = 'http://localhost/gray/admin/uploads/reels/';
    const data = (reels as any[]).map((r) => ({
      id:    r.id,
      video: `${base}${r.video}`,
    }));

    const settings = (settingsRows as any[])[0] ?? {
      marquee_text:    'GRAY',
      marquee_color:   '#000000',
      marquee_opacity: 20,
      marquee_enabled: 1,
    };

    return NextResponse.json(
      { success: true, data, settings },
      { headers: { 'Cache-Control': 'no-store' } }
    );
  } catch {
    return NextResponse.json({ success: false, data: [], settings: null }, { status: 500 });
  }
}
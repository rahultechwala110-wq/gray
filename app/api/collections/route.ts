// app/api/collections/route.ts
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
    const [hero]     = await conn.execute('SELECT * FROM collections_hero WHERE id=1 LIMIT 1');
    const [products] = await conn.execute('SELECT * FROM collections_products WHERE is_active=1 ORDER BY sort_order, id');
    await conn.end();

    const base = 'http://localhost/gray/admin/uploads/collections/';
    const heroData = (hero as any[])[0];
    if (heroData?.video_file) heroData.video_file = `${base}${heroData.video_file}`;

    const mappedProducts = (products as any[]).map(p => ({
      ...p,
      image: p.image ? `${base}${p.image}` : '',
    }));

    return NextResponse.json(
      { hero: heroData, products: mappedProducts },
      { headers: { 'Cache-Control': 'no-store' } }
    );
  } catch {
    return NextResponse.json({ hero: null, products: [] }, { status: 500 });
  }
}
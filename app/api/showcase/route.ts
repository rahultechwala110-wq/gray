import { NextResponse } from 'next/server';
import mysql from 'mysql2/promise';

export async function GET() {
  try {
    const conn = await mysql.createConnection({
      host: process.env.DB_HOST || 'localhost',
      user: process.env.DB_USER || 'root',
      password: process.env.DB_PASSWORD || '',
      database: process.env.DB_NAME || 'grayy',
    });
    const [settings] = await conn.execute('SELECT * FROM showcase_settings WHERE id=1 LIMIT 1');
    const [products] = await conn.execute(
      'SELECT * FROM showcase_products WHERE is_active=1 ORDER BY sort_order ASC, id ASC'
    );
    await conn.end();

    const base = 'http://localhost/gray/admin/uploads/products/';
    const mappedProducts = (products as any[]).map(p => ({
      ...p,
      image: p.image ? `${base}${p.image}` : '',
    }));

    return NextResponse.json(
      { settings: (settings as any[])[0], products: mappedProducts },
      { headers: { 'Cache-Control': 'no-store' } }
    );
  } catch {
    return NextResponse.json({ settings: null, products: [] }, { status: 500 });
  }
}
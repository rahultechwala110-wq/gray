// app/api/featured-product/route.ts
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

    const [products] = await conn.execute(
      'SELECT * FROM featured_products WHERE is_active=1 ORDER BY sort_order, id'
    );

    const base = 'http://localhost/gray/admin/uploads/products/';
    const result = [];
    for (const p of products as any[]) {
      const [features] = await conn.execute(
        'SELECT * FROM featured_product_features WHERE product_id=? ORDER BY sort_order',
        [p.id]
      );
      result.push({
        ...p,
        image:  p.image  ? `${base}${p.image}`  : '',
        floral: p.floral ? `${base}${p.floral}` : '',
        features,
      });
    }

    await conn.end();
    return NextResponse.json(result, { headers: { 'Cache-Control': 'no-store' } });
  } catch {
    return NextResponse.json([], { status: 500 });
  }
}
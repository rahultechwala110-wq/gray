// app/api/fragrances/route.ts
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

    const [categories] = await conn.execute(
      'SELECT * FROM fragrance_categories WHERE is_active=1 ORDER BY sort_order'
    );
    const [products] = await conn.execute(
      'SELECT * FROM fragrances WHERE is_active=1 ORDER BY sort_order, name'
    );

    await conn.end();

    const mappedProducts = (products as any[]).map(p => ({
      ...p,
      image: p.image
        ? `http://localhost/gray/admin/uploads/products/${p.image}`
        : '',
    }));

    return NextResponse.json({ categories, products: mappedProducts });
  } catch {
    return NextResponse.json({ categories: [], products: [] }, { status: 500 });
  }
}
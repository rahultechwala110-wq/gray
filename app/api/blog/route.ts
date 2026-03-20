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
      'SELECT * FROM blog_section_settings WHERE id=1 LIMIT 1'
    );

    const [posts] = await conn.execute(`
      SELECT p.id, p.title, p.slug, p.excerpt, p.image, p.created_at, c.name as category
      FROM blog_posts p
      LEFT JOIN blog_categories c ON p.category_id = c.id
      WHERE p.status = 'published'
      ORDER BY p.sort_order ASC, p.created_at DESC
      LIMIT 3
    `);

    await conn.end();

    const base = 'http://localhost/gray/admin/uploads/blog/';
    const mapped = (posts as any[]).map((p) => ({
      ...p,
      image: p.image ? `${base}${p.image}` : '',
      slug:  `/blog-details/${p.slug}`,
    }));

    return NextResponse.json(
      { posts: mapped, settings: (settings as any[])[0] ?? null },
      { headers: { 'Cache-Control': 'no-store' } }
    );
  } catch {
    return NextResponse.json({ posts: [], settings: null }, { status: 500 });
  }
}
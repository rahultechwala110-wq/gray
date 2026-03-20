import { NextResponse } from 'next/server';
import mysql from 'mysql2/promise';

export async function GET(req: Request) {
  try {
    const { searchParams } = new URL(req.url);
    const slug = searchParams.get('slug') || '';

    if (!slug) {
      return NextResponse.json(null, { status: 400 });
    }

    const conn = await mysql.createConnection({
      host:     process.env.DB_HOST     || 'localhost',
      user:     process.env.DB_USER     || 'root',
      password: process.env.DB_PASSWORD || '',
      database: process.env.DB_NAME     || 'grayy',
    });

    const [rows] = await conn.execute(
      'SELECT * FROM product_details WHERE slug = ? AND is_active = 1 LIMIT 1',
      [slug]
    );
    await conn.end();

    const data = (rows as any[])[0] ?? null;
    if (!data) return NextResponse.json(null, { status: 404 });

    // Use env variable for base URL — works in both dev and production
    const base = process.env.UPLOAD_BASE_URL
      ? process.env.UPLOAD_BASE_URL.replace(/\/$/, '') + '/'
      : 'http://localhost/gray/admin/uploads/product-details/';

    data.image1         = data.image1         ? `${base}${data.image1}`         : '';
    data.image2         = data.image2         ? `${base}${data.image2}`         : '';
    data.image3         = data.image3         ? `${base}${data.image3}`         : '';
    data.video1         = data.video1         ? `${base}${data.video1}`         : '';
    data.video2         = data.video2         ? `${base}${data.video2}`         : '';
    data.whisper1_image = data.whisper1_image ? `${base}${data.whisper1_image}` : '';

    // Parse key_notes from comma-separated string to array
    data.key_notes = data.key_notes
      ? data.key_notes.split(',').map((s: string) => s.trim()).filter(Boolean)
      : [];

    // ✅ FIX: Normalize newlines so \n\n paragraph splitting works on frontend
    // MySQL TEXT fields sometimes return \r\n — normalize everything to \n
    const normalizeNewlines = (str: string) =>
      str ? str.replace(/\r\n/g, '\n').replace(/\r/g, '\n') : '';

    data.full_description  = normalizeNewlines(data.full_description);
    data.whisper1_content  = normalizeNewlines(data.whisper1_content);
    data.whisper2_content  = normalizeNewlines(data.whisper2_content);

    return NextResponse.json(data, {
      headers: { 'Cache-Control': 'no-store' },
    });
  } catch (err) {
    console.error('Product API error:', err);
    return NextResponse.json(null, { status: 500 });
  }
}
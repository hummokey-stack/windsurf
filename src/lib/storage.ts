import fs from 'node:fs/promises';
import path from 'node:path';
import crypto from 'node:crypto';

export async function uploadImage(file: File, prefix: string = 'upload'): Promise<string | null> {
  if (!file || file.size === 0) return null;

  // Max 2MB
  if (file.size > 2 * 1024 * 1024) return null;

  const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg', 'image/svg+xml'];
  if (!validTypes.includes(file.type.toLowerCase())) return null;

  let ext = 'jpg';
  if (file.type.includes('png')) ext = 'png';
  else if (file.type.includes('webp')) ext = 'webp';
  else if (file.type.includes('svg')) ext = 'svg';

  const randomHex = crypto.randomBytes(8).toString('hex');
  const filename = `${prefix}_${randomHex}.${ext}`;

  const uploadsDir = path.resolve(process.cwd(), 'public/assets/images/uploads');
  await fs.mkdir(uploadsDir, { recursive: true });

  const filePath = path.join(uploadsDir, filename);
  const arrayBuffer = await file.arrayBuffer();
  await fs.writeFile(filePath, Buffer.from(arrayBuffer));

  return `assets/images/uploads/${filename}`;
}

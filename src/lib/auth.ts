import { SignJWT, jwtVerify } from 'jose';
import bcrypt from 'bcryptjs';

const JWT_SECRET = new TextEncoder().encode(
  process.env.JWT_SECRET || 'glisse-shop-jwt-secret-key-change-in-production-2026'
);

export const COOKIE_NAME = 'admin_session';

export interface AdminSession {
  id: number;
  login: string;
}

export async function signAdminToken(adminData: AdminSession): Promise<string> {
  return await new SignJWT({ id: adminData.id, login: adminData.login })
    .setProtectedHeader({ alg: 'HS256' })
    .setIssuedAt()
    .setExpirationTime('24h')
    .sign(JWT_SECRET);
}

export async function verifyAdminToken(token: string): Promise<AdminSession | null> {
  try {
    const { payload } = await jwtVerify(token, JWT_SECRET);
    if (payload && typeof payload.id === 'number' && typeof payload.login === 'string') {
      return {
        id: payload.id,
        login: payload.login,
      };
    }
    return null;
  } catch (e) {
    return null;
  }
}

export async function hashPassword(password: string): Promise<string> {
  return await bcrypt.hash(password, 10);
}

export async function verifyPassword(password: string, storedHash: string): Promise<boolean> {
  // Support legacy sha256 or bcrypt
  if (storedHash.length === 64 && !storedHash.startsWith('$')) {
    // Legacy SHA256 check for backwards compatibility during migration
    const crypto = await import('node:crypto');
    const sha256Hash = crypto.createHash('sha256').update(password).digest('hex');
    return sha256Hash === storedHash;
  }

  return await bcrypt.compare(password, storedHash);
}

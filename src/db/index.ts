import { drizzle } from 'drizzle-orm/libsql';
import { createClient } from '@libsql/client';
import * as schema from './schema';
import path from 'node:path';

const url = process.env.DATABASE_URL || `file:${path.resolve(process.cwd(), 'data/shop.db')}`;
const authToken = process.env.DATABASE_AUTH_TOKEN || process.env.TURSO_AUTH_TOKEN;

const client = createClient({
  url,
  ...(authToken ? { authToken } : {}),
});

export const db = drizzle(client, { schema });
export { schema };

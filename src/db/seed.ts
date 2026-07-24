import { createClient } from '@libsql/client';
import bcrypt from 'bcryptjs';
import path from 'node:path';

const dbPath = process.env.DATABASE_URL || `file:${path.resolve(process.cwd(), 'data/shop.db')}`;
const client = createClient({ url: dbPath });

async function seed() {
  console.log('Seeding initial data...');
  
  // Settings
  const settingsCount = await client.execute('SELECT COUNT(*) as count FROM settings');
  if (Number(settingsCount.rows[0].count) === 0) {
    await client.execute(`
      INSERT INTO settings (site_name, slogan, logo, livraison_gratuite_montant, email, telephone, adresse, horaires, facebook, instagram, meta_description)
      VALUES (
        'GlisseShop',
        'Votre spécialiste wingsurf & sports de glisse',
        'assets/images/logo.png',
        100.0,
        'contact@glisseshop.fr',
        '04 00 00 00 00',
        '123 Promenade des Sports, 06000 Nice',
        'Lun-Sam 9h-19h • Dim 10h-17h',
        'https://facebook.com/glisseshop',
        'https://instagram.com/glisseshop',
        'GlisseShop - Votre spécialiste wingsurf, kitesurf, surf et sports de glisse.'
      )
    `);
    console.log('✔ Initial settings inserted');
  }

  // Admin user
  const adminCount = await client.execute('SELECT COUNT(*) as count FROM admin');
  if (Number(adminCount.rows[0].count) === 0) {
    const hash = await bcrypt.hash('admin123', 10);
    await client.execute({
      sql: 'INSERT INTO admin (login, password) VALUES (?, ?)',
      args: ['admin', hash],
    });
    console.log('✔ Default admin created (login: admin, password: admin123)');
  }
}

seed().catch((err) => {
  console.error('Seed error:', err);
  process.exit(1);
});
